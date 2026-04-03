<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-sm text-gray-100 leading-tight">
                {{ __('Database Users') }}
            </h2>
            <a href="{{ route('database-users.create') }}" class="inline-flex items-center px-3 py-1.5 bg-blue-600 border border-transparent rounded-lg font-semibold text-xs text-white hover:bg-blue-500 transition">
                + Nouveau
            </a>
        </div>
    </x-slot>

    <div class="p-5">
        <div class="max-w-5xl mx-auto">
            @if(session('success'))
                <div class="mb-4 flex items-center bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 px-4 py-3 rounded-lg">
                    <svg class="w-5 h-5 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                    {{ session('success') }}
                </div>
            @endif

            <div class="bg-gray-900 border border-gray-800 overflow-hidden rounded-xl">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-800">
                        <thead>
                            <tr class="bg-gray-800/50">
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Username</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Host</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Databases</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-800">
                            @forelse($users as $user)
                                <tr class="hover:bg-gray-800/50 transition-colors">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <a href="{{ route('database-users.show', $user) }}" class="font-mono text-blue-400 hover:text-blue-300 transition">
                                            {{ $user->username }}
                                        </a>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-400 font-mono">
                                        {{ $user->host }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-400 tabular-nums">
                                        {{ $user->databases->count() }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($user->is_active)
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-500/10 text-emerald-400 ring-1 ring-inset ring-emerald-500/20">Active</span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-500/10 text-red-400 ring-1 ring-inset ring-red-500/20">Inactive</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <div class="flex items-center gap-3">
                                            <a href="{{ route('database-users.show', $user) }}" class="text-gray-400 hover:text-blue-400 transition">View</a>
                                            <a href="{{ route('database-users.edit', $user) }}" class="text-gray-400 hover:text-indigo-400 transition">Edit</a>
                                            <form action="{{ route('database-users.destroy', $user) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-gray-400 hover:text-red-400 transition">Delete</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-12 text-center text-gray-500">
                                        No database users found.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="px-6 py-4 border-t border-gray-800">
                    {{ $users->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
