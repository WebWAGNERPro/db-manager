<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-sm text-gray-100 leading-tight">
                <span class="font-mono text-emerald-400">{{ $database->database_name }}</span>
            </h2>
            <div class="flex items-center gap-2">
                @if($permissions->count() > 0)
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open" @click.outside="open = false"
                                class="inline-flex items-center px-3 py-1.5 bg-orange-600 border border-transparent rounded-lg font-semibold text-xs text-white hover:bg-orange-500 transition">
                            Explorer
                            <svg class="ml-1.5 w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/>
                            </svg>
                        </button>
                        <div x-show="open" x-cloak
                             x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="opacity-0 scale-95"
                             x-transition:enter-end="opacity-100 scale-100"
                             x-transition:leave="transition ease-in duration-75"
                             x-transition:leave-start="opacity-100 scale-100"
                             x-transition:leave-end="opacity-0 scale-95"
                             class="absolute right-0 mt-2 w-64 bg-gray-800 rounded-lg shadow-xl border border-gray-700 z-50">
                            <div class="py-1">
                                <p class="px-4 py-2 text-xs text-gray-500 uppercase font-semibold border-b border-gray-700">Se connecter en tant que</p>
                                @foreach($permissions as $permission)
                                    <a href="{{ route('explorer') }}"
                                       class="flex items-center px-4 py-2.5 text-sm text-gray-300 hover:bg-orange-500/10 hover:text-orange-400 transition">
                                        <span class="font-mono">{{ $permission->databaseUser->username }}</span>
                                        <span class="text-gray-500 ml-1">@{{ $permission->databaseUser->host }}</span>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif
                <a href="{{ route('databases.edit', $database) }}" class="inline-flex items-center px-3 py-1.5 bg-indigo-600 border border-transparent rounded-lg font-semibold text-xs text-white hover:bg-indigo-500 transition">
                    Edit
                </a>
                <form action="{{ route('databases.destroy', $database) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this database? All data will be lost!')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="inline-flex items-center px-3 py-1.5 bg-red-600 border border-transparent rounded-lg font-semibold text-xs text-white hover:bg-red-500 transition">
                        Delete
                    </button>
                </form>
            </div>
        </div>
    </x-slot>

    <div class="p-5">
        <div class="max-w-5xl mx-auto">
            @if(session('success'))
                <div class="mb-6 flex items-center bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 px-4 py-3 rounded-lg">
                    <svg class="w-5 h-5 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                    {{ session('success') }}
                </div>
            @endif

            <!-- Database Info -->
            <div class="bg-gray-900 border border-gray-800 overflow-hidden rounded-xl mb-6">
                <div class="p-6">
                    <h3 class="text-sm font-medium text-gray-400 uppercase tracking-wider mb-5">Database Information</h3>
                    <dl class="grid grid-cols-1 gap-x-6 gap-y-5 sm:grid-cols-3">
                        <div>
                            <dt class="text-xs font-medium text-gray-500 uppercase tracking-wider">Name</dt>
                            <dd class="mt-1 text-sm text-gray-200 font-mono">{{ $database->database_name }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium text-gray-500 uppercase tracking-wider">Charset</dt>
                            <dd class="mt-1 text-sm text-gray-200 font-mono">{{ $database->charset }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium text-gray-500 uppercase tracking-wider">Collation</dt>
                            <dd class="mt-1 text-sm text-gray-200 font-mono">{{ $database->collation }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium text-gray-500 uppercase tracking-wider">Status</dt>
                            <dd class="mt-1">
                                @if($database->is_active)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-500/10 text-emerald-400 ring-1 ring-inset ring-emerald-500/20">Active</span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-500/10 text-red-400 ring-1 ring-inset ring-red-500/20">Inactive</span>
                                @endif
                            </dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium text-gray-500 uppercase tracking-wider">Created</dt>
                            <dd class="mt-1 text-sm text-gray-200 tabular-nums">{{ $database->created_at->format('Y-m-d H:i:s') }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium text-gray-500 uppercase tracking-wider">Last Modified</dt>
                            <dd class="mt-1 text-sm text-gray-200 tabular-nums">{{ $database->updated_at->format('Y-m-d H:i:s') }}</dd>
                        </div>
                        @if($database->description)
                        <div class="sm:col-span-3">
                            <dt class="text-xs font-medium text-gray-500 uppercase tracking-wider">Description</dt>
                            <dd class="mt-1 text-sm text-gray-300">{{ $database->description }}</dd>
                        </div>
                        @endif
                    </dl>
                </div>
            </div>

            <!-- Assign New User -->
            <div class="bg-gray-900 border border-gray-800 overflow-hidden rounded-xl mb-6">
                <div class="p-6">
                    <h3 class="text-sm font-medium text-gray-400 uppercase tracking-wider mb-5">Assign User to Database</h3>

                    <form method="POST" action="{{ route('permissions.assign') }}">
                        @csrf
                        <input type="hidden" name="managed_database_id" value="{{ $database->id }}">

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                            <div>
                                <label for="database_user_id" class="block text-sm font-medium text-gray-300">Select User</label>
                                <select name="database_user_id" id="database_user_id" required
                                        class="mt-1 block w-full rounded-lg">
                                    <option value="">-- Choose User --</option>
                                    @foreach($availableUsers as $user)
                                        <option value="{{ $user->id }}">{{ $user->username }}@{{ $user->host }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-300 mb-2">Privileges</label>
                                <div class="grid grid-cols-2 gap-2">
                                    @foreach(['SELECT', 'INSERT', 'UPDATE', 'DELETE', 'CREATE', 'DROP', 'ALTER'] as $priv)
                                    <label class="flex items-center">
                                        <input type="checkbox" name="privileges[]" value="{{ $priv }}" class="rounded border-gray-600 text-indigo-600 bg-gray-800 focus:ring-indigo-500">
                                        <span class="ml-2 text-sm text-gray-300">{{ $priv }}</span>
                                    </label>
                                    @endforeach
                                    <label class="flex items-center col-span-2 pt-1 border-t border-gray-800">
                                        <input type="checkbox" name="privileges[]" value="ALL PRIVILEGES" class="rounded border-gray-600 text-indigo-600 bg-gray-800 focus:ring-indigo-500">
                                        <span class="ml-2 text-sm font-semibold text-gray-200">ALL PRIVILEGES</span>
                                    </label>
                                </div>
                            </div>

                            <div class="flex items-end">
                                <button type="submit" class="w-full inline-flex justify-center items-center px-4 py-2.5 bg-indigo-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-500 transition">
                                    Assign User
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Assigned Users -->
            <div class="bg-gray-900 border border-gray-800 overflow-hidden rounded-xl">
                <div class="p-6">
                    <h3 class="text-sm font-medium text-gray-400 uppercase tracking-wider mb-5">Assigned Users ({{ $database->users->count() }})</h3>

                    @if($database->users->count() > 0)
                        <div class="overflow-x-auto rounded-lg border border-gray-800">
                            <table class="min-w-full divide-y divide-gray-800">
                                <thead>
                                    <tr class="bg-gray-800/50">
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">User</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Host</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Privileges</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-800">
                                    @foreach($permissions as $permission)
                                        <tr class="hover:bg-gray-800/50 transition-colors">
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <a href="{{ route('database-users.show', $permission->databaseUser) }}" class="font-mono text-blue-400 hover:text-blue-300 transition">
                                                    {{ $permission->databaseUser->username }}
                                                </a>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-400 font-mono">
                                                {{ $permission->databaseUser->host }}
                                            </td>
                                            <td class="px-6 py-4">
                                                <div class="flex flex-wrap gap-1.5">
                                                    @foreach(is_array($permission->privileges) ? $permission->privileges : json_decode($permission->privileges, true) as $privilege)
                                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-emerald-500/10 text-emerald-400 ring-1 ring-inset ring-emerald-500/20">
                                                            {{ $privilege }}
                                                        </span>
                                                    @endforeach
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                <div class="flex items-center gap-3">
                                                    <a href="{{ route('explorer') }}"
                                                       class="text-orange-400 hover:text-orange-300 font-medium transition">Explorer</a>
                                                    <form action="{{ route('permissions.revoke', $permission) }}" method="POST" class="inline" onsubmit="return confirm('Revoke all privileges for this user?')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="text-gray-400 hover:text-red-400 transition">Revoke</button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-gray-500 text-center py-8">No users assigned to this database.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
