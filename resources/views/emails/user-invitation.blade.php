@extends('layouts.app')

@section('content')
    <div class="container mx-auto max-w-2xl p-6">
        <div class="header text-center mb-8">
            <h1 class="text-3xl font-bold text-gray-800">{{ config('app.name') }}</h1>
            <h2 class="text-2xl font-semibold text-blue-600 mt-2">You're Invited!</h2>
        </div>
        
        <div class="content bg-white rounded-lg shadow-md p-8">
            <p class="text-gray-700 mb-4">Hello,</p>
            
            <p class="text-gray-700 mb-6">You have been invited to join <strong>{{ config('app.name') }}</strong> as a <strong>{{ $roleName }}</strong>{{ $officeName ? ' at ' . $officeName : '' }}.</p>
            
            <div class="details bg-gray-50 border-l-4 border-blue-500 p-4 mb-6">
                <strong class="text-gray-800">Your Account Details:</strong><br>
                <div class="mt-2 space-y-1">
                    <div><strong>Email:</strong> {{ $user->email }}</div>
                    <div><strong>Role:</strong> {{ $roleName }}</div>
                    @if($officeName)
                    <div><strong>Office:</strong> {{ $officeName }}</div>
                    @endif
                </div>
            </div>
            
            <p class="text-gray-700 mb-6">To complete your registration, please click the button below:</p>
            
            <div class="text-center mb-8">
                <table cellpadding="0" cellspacing="0" border="0" style="margin: 0 auto;">
                    <tr>
                        <td style="background-color: #2563eb; 
                                   border-radius: 6px; 
                                   padding: 0;">
                            <a href="{{ $registrationUrl }}" 
                               style="display: block; 
                                      padding: 12px 24px; 
                                      background-color: #2563eb; 
                                      color: white; 
                                      text-decoration: none; 
                                      border-radius: 6px; 
                                      font-weight: 500; 
                                      font-size: 16px; 
                                      font-family: Arial, sans-serif;">
                                Complete Registration
                            </a>
                        </td>
                    </tr>
                </table>
            </div>
            
            <div class="mb-6">
                <p class="text-gray-800 font-semibold mb-3">What's next?</p>
                <ol class="list-decimal list-inside space-y-2 text-gray-700 ml-4">
                    <li>Click the registration link above</li>
                    <li>Enter your full name and choose a password</li>
                    <li>Provide your designation/job title</li>
                    <li>Start using the system!</li>
                </ol>
            </div>
            
            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-6">
                <p class="text-yellow-800"><strong>Security Note:</strong> This invitation link is secure and will expire automatically. If you didn't expect this invitation, please ignore this email.</p>
            </div>
            
            <p class="text-gray-700 mb-4">If you have any questions, please contact your system administrator.</p>
            
            <p class="text-blue-600 font-semibold text-lg">Welcome to the team!</p>
        </div>
        
        <div class="footer text-center mt-8 text-gray-500 text-sm">
            <p>This is an automated message from {{ config('app.name') }}.</p>
        </div>
    </div>
@endsection
