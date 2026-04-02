<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Explorer - {{ config('app.name', 'Laravel') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased overflow-hidden">
    <div class="h-screen flex flex-col bg-gray-950" x-data="explorerSidebar()" x-init="init()">

        {{-- Top bar --}}
        <header class="h-11 bg-gray-900 border-b border-gray-800 flex items-center justify-between px-4 flex-shrink-0 z-20">
            <div class="flex items-center gap-4">
                <a href="{{ route('dashboard') }}" class="text-gray-500 hover:text-gray-200 transition" title="Dashboard">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                </a>
                <span class="text-gray-800">|</span>
                <span class="text-xs font-bold text-orange-400 uppercase tracking-wider">Explorer</span>
                <a href="{{ route('databases.index') }}" class="text-xs text-gray-500 hover:text-gray-300 transition">Databases</a>
                <a href="{{ route('database-users.index') }}" class="text-xs text-gray-500 hover:text-gray-300 transition">Users</a>
            </div>
            <div class="flex items-center gap-3 text-xs">
                <span class="text-gray-600">{{ Auth::user()->name }}</span>
                <a href="{{ route('profile.edit') }}" class="text-gray-500 hover:text-gray-300 transition">Profil</a>
                <form method="POST" action="{{ route('logout') }}" class="inline">
                    @csrf
                    <button type="submit" class="text-gray-500 hover:text-red-400 transition">Deconnexion</button>
                </form>
            </div>
        </header>

        <div class="flex flex-1 overflow-hidden">

            {{-- ======== SIDEBAR ======== --}}
            <aside class="w-60 bg-gray-900 border-r border-gray-800 flex flex-col flex-shrink-0 overflow-hidden">

                {{-- Search --}}
                <div class="p-2 border-b border-gray-800">
                    <div class="relative">
                        <svg class="absolute left-2 top-[7px] w-3.5 h-3.5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        <input type="text" x-model="search" placeholder="Filtrer..."
                               class="w-full pl-7 pr-2 py-1 text-xs bg-gray-800/50 border-0 rounded text-gray-300 placeholder-gray-600 focus:ring-1 focus:ring-indigo-500">
                    </div>
                </div>

                {{-- Tree --}}
                <div class="flex-1 overflow-y-auto py-1 select-none">
                    <template x-if="databases.length === 0">
                        <div class="px-3 py-8 text-center text-gray-600 text-xs">Aucune base disponible</div>
                    </template>

                    <template x-for="db in filteredDatabases" :key="db.id">
                        <div>
                            <template x-for="user in db.users" :key="db.id + '-' + user.id">
                                <div>
                                    {{-- Database entry --}}
                                    <button @click="toggle(db.id, user.id)"
                                            class="w-full flex items-center gap-1 px-2 py-[5px] text-left hover:bg-gray-800/60 transition-colors group"
                                            :class="isActiveDb(db.id, user.id) ? 'bg-gray-800/40' : ''">
                                        <svg class="w-2.5 h-2.5 text-gray-600 transition-transform duration-150 flex-shrink-0"
                                             :class="isExpanded(db.id, user.id) ? 'rotate-90' : ''" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                                        </svg>
                                        <svg class="w-3.5 h-3.5 flex-shrink-0" :class="isExpanded(db.id, user.id) ? 'text-orange-400' : 'text-gray-500'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4"/></svg>
                                        <span class="truncate font-mono text-xs" :class="isActiveDb(db.id, user.id) ? 'text-orange-400 font-semibold' : 'text-gray-300'" x-text="db.name"></span>
                                        <template x-if="db.users.length > 1">
                                            <span class="ml-auto text-[9px] text-gray-600 font-mono" x-text="user.username"></span>
                                        </template>
                                        <template x-if="isLoading(db.id, user.id)">
                                            <svg class="w-3 h-3 ml-auto text-gray-500 animate-spin flex-shrink-0" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                                        </template>
                                    </button>

                                    {{-- Tables --}}
                                    <div x-show="isExpanded(db.id, user.id)" x-collapse.duration.150ms>
                                        <template x-if="getTables(db.id, user.id)">
                                            <div>
                                                <template x-for="tbl in getFilteredTables(db.id, user.id)" :key="tbl.name">
                                                    <a :href="'/explorer/' + db.id + '/' + user.id + '/' + tbl.name"
                                                       class="flex items-center gap-1.5 pl-8 pr-2 py-[4px] hover:bg-gray-800/60 transition-colors"
                                                       :class="isActiveTable(db.id, user.id, tbl.name) ? 'bg-indigo-500/10 text-indigo-300' : 'text-gray-400'">
                                                        <svg class="w-3 h-3 flex-shrink-0" :class="isActiveTable(db.id, user.id, tbl.name) ? 'text-indigo-400' : 'text-gray-700'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                                                        <span class="truncate font-mono text-[11px]" x-text="tbl.name"></span>
                                                        <span class="ml-auto text-[9px] tabular-nums flex-shrink-0"
                                                              :class="isActiveTable(db.id, user.id, tbl.name) ? 'text-indigo-500' : 'text-gray-700'"
                                                              x-text="Number(tbl.rows).toLocaleString()"></span>
                                                    </a>
                                                </template>
                                                <template x-if="getFilteredTables(db.id, user.id).length === 0">
                                                    <div class="pl-8 pr-2 py-2 text-[11px] text-gray-600 italic">Aucune table</div>
                                                </template>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </template>
                </div>

                {{-- Footer --}}
                <div class="px-3 py-2 border-t border-gray-800 text-[10px] text-gray-600 flex justify-between">
                    <span x-text="databases.length + ' base(s)'"></span>
                    <a href="{{ route('databases.create') }}" class="text-gray-500 hover:text-emerald-400 transition">+ Nouvelle</a>
                </div>
            </aside>

            {{-- ======== MAIN ======== --}}
            <main class="flex-1 overflow-y-auto">
                @yield('content')
            </main>

        </div>
    </div>

    <script>
    function explorerSidebar() {
        return {
            databases: @json($sidebarData ?? []),
            expanded: {},
            tables: {},
            loading: {},
            search: '',
            currentDbId: {{ $database->id ?? 'null' }},
            currentUserId: {{ $databaseUser->id ?? 'null' }},
            currentTable: @json($currentTable ?? ''),

            init() {
                if (this.currentDbId && this.currentUserId) {
                    this.toggle(this.currentDbId, this.currentUserId);
                }
            },
            key(d, u) { return d + '-' + u; },
            isExpanded(d, u) { return !!this.expanded[this.key(d, u)]; },
            isLoading(d, u) { return !!this.loading[this.key(d, u)]; },
            isActiveDb(d, u) { return this.currentDbId === d && this.currentUserId === u; },
            isActiveTable(d, u, t) { return this.isActiveDb(d, u) && this.currentTable === t; },
            getTables(d, u) { return this.tables[this.key(d, u)] || null; },
            getFilteredTables(d, u) {
                let t = this.tables[this.key(d, u)] || [];
                if (!this.search) return t;
                let s = this.search.toLowerCase();
                return t.filter(x => x.name.toLowerCase().includes(s));
            },
            get filteredDatabases() {
                if (!this.search) return this.databases;
                let s = this.search.toLowerCase();
                return this.databases.filter(db => {
                    if (db.name.toLowerCase().includes(s)) return true;
                    for (let u of db.users) {
                        let t = this.tables[this.key(db.id, u.id)];
                        if (t && t.some(x => x.name.toLowerCase().includes(s))) return true;
                    }
                    return false;
                });
            },
            async toggle(d, u) {
                let k = this.key(d, u);
                if (this.expanded[k]) { this.expanded[k] = false; return; }
                this.expanded[k] = true;
                if (!this.tables[k]) await this.loadTables(d, u);
            },
            async loadTables(d, u) {
                let k = this.key(d, u);
                this.loading[k] = true;
                try {
                    let r = await fetch('/explorer/api/' + d + '/' + u + '/tables');
                    this.tables[k] = r.ok ? await r.json() : [];
                } catch(e) { this.tables[k] = []; }
                this.loading[k] = false;
            }
        };
    }
    </script>
</body>
</html>
