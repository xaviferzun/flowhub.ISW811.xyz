<?php

namespace App\Contracts;

use App\Models\Action;

//FH-34 Contrato comun que toda accion debe implementar - patron adaptador x restriccion 3 del enunciado
interface ActionHandler
{
    //Ejecuta la accion contra la API del proveedor correspondiente, usando los datos del trigger para interpolar su configuracion
    public function execute(Action $action, array $data): void;
}