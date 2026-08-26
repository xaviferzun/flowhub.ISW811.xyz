<?php

namespace App\Http\Controllers;

use App\Jobs\ExecuteAutomationJob;
use App\Models\Trigger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

//FH-53 Expone un servidor MCP propio. Cada automatizacion con un trigger tipo mcp.tool_call
//tiene su propia URL con un token secreto. Un cliente MCP que llame la herramienta expuesta
//dispara esa automatizacion, igual que cualquier otro trigger. Implementa lo minimo del
//protocolo JSON-RPC de MCP: initialize, tools/list y tools/call.
class McpServerController extends Controller
{
    public function handle(Request $request, string $token): JsonResponse
    {
        $trigger = Trigger::where('type', 'mcp.tool_call')
            ->where('config->token', $token)
            ->first();

        if (! $trigger) {
            return response()->json(['error' => 'MCP endpoint no encontrado'], 404);
        }

        $payload = $request->json()->all();
        $id = $payload['id'] ?? null;
        $method = $payload['method'] ?? null;

        return match ($method) {
            'initialize' => $this->jsonRpc($id, [
                'protocolVersion' => '2024-11-05',
                'serverInfo' => ['name' => 'FlowHub', 'version' => '1.0'],
                'capabilities' => ['tools' => new \stdClass()],
            ]),
            'tools/list' => $this->jsonRpc($id, [
                'tools' => [[
                    'name' => 'trigger_automation',
                    'description' => 'Dispara la automatizacion de FlowHub conectada a este servidor MCP',
                    'inputSchema' => [
                        'type' => 'object',
                        'properties' => ['trigger_data' => ['type' => 'object']],
                    ],
                ]],
            ]),
            'tools/call' => $this->callTool($id, $trigger, $payload['params'] ?? []),
            default => $this->jsonRpcError($id, -32601, 'Metodo no soportado'),
        };
    }

    //Ejecuta la herramienta 'trigger_automation': dispara la automatizacion asociada a este
    //endpoint con los datos que envio el cliente MCP, igual que cualquier otro trigger.
    private function callTool($id, Trigger $trigger, array $params): JsonResponse
    {
        if (($params['name'] ?? null) !== 'trigger_automation') {
            return $this->jsonRpcError($id, -32602, 'Herramienta desconocida');
        }

        $triggerData = $params['arguments']['trigger_data'] ?? [];
        $executionId = (string) Str::uuid();

        ExecuteAutomationJob::dispatch($trigger->automation_id, $triggerData, $executionId);

        return $this->jsonRpc($id, [
            'content' => [[
                'type' => 'text',
                'text' => "Automatizacion #{$trigger->automation_id} disparada (execution {$executionId})",
            ]],
        ]);
    }

    private function jsonRpc($id, array $result): JsonResponse
    {
        return response()->json(['jsonrpc' => '2.0', 'id' => $id, 'result' => $result]);
    }

    private function jsonRpcError($id, int $code, string $message): JsonResponse
    {
        return response()->json(['jsonrpc' => '2.0', 'id' => $id, 'error' => ['code' => $code, 'message' => $message]]);
    }
}
