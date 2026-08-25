<?php

namespace App\Console\Commands;

use App\Models\Trigger;
use App\Triggers\ScheduleCronTrigger;
use Illuminate\Console\Command;

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
                $this->info("Trigger #{$trigger->id} disparado (automation #{$trigger->automation_id})");

                //Pendiente el job que ejecuta la cadena de acciones de la FH-38 en adelnate
            }
        }
    }
}