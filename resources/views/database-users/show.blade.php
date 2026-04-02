<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-100 leading-tight">
                <span class="font-mono text-blue-400">{{ $databaseUser->username }}</span>
                <span class="text-gray-500">@</span>
                <span class="text-gray-400">{{ $databaseUser->host }}</span>
            </h2>
            <div class="flex items-center gap-2">
                <a href="{{ route('database-users.edit', $databaseUser) }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-500 transition">
                    Edit
                </a>
                <form action="{{ route('database-users.destroy', $databaseUser) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this user?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-500 transition">
                        Delete
                    </button>
                </form>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="mb-6 flex items-center bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 px-4 py-3 rounded-lg">
                    <svg class="w-5 h-5 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                    {{ session('success') }}
                </div>
            @endif

            @if(session('password'))
                <div class="mb-6 bg-amber-500/10 border border-amber-500/20 rounded-lg p-4">
                    <div class="flex">
                        <svg class="h-5 w-5 text-amber-400 flex-shrink-0" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                        </svg>
                        <div class="ml-3">
                            <p class="text-sm text-amber-300 font-semibold">
                                Generated Password (save this now - it won't be shown again):
                            </p>
                            <p class="mt-2 text-sm font-mono bg-gray-800 text-amber-200 p-3 rounded-lg border border-gray-700 select-all">
                                {{ session('password') }}
                            </p>
                        </div>
                    </div>
                </div>
            @endif

            <!-- User Info -->
            <div class="bg-gray-900 border border-gray-800 overflow-hidden rounded-xl mb-6">
                <div class="p-6">
                    <h3 class="text-sm font-medium text-gray-400 uppercase tracking-wider mb-5">User Information</h3>
                    <dl class="grid grid-cols-1 gap-x-6 gap-y-5 sm:grid-cols-2">
                        <div>
                            <dt class="text-xs font-medium text-gray-500 uppercase tracking-wider">Username</dt>
                            <dd class="mt-1 text-sm text-gray-200 font-mono">{{ $databaseUser->username }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium text-gray-500 uppercase tracking-wider">Host</dt>
                            <dd class="mt-1 text-sm text-gray-200 font-mono">{{ $databaseUser->host }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium text-gray-500 uppercase tracking-wider">Status</dt>
                            <dd class="mt-1">
                                @if($databaseUser->is_active)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-500/10 text-emerald-400 ring-1 ring-inset ring-emerald-500/20">Active</span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-500/10 text-red-400 ring-1 ring-inset ring-red-500/20">Inactive</span>
                                @endif
                            </dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium text-gray-500 uppercase tracking-wider">Created</dt>
                            <dd class="mt-1 text-sm text-gray-200 tabular-nums">{{ $databaseUser->created_at->format('Y-m-d H:i:s') }}</dd>
                        </div>
                        @if($databaseUser->notes)
                        <div class="sm:col-span-2">
                            <dt class="text-xs font-medium text-gray-500 uppercase tracking-wider">Notes</dt>
                            <dd class="mt-1 text-sm text-gray-300">{{ $databaseUser->notes }}</dd>
                        </div>
                        @endif
                    </dl>
                </div>
            </div>

            <!-- Assigned Databases -->
            <div class="bg-gray-900 border border-gray-800 overflow-hidden rounded-xl">
                <div class="p-6">
                    <h3 class="text-sm font-medium text-gray-400 uppercase tracking-wider mb-5">Assigned Databases ({{ $databaseUser->databases->count() }})</h3>

                    @if($databaseUser->databases->count() > 0)
                        <div class="overflow-x-auto rounded-lg border border-gray-800">
                            <table class="min-w-full divide-y divide-gray-800">
                                <thead>
                                    <tr class="bg-gray-800/50">
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Database</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Privileges</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-800">
                                    @foreach($databaseUser->databases as $database)
                                        <tr class="hover:bg-gray-800/50 transition-colors">
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <a href="{{ route('databases.show', $database) }}" class="font-mono text-emerald-400 hover:text-emerald-300 transition">
                                                    {{ $database->database_name }}
                                                </a>
                                            </td>
                                            <td class="px-6 py-4">
                                                <div class="flex flex-wrap gap-1.5">
                                                    @foreach(json_decode($database->pivot->privileges, true) ?? [] as $privilege)
                                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-500/10 text-blue-400 ring-1 ring-inset ring-blue-500/20">
                                                            {{ $privilege }}
                                                        </span>
                                                    @endforeach
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                <form action="{{ route('permissions.revoke', $database->pivot->id) }}" method="POST" class="inline" onsubmit="return confirm('Revoke all privileges?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-gray-400 hover:text-red-400 transition">Revoke</button>
                                                </form>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-gray-500 text-center py-8">No databases assigned to this user.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
