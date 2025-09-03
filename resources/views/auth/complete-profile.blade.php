{{-- filepath: resources/views/auth/complete-profile.blade.php --}}
@extends('layouts.app')

@section('title', 'Complete Your Profile')

@section('content')
<div class="min-h-screen flex flex-col justify-center items-center bg-gray-100">
    <div class="w-full max-w-md bg-white rounded shadow p-8">
        <h2 class="text-2xl font-bold mb-6 text-center">Complete Your Profile</h2>
        <form method="POST" action="{{ url('/complete-profile') }}">
            @csrf
            <div class="mb-4">
                <label for="name" class="block text-gray-700 font-semibold mb-2">Full Name</label>
                <input id="name" type="text" name="name" required autofocus
                    class="w-full px-3 py-2 border rounded focus:outline-none focus:ring focus:border-blue-300"
                    value="{{ old('name') }}">
                @error('name')
                    <span class="text-red-500 text-sm mt-1 block">{{ $message }}</span>
                @enderror
            </div>
            <button type="submit"
                class="w-full bg-blue-600 text-white py-2 rounded hover:bg-blue-700 transition font-semibold">
                Save
            </button>
        </form>
    </div>
</div>
@endsection