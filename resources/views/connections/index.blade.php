<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Mis conexiones') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if (session('status'))
                <div class="p-4 bg-green-100 border border-green-300 text-green-800 rounded-md">
                    {{ session('status') }}
                </div>
            @endif

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg divide-y divide-gray-200 dark:divide-gray-700">
                @foreach ($providers as $key => $label)
                    <div class="p-6 flex items-center justify-between">
                        <div>
                            <p class="text-lg font-medium text-gray-900 dark:text-gray-100">{{ $label }}</p>

                            @if ($connectedAccounts->has($key))
                                <p class="text-sm text-gray-500 dark:text-gray-400">
                                    {{ __('Conectado el') }} {{ $connectedAccounts[$key]->created_at->format('d/m/Y') }}
                                </p>
                            @else
                                <p class="text-sm text-gray-500 dark:text-gray-400">
                                    {{ __('No conectado') }}
                                </p>
                            @endif
                        </div>

                        @if ($connectedAccounts->has($key))
                            <div x-data="">
                                <x-danger-button
                                    type="button"
                                    x-on:click="$dispatch('open-modal', 'revoke-{{ $key }}')"
                                >
                                    {{ __('Revocar') }}
                                </x-danger-button>

                                <x-modal name="revoke-{{ $key }}" focusable>
                                    <div class="p-6">
                                        <div class="flex items-center gap-3 mb-4">
                                            <svg width="28" height="28" viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg">
                                                <path d="M50,20 Q75,35 78,68" fill="none" stroke="#94a3b8" stroke-width="4" stroke-linecap="round"/>
                                                <path d="M78,68 Q50,85 22,68" fill="none" stroke="#94a3b8" stroke-width="4" stroke-linecap="round"/>
                                                <path d="M22,68 Q25,35 50,20" fill="none" stroke="#94a3b8" stroke-width="4" stroke-linecap="round"/>
                                                <polygon points="46.4,22.9 41.9,29.1 38.9,23.9" fill="#94a3b8"/>
                                                <circle cx="50" cy="20" r="11" fill="#6366f1"/>
                                                <circle cx="78" cy="68" r="11" fill="#14b8a6"/>
                                                <circle cx="22" cy="68" r="11" fill="#f59e0b"/>
                                            </svg>
                                            <span class="text-sm font-medium text-gray-500 dark:text-gray-400">FlowHub</span>
                                        </div>

                                        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                                            {{ __('¿Revocar la conexión con') }} {{ $label }}?
                                        </h2>

                                        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                            {{ __('FlowHub dejará de tener acceso a tu cuenta de') }} {{ $label }}. {{ __('Podés volver a conectarla cuando quieras.') }}
                                        </p>

                                        <form method="POST" action="{{ route('connections.destroy', $connectedAccounts[$key]) }}" class="mt-6 flex justify-end">
                                            @csrf
                                            @method('DELETE')

                                            <x-secondary-button type="button" x-on:click="$dispatch('close')">
                                                {{ __('Cancelar') }}
                                            </x-secondary-button>

                                            <x-danger-button type="submit" class="ms-3">
                                                {{ __('Revocar') }}
                                            </x-danger-button>
                                        </form>
                                    </div>
                                </x-modal>
                            </div>
                        @else
                            <a href="{{ route('connect.redirect', $key) }}">
                                <x-primary-button type="button">
                                    {{ __('Conectar') }}
                                </x-primary-button>
                            </a>
                        @endif
                    </div>
                @endforeach
            </div>

        </div>
    </div>
</x-app-layout>