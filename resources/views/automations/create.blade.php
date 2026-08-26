<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Nueva automatización') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6"
                 x-data="{
                    step: 1,
                    conditions: [],
                    actions: [{ type: '', value: '' }],
                    addCondition() { this.conditions.push({ field: '', operator: 'equals', value: '', logic: 'and' }) },
                    removeCondition(i) { this.conditions.splice(i, 1) },
                    addAction() { this.actions.push({ type: '', value: '' }) },
                    removeAction(i) { this.actions.splice(i, 1) },
                 }">

                {{-- Indicador de paso --}}
                <div class="flex items-center gap-4 mb-6 text-sm">
                    <span :class="step === 1 ? 'font-bold text-indigo-600' : 'text-gray-400'">1. Disparador</span>
                    <span>&rarr;</span>
                    <span :class="step === 2 ? 'font-bold text-indigo-600' : 'text-gray-400'">2. Condiciones</span>
                    <span>&rarr;</span>
                    <span :class="step === 3 ? 'font-bold text-indigo-600' : 'text-gray-400'">3. Acciones</span>
                </div>

                @if ($errors->any())
                    <div class="mb-4 p-4 bg-red-50 text-red-700 rounded">
                        <ul class="list-disc list-inside">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('automations.store') }}">
                    @csrf

                    {{-- Nombre: siempre visible, no forma parte de los 3 pasos --}}
                    <div class="mb-6">
                        <x-input-label for="name" value="Nombre de la automatización" />
                        <x-text-input id="name" name="name" type="text" class="mt-1 block w-full"
                                      value="{{ old('name') }}" required />
                    </div>

                    {{-- Paso 1: Disparador --}}
                    <div x-show="step === 1">
                        <x-input-label for="trigger_type" value="Tipo de disparador" />
                        <select id="trigger_type" name="trigger_type"
                                class="mt-1 block w-full border-gray-300 rounded-md">
                            <option value="">-- Seleccionar --</option>
                            @foreach ($triggerTypes as $value => $label)
                                <option value="{{ $value }}" {{ old('trigger_type') === $value ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>

                        <div class="mt-4">
                            <x-input-label for="trigger_value" value="Configuración (ej: repo, expresión cron)" />
                            <x-text-input id="trigger_value" name="trigger_value" type="text"
                                          class="mt-1 block w-full" value="{{ old('trigger_value') }}" />
                        </div>
                    </div>

                    {{-- Paso 2: Condiciones (opcional) --}}
                    <div x-show="step === 2">
                        <p class="text-sm text-gray-500 mb-3">Opcional: Aplicar filtros que deben cumplirse para que la automatización continúe.</p>

                        <template x-for="(condition, index) in conditions" :key="index">
                            <div class="flex gap-2 mb-2 items-center">
                                <select :name="`conditions[${index}][logic]`" x-model="condition.logic"
                                        x-show="index > 0" class="border-gray-300 rounded-md w-24">
                                    <option value="and">Y (AND)</option>
                                    <option value="or">O (OR)</option>
                                </select>

                                <input type="text" :name="`conditions[${index}][field]`" x-model="condition.field"
                                       placeholder="Campo" class="border-gray-300 rounded-md w-1/4">

                                <select :name="`conditions[${index}][operator]`" x-model="condition.operator"
                                        class="border-gray-300 rounded-md w-1/4">
                                    <option value="equals">Igual a</option>
                                    <option value="not_equals">Distinto de</option>
                                    <option value="contains">Contiene</option>
                                    <option value="not_contains">No contiene</option>
                                    <option value="starts_with">Empieza con</option>
                                    <option value="ends_with">Termina con</option>
                                    <option value="greater_than">Mayor que</option>
                                    <option value="less_than">Menor que</option>
                                </select>

                                <input type="text" :name="`conditions[${index}][value]`" x-model="condition.value"
                                       placeholder="Valor" class="border-gray-300 rounded-md w-1/4">
                                <button type="button" @click="removeCondition(index)" class="text-red-500">✕</button>
                            </div>
                        </template>

                        <button type="button" @click="addCondition()" class="text-indigo-600 text-sm mt-2">
                            + Agregar condición
                        </button>
                    </div>

                    {{-- Paso 3: Acciones --}}
                    <div x-show="step === 3">
                        <p class="text-sm text-gray-500 mb-3">Se ejecutan en el orden agregado.</p>

                        <template x-for="(action, index) in actions" :key="index">
                            <div class="flex gap-2 mb-2">
                                <select :name="`actions[${index}][type]`" x-model="action.type"
                                        class="border-gray-300 rounded-md w-1/3">
                                    <option value="">-- Tipo --</option>
                                    @foreach ($actionTypes as $value => $label)
                                        <option value="{{ $value }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                                <input type="text" :name="`actions[${index}][value]`" x-model="action.value"
                                       placeholder="Configuración" class="border-gray-300 rounded-md w-1/2">
                                <button type="button" @click="removeAction(index)" class="text-red-500">✕</button>
                            </div>
                        </template>

                        <button type="button" @click="addAction()" class="text-indigo-600 text-sm mt-2">
                            + Agregar acción
                        </button>
                    </div>

                    {{-- Navegación entre pasos --}}
                    <div class="flex justify-between mt-8">
                        <button type="button" @click="step--" x-show="step > 1"
                                class="px-4 py-2 bg-gray-200 rounded-md">
                            Anterior
                        </button>
                        <span x-show="step === 1"></span>

                        <button type="button" @click="step++" x-show="step < 3"
                                class="px-4 py-2 bg-indigo-600 text-white rounded-md">
                            Siguiente
                        </button>
                        <button type="submit" x-show="step === 3"
                                class="px-4 py-2 bg-green-600 text-white rounded-md">
                            Guardar automatización
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>