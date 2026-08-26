<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Autenticación de dos factores') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">

                @if (session('status'))
                    <div class="mb-4 p-4 bg-green-100 border border-green-300 text-green-800 rounded-md">
                        {{ session('status') }}
                    </div>
                @endif

                @if ($enabled)
                    <p class="text-gray-700 dark:text-gray-300 mb-4">
                        {{ __('El segundo factor de autenticación está activo en tu cuenta.') }}
                    </p>

                    <form method="POST" action="{{ route('two-factor.disable') }}">
                        @csrf
                        <x-danger-button type="submit">
                            {{ __('Desactivar 2FA') }}
                        </x-danger-button>
                    </form>
                @else
                    <p class="text-gray-700 dark:text-gray-300 mb-4">
                        {{ __('Escaneá este código QR con Google Authenticator, y luego ingresá el código de 6 dígitos para confirmar la activación.') }}
                    </p>

                    <div class="mb-4 flex justify-center bg-white p-4 rounded">
                        <img src="data:image/svg+xml;base64,{{ $qrCodeSvg }}" alt="Código QR de 2FA">
                    </div>

                    <p class="text-xs text-gray-500 mb-4 text-center break-all">
                        {{ __('Código manual:') }} <strong>{{ $secret }}</strong>
                    </p>

                    @if ($errors->any())
                        <div class="mb-4 p-4 bg-red-50 text-red-700 rounded">
                            <ul class="list-disc list-inside">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('two-factor.enable') }}">
                        @csrf
                        <x-input-label for="code" value="Código de verificación" />
                        <x-text-input id="code" name="code" type="text" class="mt-1 block w-full" maxlength="6" autofocus />

                        <x-primary-button class="mt-4">
                            {{ __('Activar 2FA') }}
                        </x-primary-button>
                    </form>
                @endif

            </div>
        </div>
    </div>
</x-app-layout>