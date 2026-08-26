<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Historial de ejecuciones') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            <form method="GET" action="{{ route('execution-logs.index') }}" class="mb-4 flex items-center gap-2">
                <label for="status" class="text-sm text-gray-600 dark:text-gray-400">{{ __('Estado:') }}</label>
                <select
                    name="status"
                    id="status"
                    onchange="this.form.submit()"
                    class="text-sm rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200"
                >
                    <option value="" {{ $status === null ? 'selected' : '' }}>{{ __('Todos') }}</option>
                    <option value="success" {{ $status === 'success' ? 'selected' : '' }}>{{ __('Exitosas') }}</option>
                    <option value="failed" {{ $status === 'failed' ? 'selected' : '' }}>{{ __('Fallidas') }}</option>
                </select>
            </form>

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg divide-y divide-gray-200 dark:divide-gray-700">
                @forelse ($executionLogs as $log)
                    <div class="p-6">
                        <div class="flex items-center justify-between gap-4">
                            <p class="text-lg font-medium text-gray-900 dark:text-gray-100">
                                {{ $log->automation->name }}
                            </p>

                            <div class="flex items-center gap-3">
                                @if ($log->status === 'success')
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                        {{ __('Exitosa') }}
                                    </span>
                                @else
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">
                                        {{ __('Fallida') }}
                                    </span>
                                @endif

                                <p class="text-sm text-gray-500 dark:text-gray-400 whitespace-nowrap">
                                    {{ $log->created_at->format('d/m/Y H:i:s') }}
                                </p>
                            </div>
                        </div>

                        @if ($log->error_detail)
                            <p class="mt-2 text-sm text-red-600 dark:text-red-400 break-all">
                                {{ $log->error_detail }}
                            </p>
                        @endif
                    </div>
                @empty
                    <div class="p-6 text-sm text-gray-500 dark:text-gray-400">
                        {{ __('No hay ejecuciones registradas todavía.') }}
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</x-app-layout>
