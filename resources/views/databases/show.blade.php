<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Database: ') }} {{ $database->database_name }}
            </h2>
            <div class="flex items-center space-x-2">
                @if($permissions->count() > 0)
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open" @click.outside="open = false"
                                class="inline-flex items-center px-4 py-2 bg-orange-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-orange-600">
                            Explorer
                            <svg class="ml-1 w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/>
                            </svg>
                        </button>
                        <div x-show="open" x-cloak
                             class="absolute right-0 mt-2 w-64 bg-white rounded-md shadow-lg border border-gray-200 z-50">
                            <div class="py-1">
                                <p class="px-4 py-2 text-xs text-gray-500 uppercase font-semibold border-b">Se connecter en tant que</p>
                                @foreach($permissions as $permission)
                                    <a href="{{ route('databases.explorer', [$database, $permission->databaseUser]) }}"
                                       class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-orange-50 hover:text-orange-700">
                                        <span class="font-mono">{{ $permission->databaseUser->username }}</span>
                                        <span class="text-gray-400 ml-1">@{{ $permission->databaseUser->host }}</span>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif
                <a href="{{ route('databases.edit', $database) }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700">
                    Edit
                </a>
                <form action="{{ route('databases.destroy', $database) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this database? All data will be lost!')">
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

            <!-- Database Info -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-semibold mb-4">Database Information</h3>
                    <dl class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Database Name</dt>
                            <dd class="mt-1 text-sm text-gray-900 font-mono">{{ $database->database_name }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Character Set</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $database->charset }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Collation</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $database->collation }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Status</dt>
                            <dd class="mt-1">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $database->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                    {{ $database->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Created</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $database->created_at->format('Y-m-d H:i:s') }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Last Modified</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $database->updated_at->format('Y-m-d H:i:s') }}</dd>
                        </div>
                        @if($database->description)
                        <div class="sm:col-span-2">
                            <dt class="text-sm font-medium text-gray-500">Description</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $database->description }}</dd>
                        </div>
                        @endif
                    </dl>
                </div>
            </div>

            <!-- Assign New User -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-semibold mb-4">Assign User to Database</h3>

                    <form method="POST" action="{{ route('permissions.assign') }}">
                        @csrf
                        <input type="hidden" name="managed_database_id" value="{{ $database->id }}">

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label for="database_user_id" class="block text-sm font-medium text-gray-700">Select User</label>
                                <select name="database_user_id" id="database_user_id" required
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500">
                                    <option value="">-- Choose User --</option>
                                    @foreach($availableUsers as $user)
                                        <option value="{{ $user->id }}">{{ $user->username }}@{{ $user->host }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Privileges</label>
                                <div class="mt-1 space-y-2">
                                    <label class="inline-flex items-center mr-4">
                                        <input type="checkbox" name="privileges[]" value="SELECT" class="rounded border-gray-300 text-green-600">
                                        <span class="ml-2 text-sm">SELECT</span>
                                    </label>
                                    <label class="inline-flex items-center mr-4">
                                        <input type="checkbox" name="privileges[]" value="INSERT" class="rounded border-gray-300 text-green-600">
                                        <span class="ml-2 text-sm">INSERT</span>
                                    </label>
                                    <label class="inline-flex items-center mr-4">
                                        <input type="checkbox" name="privileges[]" value="UPDATE" class="rounded border-gray-300 text-green-600">
                                        <span class="ml-2 text-sm">UPDATE</span>
                                    </label>
                                    <label class="inline-flex items-center mr-4">
                                        <input type="checkbox" name="privileges[]" value="DELETE" class="rounded border-gray-300 text-green-600">
                                        <span class="ml-2 text-sm">DELETE</span>
                                    </label>
                                </div>
                                <div class="mt-2 space-y-2">
                                    <label class="inline-flex items-center mr-4">
                                        <input type="checkbox" name="privileges[]" value="CREATE" class="rounded border-gray-300 text-green-600">
                                        <span class="ml-2 text-sm">CREATE</span>
                                    </label>
                                    <label class="inline-flex items-center mr-4">
                                        <input type="checkbox" name="privileges[]" value="DROP" class="rounded border-gray-300 text-green-600">
                                        <span class="ml-2 text-sm">DROP</span>
                                    </label>
                                    <label class="inline-flex items-center mr-4">
                                        <input type="checkbox" name="privileges[]" value="ALTER" class="rounded border-gray-300 text-green-600">
                                        <span class="ml-2 text-sm">ALTER</span>
                                    </label>
                                    <label class="inline-flex items-center">
                                        <input type="checkbox" name="privileges[]" value="ALL PRIVILEGES" class="rounded border-gray-300 text-green-600">
                                        <span class="ml-2 text-sm font-semibold">ALL PRIVILEGES</span>
                                    </label>
                                </div>
                            </div>

                            <div class="flex items-end">
                                <button type="submit" class="w-full inline-flex justify-center items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700">
                                    Assign User
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Assigned Users -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-semibold mb-4">Assigned Users ({{ $database->users->count() }})</h3>

                    @if($database->users->count() > 0)
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Host</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Privileges</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($permissions as $permission)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <a href="{{ route('database-users.show', $permission->databaseUser) }}" class="text-blue-600 hover:text-blue-800">
                                                {{ $permission->databaseUser->username }}
                                            </a>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $permission->databaseUser->host }}
                                        </td>
                                        <td class="px-6 py-4">
                                            @foreach(is_array($permission->privileges) ? $permission->privileges : json_decode($permission->privileges, true) as $privilege)
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 mr-2 mb-2">
                                                    {{ $privilege }}
                                                </span>
                                            @endforeach
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-3">
                                            <a href="{{ route('databases.explorer', [$database, $permission->databaseUser]) }}"
                                               class="text-orange-600 hover:text-orange-900 font-medium">Explorer</a>
                                            <form action="{{ route('permissions.revoke', $permission) }}" method="POST" class="inline" onsubmit="return confirm('Revoke all privileges for this user?')">
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
                        <p class="text-gray-500 text-center py-4">No users assigned to this database.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
