{{-- filepath: /Users/johnfordleonielbalignot/envoyr/envoyr/resources/views/auth/register.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="min-h-screen flex items-center justify-center bg-gray-100">
    <div class="bg-white p-8 rounded-lg shadow-md w-96">
        <h2 class="text-2xl font-bold mb-6">Complete Registration</h2>
        
        <p class="mb-4 text-gray-600">
            You've been invited to join as <strong>{{ $user->role->getLabel() }}</strong>{{ $user->office ? ' at ' . $user->office->name : '' }}.
        </p>

        @if($user->designation)
            <p class="mb-4 text-sm bg-blue-50 border border-blue-200 rounded p-3">
                <strong>Your designated position:</strong> {{ $user->designation }}
            </p>
        @endif

        <form method="POST" action="{{ request()->fullUrl() }}">
            @csrf
            
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2">
                    Full Name
                </label>
                <input type="text" name="name" value="{{ old('name') }}" required
                       class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:border-blue-500">
                @error('name')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
            </div>

            {{-- Show designation field only if not set during invitation --}}
            @if($needsDesignation)
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2">
                        Designation
                    </label>
                    <input type="text" name="designation" value="{{ old('designation') }}" required
                           placeholder="Enter your designation (e.g., Manager, Officer, Assistant)"
                           class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:border-blue-500">
                    @error('designation')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                </div>
            @endif

            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2">
                    Password
                </label>
                <input type="password" name="password" required
                           class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:border-blue-500">
                @error('password')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
            </div>

            <div class="mb-6">
                <label class="block text-gray-700 text-sm font-bold mb-2">
                    Confirm Password
                </label>
                <input type="password" name="password_confirmation" required
                           class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:border-blue-500">
            </div>

            <button type="submit" 
                    class="w-full bg-blue-500 text-white py-2 px-4 rounded-lg hover:bg-blue-600 transition duration-200">
                Complete Registration
            </button>
        </form>
    </div>
</div>
@endsection