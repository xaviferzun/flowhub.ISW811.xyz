<?php

namespace App\Console\Commands;

use App\Jobs\ExecuteAutomationJob;
use App\Models\Trigger;
use App\Triggers\ScheduleCronTrigger;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class CheckTimeTriggers extends Command
{
    protected $signature = 'automations:check-time-triggers';

    protected $description = 'Revisa los triggers schedule.cron y ejecuta los que coinciden con el minuto actual';

    //FH-33 Recorre triggers schedule.cron usando el contrato TriggerHandler (patron adaptador de FH-30)
    public function handle(): void
    {
        $handler = new ScheduleCronTrigger();

        $triggers = Trigger::where('type', 'schedule.cron')->get();

        foreach ($triggers as $trigger) {
            if ($handler->shouldFire($trigger)) {
                //Cada disparo tiene su propia clave unica; si el trabajo se reprocesa, la clave
                //no cambia, y ExecuteAutomationJob la usa para no repetir las acciones.
                $executionId = (string) Str::uuid();

                ExecuteAutomationJob::dispatch($trigger->automation_id, $handler->getData($trigger), $executionId);

                $this->info("Trigger #{$trigger->id} disparado (automation #{$trigger->automation_id}), job encolado");
            }
        }
    }
}
