<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Create Database') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    @if($errors->any())
                        <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                            <ul>
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('databases.store') }}">
                        @csrf

                        <div class="mb-4">
                            <label for="database_name" class="block text-sm font-medium text-gray-700">Database Name</label>
                            <input type="text" name="database_name" id="database_name" value="{{ old('database_name') }}" required
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500">
                            <p class="mt-1 text-sm text-gray-500">Max 64 characters. Only letters, numbers, and underscores.</p>
                        </div>

                        <div class="mb-4">
                            <label for="charset" class="block text-sm font-medium text-gray-700">Character Set</label>
                            <select name="charset" id="charset" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500">
                                <option value="utf8mb4" selected>utf8mb4 (Recommended - supports emojis)</option>
                                <option value="utf8">utf8</option>
                                <option value="latin1">latin1</option>
                            </select>
                        </div>

                        <div class="mb-4">
                            <label for="collation" class="block text-sm font-medium text-gray-700">Collation</label>
                            <select name="collation" id="collation" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500">
                                <option value="utf8mb4_unicode_ci" selected>utf8mb4_unicode_ci (Recommended)</option>
                                <option value="utf8mb4_general_ci">utf8mb4_general_ci</option>
                                <option value="utf8_unicode_ci">utf8_unicode_ci</option>
                                <option value="utf8_general_ci">utf8_general_ci</option>
                                <option value="latin1_swedish_ci">latin1_swedish_ci</option>
                            </select>
                        </div>

                        <div class="mb-4">
                            <label for="description" class="block text-sm font-medium text-gray-700">Description (Optional)</label>
                            <textarea name="description" id="description" rows="3"
                                      class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500">{{ old('description') }}</textarea>
                            <p class="mt-1 text-sm text-gray-500">Brief description of what this database is for.</p>
                        </div>

                        <div class="flex items-center justify-end space-x-3">
                            <a href="{{ route('databases.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-300 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-400">
                                Cancel
                            </a>
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700">
                                Create Database
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
