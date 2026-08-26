<?php

namespace App\Http\Controllers;

use App\Models\FailedJob;
use Illuminate\View\View;
use Illuminate\Support\Str;

class FailedJobController extends Controller
{
    //Lista los jobs que agotaron sus reintentos (tabla failed_jobs), mas recientes primero.
    //Muestra la clase del job, cuando fallo, y la primera linea de la excepcion.
    public function index(): View
    {
        $failedJobs = FailedJob::orderByDesc('failed_at')->get()->map(function (FailedJob $job) {
            $payload = json_decode($job->payload, true);

            return [
                'id' => $job->id,
                'job_class' => class_basename($payload['displayName'] ?? 'Desconocido'),
                'failed_at' => $job->failed_at,
                'exception_summary' => Str::of($job->exception)->explode("\n")->first(),
            ];
        });

        return view('failed-jobs.index', compact('failedJobs'));
    }
}
