<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-100 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <!-- Stats -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                <!-- Database Users Card -->
                <div class="bg-gray-900 border border-gray-800 overflow-hidden rounded-xl p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-sm font-medium text-gray-400 uppercase tracking-wider">Database Users</h3>
                        <span class="flex items-center justify-center w-10 h-10 rounded-lg bg-blue-500/10">
                            <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                        </span>
                    </div>
                    <p class="text-4xl font-bold text-blue-400 tabular-nums">{{ \App\Models\DatabaseUser::count() }}</p>
                    <div class="mt-4 flex items-center gap-4">
                        <a href="{{ route('database-users.index') }}" class="text-sm text-gray-400 hover:text-blue-400 transition">
                            Voir tout &rarr;
                        </a>
                        <a href="{{ route('database-users.create') }}" class="inline-flex items-center px-3 py-1.5 bg-blue-600 rounded-lg font-semibold text-xs text-white hover:bg-blue-500 transition">
                            + Nouveau
                        </a>
                    </div>
                </div>

                <!-- Databases Card -->
                <div class="bg-gray-900 border border-gray-800 overflow-hidden rounded-xl p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-sm font-medium text-gray-400 uppercase tracking-wider">Databases</h3>
                        <span class="flex items-center justify-center w-10 h-10 rounded-lg bg-emerald-500/10">
                            <svg class="w-5 h-5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4"/></svg>
                        </span>
                    </div>
                    <p class="text-4xl font-bold text-emerald-400 tabular-nums">{{ \App\Models\ManagedDatabase::count() }}</p>
                    <div class="mt-4 flex items-center gap-4">
                        <a href="{{ route('databases.index') }}" class="text-sm text-gray-400 hover:text-emerald-400 transition">
                            Voir tout &rarr;
                        </a>
                        <a href="{{ route('databases.create') }}" class="inline-flex items-center px-3 py-1.5 bg-emerald-600 rounded-lg font-semibold text-xs text-white hover:bg-emerald-500 transition">
                            + Nouvelle
                        </a>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="bg-gray-900 border border-gray-800 overflow-hidden rounded-xl p-6">
                <h3 class="text-sm font-medium text-gray-400 uppercase tracking-wider mb-5">Actions rapides</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <a href="{{ route('database-users.create') }}" class="group p-4 rounded-lg border border-gray-800 hover:border-blue-500/50 hover:bg-blue-500/5 transition-all duration-200">
                        <div class="text-blue-400 font-semibold group-hover:text-blue-300 transition">Nouvel utilisateur</div>
                        <div class="text-sm text-gray-500 mt-1">Ajouter un utilisateur de base de donnees</div>
                    </a>
                    <a href="{{ route('databases.create') }}" class="group p-4 rounded-lg border border-gray-800 hover:border-emerald-500/50 hover:bg-emerald-500/5 transition-all duration-200">
                        <div class="text-emerald-400 font-semibold group-hover:text-emerald-300 transition">Nouvelle base</div>
                        <div class="text-sm text-gray-500 mt-1">Creer une nouvelle base de donnees</div>
                    </a>
                    <a href="{{ route('database-users.index') }}" class="group p-4 rounded-lg border border-gray-800 hover:border-purple-500/50 hover:bg-purple-500/5 transition-all duration-200">
                        <div class="text-purple-400 font-semibold group-hover:text-purple-300 transition">Permissions</div>
                        <div class="text-sm text-gray-500 mt-1">Gerer les permissions des utilisateurs</div>
                    </a>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
