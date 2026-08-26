<?php

namespace App\Actions;

use App\Contracts\ActionHandler;
use App\Models\Action;
use App\Services\TemplateInterpolator;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

//FH-34 Adaptador para acciones de tipo discord.send_message, implementando el contrato ActionHandler
class DiscordSendMessageAction implements ActionHandler
{
    //FH-35 Interpola el mensaje y lo publica en el canal via el webhook autorizado por OAuth (conexion discord_webhook)
    public function execute(Action $action, array $data): void
    {
        $interpolator = new TemplateInterpolator();

        $message = $interpolator->interpolate($action->config['value'] ?? '', $data);

        $connection = $action->automation->user
            ->connectedAccounts()
            ->where('provider', 'discord_webhook')
            ->first();

        if (! $connection || ! $connection->webhook_url) {
            Log::warning("Accion discord.send_message sin conexion webhook activa (automation #{$action->automation_id})");
            return;
        }

        $response = Http::post($connection->webhook_url, ['content' => $message]);

        if ($response->failed()) {
            //Se lanza la excepcion (en vez de solo loguear) para que el job la detecte como fallo
            //y reintente segun su configuracion de tries/backoff.
            throw new \RuntimeException("Fallo al enviar mensaje a Discord (automation #{$action->automation_id}): {$response->status()}");
        }
    }
}
