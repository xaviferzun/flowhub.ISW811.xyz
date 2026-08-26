<?php

namespace App\Http\Controllers;

use App\Models\Automation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;

class AutomationController extends Controller
{
    //FH-27 Catalogo minimo de triggers/actions disponibles para el wizard (hardcodeado por ahora, el catalogo real llega en el epic de Triggers/Actions)
    private function triggerTypes(): array
    {
        return [
            'schedule.cron' => 'Programado (cron)',
            'github.issue_created' => 'GitHub: nueva issue',
        ];
    }

    private function actionTypes(): array
    {
        return [
            'discord.send_message' => 'Discord: enviar mensaje',
            'github.create_issue' => 'GitHub: crear issue',
            'email.send' => 'Email: enviar correo',
        ];
    }

    //FH-26 Lista las automatizaciones del usuario autenticado, con su estado activo/inactivo
    public function index(Request $request): View
    {
        $automations = $request->user()->automations()->latest()->get();

        return view('automations.index', compact('automations'));
    }

    //FH-27 Muestra el wizard de creacion (disparador, condiciones opcionales, acciones ordenadas)
    public function create(): View
    {
        return view('automations.create', [
            'triggerTypes' => $this->triggerTypes(),
            'actionTypes' => $this->actionTypes(),
        ]);
    }

    //FH-27 Guarda la automatizacion con su trigger, condiciones opcionales y acciones ordenadas
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],

            'trigger_type' => ['required', 'string', 'in:'.implode(',', array_keys($this->triggerTypes()))],
            'trigger_value' => ['nullable', 'string', 'max:255'],

            'conditions' => ['nullable', 'array'],
            'conditions.*.field' => ['required_with:conditions', 'string', 'max:255'],
            'conditions.*.operator' => ['required_with:conditions', 'string', 'in:equals,not_equals,contains,not_contains,starts_with,ends_with,greater_than,less_than'],
            'conditions.*.value' => ['required_with:conditions', 'string', 'max:255'],
            'conditions.*.logic' => ['nullable', 'string', 'in:and,or'],

            'actions' => ['required', 'array', 'min:1'],
            'actions.*.type' => ['required', 'string', 'in:'.implode(',', array_keys($this->actionTypes()))],
            'actions.*.value' => ['nullable', 'string', 'max:255'],
        ]);

        //FH-27 Transaccion: si algo falla a mitad de camino, no queda una automatizacion a medias sin trigger o sin todas sus acciones
        DB::transaction(function () use ($request, $validated) {
            $automation = $request->user()->automations()->create([
                'name' => $validated['name'],
                'is_active' => true,
            ]);

            $automation->trigger()->create([
                'type' => $validated['trigger_type'],
                'config' => ['value' => $validated['trigger_value'] ?? null],
            ]);

            foreach ($validated['conditions'] ?? [] as $condition) {
                $automation->conditions()->create([
                    'field' => $condition['field'],
                    'operator' => $condition['operator'],
                    'value' => $condition['value'],
                    'logic' => $condition['logic'] ?? 'and',
                ]);
            }

            foreach ($validated['actions'] as $index => $action) {
                $automation->actions()->create([
                    'type' => $action['type'],
                    'config' => ['value' => $action['value'] ?? null],
                    'order' => $index + 1,
                ]);
            }
        });

        return redirect()->route('automations.index');
    }

    //FH-28 Elimina una automatizacion propia. cascadeOnDelete en las migraciones borra trigger/conditions/actions automaticamente
    public function destroy(Request $request, Automation $automation): RedirectResponse
    {
        abort_unless($automation->user_id === $request->user()->id, 403);

        $automation->delete();

        return redirect()->route('automations.index');
    }

    //FH-28 Activa/desactiva sin borrar el registro
    public function toggle(Request $request, Automation $automation): RedirectResponse
    {
        abort_unless($automation->user_id === $request->user()->id, 403);

        $automation->update(['is_active' => ! $automation->is_active]);

        return redirect()->route('automations.index');
    }
}