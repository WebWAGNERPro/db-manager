@extends('layouts.explorer')

@section('content')
<div class="flex items-center justify-center h-full">
    <div class="text-center max-w-sm px-6">
        <svg class="mx-auto w-14 h-14 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4"/></svg>
        <h2 class="mt-4 text-base font-semibold text-gray-300">Database Explorer</h2>
        <p class="mt-2 text-sm text-gray-500">Selectionnez une table dans le panneau lateral pour commencer.</p>
    </div>
</div>
@endsection
