<?php

namespace App\Jobs;

use App\Actions\DiscordSendMessageAction;
use App\Actions\EmailSendAction;
use App\Actions\GithubCreateIssueAction;
use App\Contracts\ActionHandler;
use App\Models\Automation;
use App\Models\AutomationExecution;
use App\Models\Condition;
use App\Models\ExecutionLog;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

//FH-38 Job consumidor: recibe el ID de una automatizacion ya disparada, recorre sus condiciones
//y, si todas se cumplen, ejecuta la cadena de acciones en orden contra el ActionHandler
//correspondiente a cada tipo. Corre en el worker (php artisan queue:work), como proceso
//independiente del proceso web que lo encola (requerimiento #7 / restriccion arquitectonica #1).
class ExecuteAutomationJob implements ShouldQueue
{
    use Queueable;

    //FH-42 Cantidad maxima de intentos ante fallos transitorios.
    public int $tries = 3;

    //FH-42 Espera entre reintentos, creciente: 10s, luego 30s, luego 60s.
    public array $backoff = [10, 30, 60];

    //FH-38 Mapa tipo de accion => clase adaptadora (patron adaptador, restriccion #3 del enunciado).
    //Sumar un proveedor nuevo solo implica agregar una entrada aqui, sin tocar el resto del job.
    //email.send se suma cuando su adaptador este listo.
    private const ACTION_HANDLERS = [
        'discord.send_message' => DiscordSendMessageAction::class,
        'github.create_issue' => GithubCreateIssueAction::class,
        'email.send' => EmailSendAction::class,
    ];
    /**
     * Create a new job instance.
     *
     * @param int $automationId ID de la automatizacion a ejecutar.
     * @param array $triggerData Datos que produjo el disparador, usados para evaluar
     *                           condiciones e interpolar las plantillas de las acciones.
     * @param string $executionId Clave unica (UUID) de este disparo especifico. Si el mismo
     *                            trabajo se reprocesa, llega con la misma clave, lo que permite
     *                            detectarlo y no repetir las acciones.
     */
    public function __construct(
        public int $automationId,
        public array $triggerData = [],
        public string $executionId = '',
    ) {
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        if ($this->executionId !== '' && $this->isDuplicate()) {
            Log::info("ExecuteAutomationJob: execution {$this->executionId} ya fue procesada, se descarta para no repetir acciones.");
            return;
        }

        $automation = Automation::with(['conditions', 'actions' => fn ($query) => $query->orderBy('order')])
            ->find($this->automationId);

        if (! $automation) {
            Log::warning("ExecuteAutomationJob: automation #{$this->automationId} ya no existe, se descarta.");
            return;
        }

        if (! $automation->is_active) {
            Log::info("ExecuteAutomationJob: automation #{$this->automationId} esta desactivada, se descarta.");
            return;
        }

        if (! $this->conditionsPass($automation)) {
            Log::info("ExecuteAutomationJob: automation #{$this->automationId} no cumplio sus condiciones, no se ejecutan acciones.");
            return;
        }

        $executedActions = [];

        foreach ($automation->actions as $action) {
            $handlerClass = self::ACTION_HANDLERS[$action->type] ?? null;

            if (! $handlerClass) {
                Log::warning("ExecuteAutomationJob: no hay ActionHandler registrado para el tipo '{$action->type}' (automation #{$this->automationId}, action #{$action->id}).");
                continue;
            }

            /** @var ActionHandler $handler */
            $handler = new $handlerClass();
            $handler->execute($action, $this->triggerData);
            $executedActions[] = $action->type;
        }

        //FH-42 Recien aca, con todas las acciones ya ejecutadas sin errores, se marca la ejecucion
        //como procesada. Si algo lanzo una excepcion antes de llegar aca, no se marca nada, y el
        //reintento (tries/backoff de arriba) puede volver a correr todo desde cero.
        $this->markProcessed();

        //FH-45 Deja constancia de la ejecucion exitosa para la vista de historial.
        $this->logExecution('success', ['actions' => $executedActions]);
    }

    //FH-45 Laravel llama este metodo automaticamente cuando el job agota todos sus reintentos
    //y queda definitivamente fallido (el mismo momento en que cae en failed_jobs). Deja
    //constancia de ese fallo para la vista de historial.
    public function failed(Throwable $exception): void
    {
        $this->logExecution('failed', null, $exception->getMessage());
    }

    //FH-45 Guarda el resultado de esta ejecucion (exitosa o fallida) para poder consultarlo
    //despues en la vista de historial.
    private function logExecution(string $status, ?array $result = null, ?string $errorDetail = null): void
    {
        ExecutionLog::create([
            'execution_id' => $this->executionId !== '' ? $this->executionId : (string) Str::uuid(),
            'automation_id' => $this->automationId,
            'status' => $status,
            'input_data' => $this->triggerData,
            'result' => $result,
            'error_detail' => $errorDetail,
        ]);
    }

    //FH-41 Revisa si esta clave de ejecucion ya fue registrada como completada.
    private function isDuplicate(): bool
    {
        return AutomationExecution::where('execution_id', $this->executionId)->exists();
    }

    //FH-42 Registra la ejecucion como completada, solo despues de correr todas las acciones sin errores.
    private function markProcessed(): void
    {
        if ($this->executionId === '') {
            return;
        }

        AutomationExecution::firstOrCreate(
            ['execution_id' => $this->executionId],
            ['automation_id' => $this->automationId]
        );
    }

    //FH-51 Evalua las condiciones en orden, combinandolas secuencialmente con su campo 'logic'
    //(and/or). La primera condicion no tiene 'anterior' con quien combinarse, asi que su propio
    //valor es el punto de partida. Sin condiciones, pasa siempre (son opcionales, requerimiento #3).
    private function conditionsPass(Automation $automation): bool
    {
        $conditions = $automation->conditions;

        if ($conditions->isEmpty()) {
            return true;
        }

        $result = $this->evaluateCondition($conditions->first());

        foreach ($conditions->skip(1) as $condition) {
            $current = $this->evaluateCondition($condition);

            $result = $condition->logic === 'or'
                ? $result || $current
                : $result && $current;
        }

        return $result;
    }

    //FH-38 Evalua una condicion contra los datos del trigger. El campo se busca tal cual en
    //$triggerData (mismos nombres que usa TemplateInterpolator para {{trigger.campo}}).
    //FH-51 Comparadores ampliados: starts_with, ends_with y not_contains sumados a los originales.
    private function evaluateCondition(Condition $condition): bool
    {
        $actual = $this->triggerData[$condition->field] ?? null;
        $expected = $condition->value;

        return match ($condition->operator) {
            'equals' => (string) $actual === $expected,
            'not_equals' => (string) $actual !== $expected,
            'contains' => str_contains((string) $actual, $expected),
            'not_contains' => ! str_contains((string) $actual, $expected),
            'starts_with' => str_starts_with((string) $actual, $expected),
            'ends_with' => str_ends_with((string) $actual, $expected),
            'greater_than' => is_numeric($actual) && is_numeric($expected) && (float) $actual > (float) $expected,
            'less_than' => is_numeric($actual) && is_numeric($expected) && (float) $actual < (float) $expected,
            default => false,
        };
    }
}
