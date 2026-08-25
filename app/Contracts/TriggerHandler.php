<?php

namespace App\Contracts;

use App\Models\Trigger;

//FH-30 Contrato comun que todo disparador debe implementar (patron adaptador, restriccion arquitectonica #3 del enunciado)
interface TriggerHandler
{
    //Determina si este trigger debe dispararse en este momento
    public function shouldFire(Trigger $trigger): bool;

    //Devuelve los datos que la automatizacion va a usar para interpolar las plantillas de sus acciones (ver App\Services\TemplateInterpolator)
    public function getData(Trigger $trigger): array;
}