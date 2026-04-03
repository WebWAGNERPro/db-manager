<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-sm text-gray-100 leading-tight">
            {{ __('Profile') }}
        </h2>
    </x-slot>

    <div class="p-5">
        <div class="max-w-5xl mx-auto space-y-6">
            <div class="p-4 sm:p-8 bg-gray-900 border border-gray-800 shadow rounded-xl">
                <div class="max-w-xl">
                    @include('profile.partials.update-profile-information-form')
                </div>
            </div>

            <div class="p-4 sm:p-8 bg-gray-900 border border-gray-800 shadow rounded-xl">
                <div class="max-w-xl">
                    @include('profile.partials.update-password-form')
                </div>
            </div>

            <div class="p-4 sm:p-8 bg-gray-900 border border-gray-800 shadow rounded-xl">
                <div class="max-w-xl">
                    @include('profile.partials.delete-user-form')
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
