<?php

namespace App\Triggers;

use App\Contracts\TriggerHandler;
use App\Models\Trigger;
use Carbon\Carbon;

//FH-30 Adaptador de ejemplo para triggers de tipo schedule.cron, implementando el contrato TriggerHandler
class ScheduleCronTrigger implements TriggerHandler
{
    //Implementacion de prueba. Cron corresponde al ticket del catalogo de disparadores
    public function shouldFire(Trigger $trigger): bool
    {
        return true;
    }

    //Datos minimos disponibles para este tipo de trigger - fecha/hora de disparo
    public function getData(Trigger $trigger): array
    {
        return [
            'fired_at' => Carbon::now()->toDateTimeString(),
        ];
    }
}