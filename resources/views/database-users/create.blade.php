<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-sm text-gray-100 leading-tight">
            {{ __('Create Database User') }}
        </h2>
    </x-slot>

    <div class="p-5">
        <div class="max-w-2xl mx-auto">
            <div class="bg-gray-900 border border-gray-800 overflow-hidden rounded-xl">
                <div class="p-6">
                    @if($errors->any())
                        <div class="mb-6 bg-red-500/10 border border-red-500/20 text-red-400 px-4 py-3 rounded-lg">
                            <ul class="list-disc list-inside text-sm">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('database-users.store') }}">
                        @csrf

                        <div class="space-y-5">
                            <div>
                                <label for="username" class="block text-sm font-medium text-gray-300">Username</label>
                                <input type="text" name="username" id="username" value="{{ old('username') }}" required
                                       class="mt-1 block w-full rounded-lg">
                                <p class="mt-1.5 text-sm text-gray-500">Max 32 characters. Only letters, numbers, and underscores.</p>
                            </div>

                            <div>
                                <label for="host" class="block text-sm font-medium text-gray-300">Host</label>
                                <select name="host" id="host" class="mt-1 block w-full rounded-lg">
                                    <option value="localhost" selected>localhost</option>
                                    <option value="127.0.0.1">127.0.0.1</option>
                                    <option value="%">% (any host - not recommended)</option>
                                </select>
                                <p class="mt-1.5 text-sm text-gray-500">Where this user can connect from.</p>
                            </div>

                            <div>
                                <label for="notes" class="block text-sm font-medium text-gray-300">Notes (Optional)</label>
                                <textarea name="notes" id="notes" rows="3"
                                          class="mt-1 block w-full rounded-lg">{{ old('notes') }}</textarea>
                                <p class="mt-1.5 text-sm text-gray-500">Internal notes about this user.</p>
                            </div>

                            <div class="bg-amber-500/10 border border-amber-500/20 rounded-lg p-4">
                                <div class="flex">
                                    <svg class="h-5 w-5 text-amber-400 flex-shrink-0" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                    </svg>
                                    <p class="ml-3 text-sm text-amber-300">
                                        A secure random password will be generated automatically.
                                        <strong>Make sure to save it - it will only be shown once!</strong>
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center justify-end gap-3 mt-6 pt-6 border-t border-gray-800">
                            <a href="{{ route('database-users.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-gray-600 rounded-lg font-semibold text-xs text-gray-300 uppercase tracking-widest hover:bg-gray-700 transition">
                                Cancel
                            </a>
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-500 transition">
                                Create User
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
