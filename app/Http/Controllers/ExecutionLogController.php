<?php

namespace App\Http\Controllers;

use App\Models\ExecutionLog;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ExecutionLogController extends Controller
{
    //Lista las ejecuciones de las automatizaciones del usuario logueado, mas recientes primero,
    //con filtro opcional por estado (success/failed).
    public function index(Request $request): View
    {
        $status = $request->query('status');

        $executionLogs = ExecutionLog::with('automation')
            ->whereHas('automation', fn ($query) => $query->where('user_id', $request->user()->id))
            ->when($status, fn ($query) => $query->where('status', $status))
            ->orderByDesc('created_at')
            ->get();

        return view('execution-logs.index', [
            'executionLogs' => $executionLogs,
            'status' => $status,
        ]);
    }
}
