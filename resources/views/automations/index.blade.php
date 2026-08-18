<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Mis automatizaciones') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">

                <div class="mb-4">
                    <a href="{{ route('automations.create') }}" class="px-4 py-2 bg-indigo-600 text-white rounded-md text-sm">
                        {{ __('+ Nueva automatización') }}
                    </a>
                </div>

                @if ($automations->isEmpty())
                    <p class="text-gray-500 dark:text-gray-400">
                        {{ __('No tienes automatizaciones todavía.') }}
                    </p>
                @else
                    <ul class="divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach ($automations as $automation)
                            <li class="py-4 flex items-center justify-between">
                                <span class="text-gray-900 dark:text-gray-100">
                                    {{ $automation->name }}
                                </span>

                                <div class="flex items-center gap-3">
                                    @if ($automation->is_active)
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                            {{ __('Activa') }}
                                        </span>
                                    @else
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-600">
                                            {{ __('Inactiva') }}
                                        </span>
                                    @endif

                                    <form method="POST" action="{{ route('automations.toggle', $automation) }}">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="text-sm text-indigo-600 hover:underline">
                                            {{ $automation->is_active ? __('Desactivar') : __('Activar') }}
                                        </button>
                                    </form>

                                    <form method="POST" action="{{ route('automations.destroy', $automation) }}"
                                          onsubmit="return confirm('¿Eliminar esta automatización? Esta acción no se puede deshacer.');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-sm text-red-600 hover:underline">
                                            {{ __('Eliminar') }}
                                        </button>
                                    </form>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>