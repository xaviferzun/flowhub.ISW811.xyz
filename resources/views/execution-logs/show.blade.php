<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Detalle de ejecución') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <a href="{{ route('execution-logs.index') }}" class="text-sm text-indigo-600 hover:underline">
                {{ __('← Volver al historial') }}
            </a>

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                <div class="flex items-center justify-between gap-4">
                    <p class="text-lg font-medium text-gray-900 dark:text-gray-100">
                        {{ $executionLog->automation->name }}
                    </p>

                    @if ($executionLog->status === 'success')
                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                            {{ __('Exitosa') }}
                        </span>
                    @else
                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">
                            {{ __('Fallida') }}
                        </span>
                    @endif
                </div>

                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    {{ $executionLog->created_at->format('d/m/Y H:i:s') }}
                </p>
            </div>

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">{{ __('Datos de entrada') }}</h3>
                <pre class="text-xs bg-gray-50 dark:bg-gray-900 text-gray-800 dark:text-gray-200 rounded-md p-4 overflow-x-auto">{{ json_encode($executionLog->input_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
            </div>

            @if ($executionLog->status === 'success')
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">{{ __('Resultado') }}</h3>
                    <pre class="text-xs bg-gray-50 dark:bg-gray-900 text-gray-800 dark:text-gray-200 rounded-md p-4 overflow-x-auto">{{ json_encode($executionLog->result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                </div>
            @else
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">{{ __('Error completo') }}</h3>
                    <pre class="text-xs bg-red-50 dark:bg-red-950 text-red-700 dark:text-red-300 rounded-md p-4 overflow-x-auto whitespace-pre-wrap break-all">{{ $executionLog->error_detail }}</pre>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
