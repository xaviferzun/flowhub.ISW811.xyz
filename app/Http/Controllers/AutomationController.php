<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class AutomationController extends Controller
{
    //FH-26 Lista las automatizaciones del usuario autenticado, con su estado activo/inactivo
    public function index(Request $request): View
    {
        $automations = $request->user()->automations()->latest()->get();

        return view('automations.index', compact('automations'));
    }
}