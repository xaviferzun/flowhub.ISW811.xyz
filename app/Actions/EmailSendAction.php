<?php

namespace App\Actions;

use App\Contracts\ActionHandler;
use App\Models\Action;
use App\Services\TemplateInterpolator;
use Illuminate\Support\Facades\Mail;

//FH-37 Adaptador para acciones de tipo email.send, implementando el contrato ActionHandler.
//Tercer proveedor del catalogo de acciones (junto a Discord y GitHub), sin requerir conexion OAuth.
class EmailSendAction implements ActionHandler
{
    //config.value tiene el email destino. El cuerpo se arma interpolando los datos del trigger.
    public function execute(Action $action, array $data): void
    {
        $to = $action->config['value'] ?? null;

        if (! $to) {
            throw new \RuntimeException("Accion email.send sin destinatario configurado (action #{$action->id})");
        }

        $interpolator = new TemplateInterpolator();
        $body = $interpolator->interpolate('Nueva notificacion de FlowHub: {{trigger.repo}}', $data);

        Mail::raw($body, function ($message) use ($to) {
            $message->to($to)->subject('Notificacion de FlowHub');
        });
    }
}