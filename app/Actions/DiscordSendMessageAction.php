<?php

namespace App\Actions;

use App\Contracts\ActionHandler;
use App\Models\Action;
use App\Services\TemplateInterpolator;
use Illuminate\Support\Facades\Log;

//FH-34 Adaptador de ejemplo para acciones de tipo discord.send_message, implementando el contrato ActionHandler
class DiscordSendMessageAction implements ActionHandler
{
    //Implementacion de prueba, interpola el mensaje y lo registra en el log, en vez de llamar a la API real de Discord asignado a FH-35
    public function execute(Action $action, array $data): void
    {
        $interpolator = new TemplateInterpolator();

        $message = $interpolator->interpolate($action->config['value'] ?? '', $data);

        Log::info("Discord (simulado): {$message}");
    }
}