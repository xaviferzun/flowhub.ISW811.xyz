<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

//FH-33 Revisa triggers schedule.cron cada minuto siguiendo el requerimiento 4 - disparador basado en tiempo
Schedule::command('automations:check-time-triggers')->everyMinute();

//FH-31 Disparador basado en eventos via polling: revisa la API de GitHub cada 2 minutos
Schedule::command('automations:check-github-issue-triggers')->everyTwoMinutes();