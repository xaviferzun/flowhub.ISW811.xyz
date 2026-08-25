<?php

namespace App\Jobs;

use App\Actions\DiscordSendMessageAction;
use App\Contracts\ActionHandler;
use App\Models\Automation;
use App\Models\Condition;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

//FH-38 Job consumidor: recibe el ID de una automatizacion ya disparada, recorre sus condiciones
//y, si todas se cumplen, ejecuta la cadena de acciones en orden contra el ActionHandler
//correspondiente a cada tipo. Corre en el worker (php artisan queue:work), como proceso
//independiente del proceso web que lo encola (requerimiento #7 / restriccion arquitectonica #1).
class ExecuteAutomationJob implements ShouldQueue
{
    use Queueable;

    //FH-38 Mapa tipo de accion => clase adaptadora (patron adaptador, restriccion #3 del enunciado).
    //Sumar un proveedor nuevo solo implica agregar una entrada aqui, sin tocar el resto del job.
    //github.create_issue y email.send se suman cuando sus adaptadores esten listos.
    private const ACTION_HANDLERS = [
        'discord.send_message' => DiscordSendMessageAction::class,
    ];

    /**
     * Create a new job instance.
     *
     * @param int $automationId ID de la automatizacion a ejecutar.
     * @param array $triggerData Datos que produjo el disparador, usados para evaluar
     *                           condiciones e interpolar las plantillas de las acciones.
     */
    public function __construct(
        public int $automationId,
        public array $triggerData = [],
    ) {
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
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

        foreach ($automation->actions as $action) {
            $handlerClass = self::ACTION_HANDLERS[$action->type] ?? null;

            if (! $handlerClass) {
                Log::warning("ExecuteAutomationJob: no hay ActionHandler registrado para el tipo '{$action->type}' (automation #{$this->automationId}, action #{$action->id}).");
                continue;
            }

            /** @var ActionHandler $handler */
            $handler = new $handlerClass();
            $handler->execute($action, $this->triggerData);
        }
    }

    //FH-38 Todas las condiciones deben cumplirse (AND) para que la automatizacion continue.
    //Una automatizacion sin condiciones configuradas pasa siempre (son opcionales, requerimiento #3).
    private function conditionsPass(Automation $automation): bool
    {
        foreach ($automation->conditions as $condition) {
            if (! $this->evaluateCondition($condition)) {
                return false;
            }
        }

        return true;
    }

    //FH-38 Evalua una condicion contra los datos del trigger. El campo se busca tal cual en
    //$triggerData (mismos nombres que usa TemplateInterpolator para {{trigger.campo}}).
    private function evaluateCondition(Condition $condition): bool
    {
        $actual = $this->triggerData[$condition->field] ?? null;
        $expected = $condition->value;

        return match ($condition->operator) {
            'equals' => (string) $actual === $expected,
            'not_equals' => (string) $actual !== $expected,
            'contains' => str_contains((string) $actual, $expected),
            'greater_than' => is_numeric($actual) && is_numeric($expected) && (float) $actual > (float) $expected,
            'less_than' => is_numeric($actual) && is_numeric($expected) && (float) $actual < (float) $expected,
            default => false,
        };
    }
}
