<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-100 leading-tight">
            {{ __('Create Database') }}
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

                    <form method="POST" action="{{ route('databases.store') }}">
                        @csrf

                        <div class="space-y-5">
                            <div>
                                <label for="database_name" class="block text-sm font-medium text-gray-300">Database Name</label>
                                <input type="text" name="database_name" id="database_name" value="{{ old('database_name') }}" required
                                       class="mt-1 block w-full rounded-lg">
                                <p class="mt-1.5 text-sm text-gray-500">Max 64 characters. Only letters, numbers, and underscores.</p>
                            </div>

                            <div>
                                <label for="charset" class="block text-sm font-medium text-gray-300">Character Set</label>
                                <select name="charset" id="charset" class="mt-1 block w-full rounded-lg">
                                    <option value="utf8mb4" selected>utf8mb4 (Recommended - supports emojis)</option>
                                    <option value="utf8">utf8</option>
                                    <option value="latin1">latin1</option>
                                </select>
                            </div>

                            <div>
                                <label for="collation" class="block text-sm font-medium text-gray-300">Collation</label>
                                <select name="collation" id="collation" class="mt-1 block w-full rounded-lg">
                                    <option value="utf8mb4_unicode_ci" selected>utf8mb4_unicode_ci (Recommended)</option>
                                    <option value="utf8mb4_general_ci">utf8mb4_general_ci</option>
                                    <option value="utf8_unicode_ci">utf8_unicode_ci</option>
                                    <option value="utf8_general_ci">utf8_general_ci</option>
                                    <option value="latin1_swedish_ci">latin1_swedish_ci</option>
                                </select>
                            </div>

                            <div>
                                <label for="description" class="block text-sm font-medium text-gray-300">Description (Optional)</label>
                                <textarea name="description" id="description" rows="3"
                                          class="mt-1 block w-full rounded-lg">{{ old('description') }}</textarea>
                                <p class="mt-1.5 text-sm text-gray-500">Brief description of what this database is for.</p>
                            </div>
                        </div>

                        <div class="flex items-center justify-end gap-3 mt-6 pt-6 border-t border-gray-800">
                            <a href="{{ route('databases.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-gray-600 rounded-lg font-semibold text-xs text-gray-300 uppercase tracking-widest hover:bg-gray-700 transition">
                                Cancel
                            </a>
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-emerald-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-emerald-500 transition">
                                Create Database
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
