@extends('filament.auth.layout.base')

@section('content')

@if($this->isAdministrator())
    <form wire:submit="assign" class="w-full space-y-4">
        {{ $this->form }}

        <x-filament::button type="submit" class="w-full">
            Affiliate
        </x-filament::button>
    </form>
@else
    <p class="text-sm text-center text-gray-500 dark:text-gray-400">
        Please contact your administrator to request office and section assignment.
    </p>
@endif

{{ $this->logoutAction }}
@endsection
