<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Database User: ') }} {{ $databaseUser->username }} @ {{ $databaseUser->host }}
            </h2>
            <div class="space-x-2">
                <a href="{{ route('database-users.edit', $databaseUser) }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700">
                    Edit
                </a>
                <form action="{{ route('database-users.destroy', $databaseUser) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this user?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700">
                        Delete
                    </button>
                </form>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('password'))
                <div class="mb-4 bg-yellow-50 border-l-4 border-yellow-400 p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-yellow-700 font-semibold">
                                Generated Password (save this now - it won't be shown again):
                            </p>
                            <p class="mt-2 text-sm font-mono bg-gray-100 p-2 rounded">
                                {{ session('password') }}
                            </p>
                        </div>
                    </div>
                </div>
            @endif

            <!-- User Info -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-semibold mb-4">User Information</h3>
                    <dl class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Username</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $databaseUser->username }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Host</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $databaseUser->host }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Status</dt>
                            <dd class="mt-1">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $databaseUser->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                    {{ $databaseUser->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Created</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $databaseUser->created_at->format('Y-m-d H:i:s') }}</dd>
                        </div>
                        @if($databaseUser->notes)
                        <div class="sm:col-span-2">
                            <dt class="text-sm font-medium text-gray-500">Notes</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $databaseUser->notes }}</dd>
                        </div>
                        @endif
                    </dl>
                </div>
            </div>

            <!-- Assigned Databases -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-semibold mb-4">Assigned Databases ({{ $databaseUser->databases->count() }})</h3>

                    @if($databaseUser->databases->count() > 0)
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Database</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Privileges</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($databaseUser->databases as $database)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <a href="{{ route('databases.show', $database) }}" class="text-blue-600 hover:text-blue-800">
                                                {{ $database->database_name }}
                                            </a>
                                        </td>
                                        <td class="px-6 py-4">
                                            @foreach(json_decode($database->pivot->privileges, true) ?? [] as $privilege)
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 mr-2 mb-2">
                                                    {{ $privilege }}
                                                </span>
                                            @endforeach
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <form action="{{ route('permissions.revoke', $database->pivot->id) }}" method="POST" class="inline" onsubmit="return confirm('Revoke all privileges?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 hover:text-red-900">Revoke</button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <p class="text-gray-500 text-center py-4">No databases assigned to this user.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
