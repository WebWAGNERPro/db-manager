<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-100 leading-tight">
            {{ __('Edit Database User: ') }} <span class="font-mono text-blue-400">{{ $databaseUser->username }}</span>
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
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

                    <form method="POST" action="{{ route('database-users.update', $databaseUser) }}">
                        @csrf
                        @method('PUT')

                        <div class="space-y-5">
                            <div>
                                <label class="block text-sm font-medium text-gray-300">Username</label>
                                <input type="text" value="{{ $databaseUser->username }}" disabled
                                       class="mt-1 block w-full rounded-lg">
                                <p class="mt-1.5 text-sm text-gray-500">Username cannot be changed.</p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-300">Host</label>
                                <input type="text" value="{{ $databaseUser->host }}" disabled
                                       class="mt-1 block w-full rounded-lg">
                                <p class="mt-1.5 text-sm text-gray-500">Host cannot be changed.</p>
                            </div>

                            <div>
                                <label for="notes" class="block text-sm font-medium text-gray-300">Notes</label>
                                <textarea name="notes" id="notes" rows="3"
                                          class="mt-1 block w-full rounded-lg">{{ old('notes', $databaseUser->notes) }}</textarea>
                            </div>

                            <div>
                                <label class="flex items-center">
                                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', $databaseUser->is_active) ? 'checked' : '' }}
                                           class="rounded border-gray-600 text-indigo-600 shadow-sm focus:ring-indigo-500 bg-gray-800">
                                    <span class="ml-2 text-sm text-gray-300">User is active</span>
                                </label>
                            </div>
                        </div>

                        <div class="flex items-center justify-end gap-3 mt-6 pt-6 border-t border-gray-800">
                            <a href="{{ route('database-users.show', $databaseUser) }}" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-gray-600 rounded-lg font-semibold text-xs text-gray-300 uppercase tracking-widest hover:bg-gray-700 transition">
                                Cancel
                            </a>
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-500 transition">
                                Update User
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
