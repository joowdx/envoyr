@extends('filament.auth.layout.base')

@section('content')
    <div class="w-full max-w-md mx-auto mt-10 space-y-6">
        <div class="text-center space-y-2">
            <h2 class="text-xl font-bold text-gray-800 dark:text-white">
                Account Awaiting Approval
            </h2>
            <p class="text-sm text-gray-500 dark:text-gray-400">
                Your registration is undergoing the review process. Once completed,
                you will receive an email confirming that your account has been approved and activated.
            </p>
        </div>

        <div class="text-center">
            {{ $this->logoutAction }}
        </div>
    </div>
@endsection
