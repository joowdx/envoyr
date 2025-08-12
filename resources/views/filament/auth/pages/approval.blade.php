@extends('filament.auth.layout.base')

@section('content')
<p class="text-sm text-center text-gray-500 dark:text-gray-400">
    Please wait for the approval of your account or contact the administrator for more information.
</p>

{{ $this->logoutAction }}
@endsection
