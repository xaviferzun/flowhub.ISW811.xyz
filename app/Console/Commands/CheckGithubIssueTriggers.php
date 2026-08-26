<?php

namespace App\Console\Commands;

use App\Jobs\ExecuteAutomationJob;
use App\Models\Trigger;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

//FH-31 Disparador basado en eventos via polling (sin webhook): consulta periodicamente la API
//de GitHub por issues nuevas, en vez de esperar una notificacion push del proveedor.
class CheckGithubIssueTriggers extends Command
{
    protected $signature = 'automations:check-github-issue-triggers';

    protected $description = 'Hace polling a la API de GitHub buscando issues nuevas por cada trigger github.issue_created';

    public function handle(): void
    {
        $triggers = Trigger::where('type', 'github.issue_created')->get();

        foreach ($triggers as $trigger) {
            $this->checkTrigger($trigger);
        }
    }

    private function checkTrigger(Trigger $trigger): void
    {
        $repo = $trigger->config['repo'] ?? $trigger->config['value'] ?? null;

        if (! $repo) {
            return;
        }

        $connection = $trigger->automation->user
            ->connectedAccounts()
            ->where('provider', 'github')
            ->first();

        if (! $connection) {
            return;
        }

        //Polling: preguntamos por issues, ordenadas por creacion descendente, y comparamos
        //contra la marca de la ultima vez que revisamos este trigger.
        $response = Http::withToken($connection->access_token)
            ->get("https://api.github.com/repos/{$repo}/issues", [
                'sort' => 'created',
                'direction' => 'desc',
                'state' => 'all',
                'per_page' => 5,
            ]);

        if ($response->failed()) {
            return;
        }

        $issues = $response->json();
        $lastChecked = $trigger->last_checked_at;

        foreach (array_reverse($issues) as $issue) {
            $createdAt = \Carbon\Carbon::parse($issue['created_at']);

            if (! $lastChecked || $createdAt->greaterThan($lastChecked)) {
                $this->info("Nueva issue detectada en {$repo}: #{$issue['number']} {$issue['title']}");

                ExecuteAutomationJob::dispatch(
                    $trigger->automation_id,
                    [
                        'repo' => $repo,
                        'issue_number' => (string) $issue['number'],
                        'issue_title' => $issue['title'],
                    ],
                    (string) Str::uuid()
                );
            }
        }

        $trigger->update(['last_checked_at' => now()]);
    }
}