<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Jobs fallidos') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
                {{ __('Trabajos que agotaron sus reintentos y quedaron sin procesar (cola de mensajes fallidos).') }}
            </p>

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg divide-y divide-gray-200 dark:divide-gray-700">
                @forelse ($failedJobs as $job)
                    <div class="p-6">
                        <div class="flex items-center justify-between gap-4">
                            <p class="text-lg font-medium text-gray-900 dark:text-gray-100">{{ $job['job_class'] }}</p>
                            <p class="text-sm text-gray-500 dark:text-gray-400 whitespace-nowrap">
                                {{ $job['failed_at']->format('d/m/Y H:i:s') }}
                            </p>
                        </div>
                        <p class="mt-2 text-sm text-red-600 dark:text-red-400 break-all">
                            {{ $job['exception_summary'] }}
                        </p>
                    </div>
                @empty
                    <div class="p-6 text-sm text-gray-500 dark:text-gray-400">
                        {{ __('No hay jobs fallidos por el momento.') }}
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</x-app-layout>
