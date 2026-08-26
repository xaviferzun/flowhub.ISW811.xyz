<?php

namespace App\Actions;

use App\Contracts\ActionHandler;
use App\Models\Action;
use App\Services\TemplateInterpolator;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

//FH-36 Adaptador para acciones de tipo github.create_issue, implementando el contrato ActionHandler
class GithubCreateIssueAction implements ActionHandler
{
    //FH-36 config.value tiene el repo destino el titulo del issue se arma interpolando los datos del trigger. Usa el token OAuth del usuario (conexion 'github')
    public function execute(Action $action, array $data): void
    {
        $repo = $action->config['value'] ?? null;

        if (! $repo) {
            Log::warning("Accion github.create_issue sin repo configurado (action #{$action->id})");
            return;
        }

        $connection = $action->automation->user
            ->connectedAccounts()
            ->where('provider', 'github')
            ->first();

        if (! $connection) {
            Log::warning("Accion github.create_issue sin conexion GitHub activa (automation #{$action->automation_id})");
            return;
        }

        $interpolator = new TemplateInterpolator();
        $title = $interpolator->interpolate('Automatizacion FlowHub: {{trigger.repo}}', $data);

        $response = Http::withToken($connection->access_token)
            ->post("https://api.github.com/repos/{$repo}/issues", [
                'title' => $title,
            ]);

        if ($response->failed()) {
            Log::error("Fallo al crear issue en GitHub (automation #{$action->automation_id}): {$response->status()}");
        }
    }
}