<?php

namespace App\Triggers;

use App\Contracts\TriggerHandler;
use App\Models\Trigger;
use Carbon\Carbon;
use Cron\CronExpression;

//FH-30 Adaptador para triggers de tipo schedule.cron, implementando el contrato TriggerHandler
class ScheduleCronTrigger implements TriggerHandler
{
    //FH-33 Verdadero si la expresion cron guardada coincide con el minuto actual
    public function shouldFire(Trigger $trigger): bool
    {
        $expression = $trigger->config['value'] ?? null;

        if (! $expression || ! CronExpression::isValidExpression($expression)) {
            return false;
        }

        return (new CronExpression($expression))->isDue();
    }

    //Datos minimos disponibles para este tipo de trigger - fecha/hora de disparo
    public function getData(Trigger $trigger): array
    {
        return [
            'fired_at' => Carbon::now()->toDateTimeString(),
        ];
    }
}