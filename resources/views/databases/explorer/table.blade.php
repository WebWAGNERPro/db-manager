<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="font-semibold text-xl text-gray-100 leading-tight">
                    <a href="{{ route('databases.explorer', [$database, $databaseUser]) }}"
                       class="text-orange-400 hover:text-orange-300 font-mono transition">{{ $database->database_name }}</a>
                    <span class="text-gray-600 mx-2">/</span>
                    <span class="font-mono text-gray-100">{{ $table }}</span>
                </h2>
                <p class="text-sm text-gray-500 mt-1">
                    Connecte en tant que
                    <span class="font-mono font-medium text-gray-300">{{ $databaseUser->username }}@{{ $databaseUser->host }}</span>
                    &mdash; {{ number_format($total) }} ligne{{ $total > 1 ? 's' : '' }}
                </p>
            </div>
            <a href="{{ route('databases.explorer', [$database, $databaseUser]) }}"
               class="inline-flex items-center px-4 py-2 bg-gray-800 border border-gray-600 rounded-lg font-semibold text-xs text-gray-300 uppercase tracking-widest hover:bg-gray-700 transition">
                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                Tables
            </a>
        </div>
    </x-slot>

    @php
        $primaryKeys = collect($columns)->filter(fn($col) => ((array) $col)['Key'] === 'PRI')->map(fn($col) => ((array) $col)['Field'])->values()->toArray();
        $columnList = collect($columns)->map(fn($col) => (array) $col)->values()->toArray();
        $hasPk = count($primaryKeys) > 0;
    @endphp

    <div class="py-12" x-data="{
        tab: '{{ request('tab', 'structure') }}',
        editRow: {},
        editPk: {},
        deleteRowPk: {},
        deleteRowPreview: '',
        addFields: [],
        columns: @js($columnList),
        primaryKeys: @js($primaryKeys),
        openEdit(row) {
            this.editRow = { ...row };
            this.editPk = {};
            this.primaryKeys.forEach(pk => { this.editPk[pk] = row[pk]; });
            $dispatch('open-modal', 'edit-row');
        },
        openDelete(row) {
            this.deleteRowPk = {};
            this.primaryKeys.forEach(pk => { this.deleteRowPk[pk] = row[pk]; });
            let entries = Object.entries(row).slice(0, 4);
            this.deleteRowPreview = entries.map(([k,v]) => k + ' = ' + (v === null ? 'NULL' : v)).join('\n');
            if (Object.keys(row).length > 4) this.deleteRowPreview += '\n...';
            $dispatch('open-modal', 'delete-row');
        },
        openAdd() {
            this.addFields = this.columns.map(col => ({
                column: col.Field,
                type: col.Type,
                nullable: col.Null === 'YES',
                autoIncrement: col.Extra.includes('auto_increment'),
                value: '',
                useNull: false,
                skip: col.Extra.includes('auto_increment')
            }));
            $dispatch('open-modal', 'add-row');
        }
    }">
        <div class="max-w-full mx-auto sm:px-6 lg:px-8">

            {{-- Flash messages --}}
            @if($errors->any())
                <div class="mb-6 flex items-center bg-red-500/10 border border-red-500/20 text-red-400 px-5 py-4 rounded-lg" role="alert">
                    <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>
                    <span class="text-sm font-medium">{{ $errors->first() }}</span>
                </div>
            @endif

            @if(session('success'))
                <div class="mb-6 flex items-center bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 px-5 py-4 rounded-lg" role="alert">
                    <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                    <span class="text-sm font-medium">{{ session('success') }}</span>
                </div>
            @endif

            {{-- ======== TAB BAR ======== --}}
            <div class="bg-gray-900 border border-gray-800 rounded-t-xl">
                <div class="px-6">
                    <nav class="-mb-px flex space-x-8">
                        <button @click="tab = 'structure'"
                                :class="tab === 'structure'
                                    ? 'border-indigo-500 text-indigo-400'
                                    : 'border-transparent text-gray-500 hover:text-gray-300 hover:border-gray-600'"
                                class="group inline-flex items-center py-4 px-1 border-b-2 font-medium text-sm transition-colors duration-150">
                            <svg class="w-4 h-4 mr-2 transition-colors duration-150"
                                 :class="tab === 'structure' ? 'text-indigo-400' : 'text-gray-600 group-hover:text-gray-400'"
                                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4"/>
                            </svg>
                            Structure
                            <span class="ml-2 py-0.5 px-2.5 rounded-full text-xs font-medium transition-colors duration-150"
                                  :class="tab === 'structure' ? 'bg-indigo-500/10 text-indigo-400' : 'bg-gray-800 text-gray-500'">
                                {{ count($columns) }}
                            </span>
                        </button>
                        <button @click="tab = 'browse'"
                                :class="tab === 'browse'
                                    ? 'border-emerald-500 text-emerald-400'
                                    : 'border-transparent text-gray-500 hover:text-gray-300 hover:border-gray-600'"
                                class="group inline-flex items-center py-4 px-1 border-b-2 font-medium text-sm transition-colors duration-150">
                            <svg class="w-4 h-4 mr-2 transition-colors duration-150"
                                 :class="tab === 'browse' ? 'text-emerald-400' : 'text-gray-600 group-hover:text-gray-400'"
                                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                            </svg>
                            Parcourir
                            <span class="ml-2 py-0.5 px-2.5 rounded-full text-xs font-medium transition-colors duration-150"
                                  :class="tab === 'browse' ? 'bg-emerald-500/10 text-emerald-400' : 'bg-gray-800 text-gray-500'">
                                {{ number_format($total) }}
                            </span>
                        </button>
                    </nav>
                </div>
            </div>

            {{-- ======== ONGLET STRUCTURE ======== --}}
            <div x-show="tab === 'structure'" x-cloak
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100">

                <div class="bg-gray-900 border border-t-0 border-gray-800 overflow-hidden rounded-b-xl">
                    <div class="p-6">
                        <h3 class="text-sm font-medium text-gray-400 uppercase tracking-wider mb-5 flex items-center">
                            <svg class="w-4 h-4 mr-2 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            Colonnes
                        </h3>

                        <div class="overflow-x-auto rounded-lg border border-gray-800">
                            <table class="min-w-full divide-y divide-gray-800">
                                <thead>
                                    <tr class="bg-gray-800/50">
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">#</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nom</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Null</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cle</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Defaut</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Extra</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Commentaire</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-800">
                                    @foreach($columns as $i => $col)
                                        @php $col = (array) $col; @endphp
                                        <tr class="hover:bg-gray-800/50 transition-colors duration-100">
                                            <td class="px-4 py-3 text-gray-600 text-xs tabular-nums">{{ $i + 1 }}</td>
                                            <td class="px-4 py-3">
                                                <div class="flex items-center">
                                                    @if($col['Key'] === 'PRI')
                                                        <span class="flex-shrink-0 w-5 h-5 rounded bg-amber-500/10 text-amber-400 flex items-center justify-center mr-2" title="Cle primaire">
                                                            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 8a6 6 0 01-7.743 5.743L10 14l-1 1-1 1H6v2H2v-4l4.257-4.257A6 6 0 1118 8zm-6-4a1 1 0 100 2 2 2 0 012 2 1 1 0 102 0 4 4 0 00-4-4z" clip-rule="evenodd"/></svg>
                                                        </span>
                                                    @elseif($col['Key'] === 'MUL')
                                                        <span class="flex-shrink-0 w-5 h-5 rounded bg-blue-500/10 text-blue-400 flex items-center justify-center mr-2" title="Index">
                                                            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M12.586 4.586a2 2 0 112.828 2.828l-3 3a2 2 0 01-2.828 0 1 1 0 00-1.414 1.414 4 4 0 005.656 0l3-3a4 4 0 00-5.656-5.656l-1.5 1.5a1 1 0 101.414 1.414l1.5-1.5zm-5 5a2 2 0 012.828 0 1 1 0 101.414-1.414 4 4 0 00-5.656 0l-3 3a4 4 0 105.656 5.656l1.5-1.5a1 1 0 10-1.414-1.414l-1.5 1.5a2 2 0 11-2.828-2.828l3-3z" clip-rule="evenodd"/></svg>
                                                        </span>
                                                    @elseif($col['Key'] === 'UNI')
                                                        <span class="flex-shrink-0 w-5 h-5 rounded bg-purple-500/10 text-purple-400 flex items-center justify-center mr-2" title="Unique">
                                                            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5 2a1 1 0 011 1v1h1a1 1 0 010 2H6v1a1 1 0 01-2 0V6H3a1 1 0 010-2h1V3a1 1 0 011-1zm0 10a1 1 0 011 1v1h1a1 1 0 110 2H6v1a1 1 0 11-2 0v-1H3a1 1 0 110-2h1v-1a1 1 0 011-1z" clip-rule="evenodd"/></svg>
                                                        </span>
                                                    @else
                                                        <span class="w-5 mr-2"></span>
                                                    @endif
                                                    <span class="font-mono font-medium text-sm text-gray-200">{{ $col['Field'] }}</span>
                                                </div>
                                            </td>
                                            <td class="px-4 py-3">
                                                <code class="text-xs bg-blue-500/10 text-blue-400 px-1.5 py-0.5 rounded">{{ $col['Type'] }}</code>
                                            </td>
                                            <td class="px-4 py-3">
                                                @if($col['Null'] === 'YES')
                                                    <span class="text-gray-500 text-xs italic">nullable</span>
                                                @else
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-500/10 text-red-400">NOT NULL</span>
                                                @endif
                                            </td>
                                            <td class="px-4 py-3">
                                                @if($col['Key'] === 'PRI')
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-amber-500/10 text-amber-400 ring-1 ring-inset ring-amber-500/20">PRIMARY</span>
                                                @elseif($col['Key'] === 'MUL')
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-blue-500/10 text-blue-400 ring-1 ring-inset ring-blue-500/20">INDEX</span>
                                                @elseif($col['Key'] === 'UNI')
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-purple-500/10 text-purple-400 ring-1 ring-inset ring-purple-500/20">UNIQUE</span>
                                                @else
                                                    <span class="text-gray-700">&mdash;</span>
                                                @endif
                                            </td>
                                            <td class="px-4 py-3 font-mono text-xs text-gray-500">
                                                @if(is_null($col['Default']))
                                                    <span class="italic text-gray-600">NULL</span>
                                                @else
                                                    {{ $col['Default'] }}
                                                @endif
                                            </td>
                                            <td class="px-4 py-3">
                                                @if($col['Extra'])
                                                    <code class="text-xs bg-gray-800 text-gray-400 px-1.5 py-0.5 rounded">{{ $col['Extra'] }}</code>
                                                @else
                                                    <span class="text-gray-700">&mdash;</span>
                                                @endif
                                            </td>
                                            <td class="px-4 py-3 text-xs text-gray-500 max-w-xs truncate">{{ $col['Comment'] ?: '' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                @if(count($indexes) > 0)
                    <div class="bg-gray-900 border border-gray-800 overflow-hidden rounded-xl mt-6">
                        <div class="p-6">
                            <h3 class="text-sm font-medium text-gray-400 uppercase tracking-wider mb-5 flex items-center">
                                <svg class="w-4 h-4 mr-2 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                                Index
                            </h3>
                            <div class="overflow-x-auto rounded-lg border border-gray-800">
                                <table class="min-w-full divide-y divide-gray-800">
                                    <thead>
                                        <tr class="bg-gray-800/50">
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nom</th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Colonne</th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Unique</th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cardinalite</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-800">
                                        @foreach($indexes as $idx)
                                            @php $idx = (array) $idx; @endphp
                                            <tr class="hover:bg-gray-800/50 transition-colors duration-100">
                                                <td class="px-4 py-3 font-mono text-sm font-medium text-gray-200">{{ $idx['Key_name'] }}</td>
                                                <td class="px-4 py-3 font-mono text-sm text-gray-400">{{ $idx['Column_name'] }}</td>
                                                <td class="px-4 py-3">
                                                    <code class="text-xs bg-gray-800 text-gray-400 px-1.5 py-0.5 rounded">{{ $idx['Index_type'] }}</code>
                                                </td>
                                                <td class="px-4 py-3">
                                                    @if($idx['Non_unique'] == 0)
                                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-emerald-500/10 text-emerald-400 ring-1 ring-inset ring-emerald-500/20">Oui</span>
                                                    @else
                                                        <span class="text-gray-500 text-xs">Non</span>
                                                    @endif
                                                </td>
                                                <td class="px-4 py-3 text-sm text-gray-400 tabular-nums">{{ number_format($idx['Cardinality'] ?? 0) }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            {{-- ======== ONGLET PARCOURIR ======== --}}
            <div x-show="tab === 'browse'" x-cloak
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100">

                <div class="bg-gray-900 border border-t-0 border-gray-800 overflow-hidden rounded-b-xl">
                    <div class="p-6">

                        {{-- Toolbar --}}
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-5">
                            <div>
                                <h3 class="text-sm font-medium text-gray-400 uppercase tracking-wider flex items-center">
                                    <svg class="w-4 h-4 mr-2 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                                    Donnees
                                </h3>
                                @if($total > 0)
                                    <p class="text-sm text-gray-500 mt-0.5">
                                        Lignes {{ number_format(($page - 1) * $perPage + 1) }}&ndash;{{ number_format(min($page * $perPage, $total)) }}
                                        sur {{ number_format($total) }}
                                    </p>
                                @endif
                            </div>
                            @if($hasPk)
                                <button @click="openAdd()"
                                        class="inline-flex items-center px-4 py-2 bg-emerald-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 focus:ring-offset-gray-900 transition">
                                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                                    Ajouter une ligne
                                </button>
                            @endif
                        </div>

                        @if($rows->count() > 0)
                            <div class="overflow-x-auto rounded-lg border border-gray-800">
                                <table class="min-w-full divide-y divide-gray-800">
                                    <thead>
                                        <tr class="bg-gray-800/50">
                                            @if($hasPk)
                                                <th class="px-3 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider w-20">Actions</th>
                                            @endif
                                            @foreach(array_keys((array) $rows->first()) as $colName)
                                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider font-mono whitespace-nowrap">
                                                    @if(in_array($colName, $primaryKeys))
                                                        <span class="inline-flex items-center">
                                                            <svg class="w-3 h-3 text-amber-500 mr-1" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 8a6 6 0 01-7.743 5.743L10 14l-1 1-1 1H6v2H2v-4l4.257-4.257A6 6 0 1118 8zm-6-4a1 1 0 100 2 2 2 0 012 2 1 1 0 102 0 4 4 0 00-4-4z" clip-rule="evenodd"/></svg>
                                                            {{ $colName }}
                                                        </span>
                                                    @else
                                                        {{ $colName }}
                                                    @endif
                                                </th>
                                            @endforeach
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-800">
                                        @foreach($rows as $i => $row)
                                            <tr class="hover:bg-gray-800/50 transition-colors duration-100 group">
                                                @if($hasPk)
                                                    <td class="px-3 py-2 whitespace-nowrap text-center">
                                                        <div class="inline-flex items-center rounded-lg opacity-30 group-hover:opacity-100 transition-opacity duration-150">
                                                            <button @click="openEdit(@js((array) $row))"
                                                                    class="inline-flex items-center px-2.5 py-1.5 rounded-l-lg border border-gray-700 bg-gray-800 text-gray-500 hover:text-indigo-400 hover:bg-indigo-500/10 hover:border-indigo-500/30 transition-colors duration-100 text-xs"
                                                                    title="Modifier">
                                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                                            </button>
                                                            <button @click="openDelete(@js((array) $row))"
                                                                    class="inline-flex items-center px-2.5 py-1.5 -ml-px rounded-r-lg border border-gray-700 bg-gray-800 text-gray-500 hover:text-red-400 hover:bg-red-500/10 hover:border-red-500/30 transition-colors duration-100 text-xs"
                                                                    title="Supprimer">
                                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                            </button>
                                                        </div>
                                                    </td>
                                                @endif
                                                @foreach((array) $row as $value)
                                                    <td class="px-4 py-2.5 font-mono text-xs whitespace-nowrap max-w-xs truncate {{ is_null($value) ? 'text-gray-600' : 'text-gray-300' }}"
                                                        title="{{ is_null($value) ? 'NULL' : e($value) }}">
                                                        @if(is_null($value))
                                                            <span class="italic text-gray-600">NULL</span>
                                                        @elseif(strlen((string) $value) > 80)
                                                            {{ \Illuminate\Support\Str::limit((string) $value, 80) }}
                                                        @else
                                                            {{ $value }}
                                                        @endif
                                                    </td>
                                                @endforeach
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            {{-- Pagination --}}
                            @if($lastPage > 1)
                                <div class="mt-6 flex flex-col sm:flex-row items-center justify-between gap-3 pt-4 border-t border-gray-800">
                                    <p class="text-sm text-gray-500">
                                        Page <span class="font-semibold text-gray-300">{{ $page }}</span>
                                        sur <span class="font-semibold text-gray-300">{{ number_format($lastPage) }}</span>
                                    </p>
                                    <nav class="flex items-center space-x-1">
                                        @if($page > 1)
                                            <a href="{{ request()->fullUrlWithQuery(['page' => 1, 'tab' => 'browse']) }}"
                                               class="inline-flex items-center px-2.5 py-1.5 text-xs font-medium rounded-lg border border-gray-700 bg-gray-800 text-gray-400 hover:bg-gray-700 hover:text-gray-200 transition" title="Premiere">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7"/></svg>
                                            </a>
                                            <a href="{{ request()->fullUrlWithQuery(['page' => $page - 1, 'tab' => 'browse']) }}"
                                               class="inline-flex items-center px-3 py-1.5 text-xs font-medium rounded-lg border border-gray-700 bg-gray-800 text-gray-400 hover:bg-gray-700 hover:text-gray-200 transition">
                                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                                                Prec.
                                            </a>
                                        @endif

                                        @for($p = max(1, $page - 2); $p <= min($lastPage, $page + 2); $p++)
                                            @if($p === $page)
                                                <span class="inline-flex items-center px-3.5 py-1.5 text-xs font-bold rounded-lg bg-emerald-600 text-white shadow-sm">{{ $p }}</span>
                                            @else
                                                <a href="{{ request()->fullUrlWithQuery(['page' => $p, 'tab' => 'browse']) }}"
                                                   class="inline-flex items-center px-3.5 py-1.5 text-xs font-medium rounded-lg border border-gray-700 bg-gray-800 text-gray-400 hover:bg-gray-700 hover:text-gray-200 transition">{{ $p }}</a>
                                            @endif
                                        @endfor

                                        @if($page < $lastPage)
                                            <a href="{{ request()->fullUrlWithQuery(['page' => $page + 1, 'tab' => 'browse']) }}"
                                               class="inline-flex items-center px-3 py-1.5 text-xs font-medium rounded-lg border border-gray-700 bg-gray-800 text-gray-400 hover:bg-gray-700 hover:text-gray-200 transition">
                                                Suiv.
                                                <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                            </a>
                                            <a href="{{ request()->fullUrlWithQuery(['page' => $lastPage, 'tab' => 'browse']) }}"
                                               class="inline-flex items-center px-2.5 py-1.5 text-xs font-medium rounded-lg border border-gray-700 bg-gray-800 text-gray-400 hover:bg-gray-700 hover:text-gray-200 transition" title="Derniere">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 5l7 7-7 7M5 5l7 7-7 7"/></svg>
                                            </a>
                                        @endif
                                    </nav>
                                </div>
                            @endif

                        @else
                            <div class="text-center py-16">
                                <svg class="mx-auto h-12 w-12 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/></svg>
                                <h3 class="mt-3 text-sm font-semibold text-gray-300">Aucune donnee</h3>
                                <p class="mt-1 text-sm text-gray-500">Cette table est vide.</p>
                                @if($hasPk)
                                    <div class="mt-6">
                                        <button @click="openAdd()"
                                                class="inline-flex items-center px-4 py-2 bg-emerald-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-emerald-500 transition">
                                            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                                            Ajouter une ligne
                                        </button>
                                    </div>
                                @endif
                            </div>
                        @endif

                    </div>
                </div>
            </div>

        </div>

        {{-- ==================== MODAL EDIT ==================== --}}
        <x-modal name="edit-row" maxWidth="2xl" focusable>
            <div class="px-6 py-4 border-b border-gray-800 bg-gradient-to-r from-indigo-500/10 to-transparent">
                <h3 class="text-lg font-semibold text-gray-100 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    Modifier la ligne
                </h3>
                <p class="text-sm text-gray-500 mt-1">Les cles primaires ne sont pas modifiables.</p>
            </div>
            <form method="POST" action="{{ route('databases.explorer.update', [$database, $databaseUser, $table]) }}">
                @csrf
                @method('PUT')
                <input type="hidden" name="page" value="{{ $page }}">

                <div class="px-6 py-5 max-h-[60vh] overflow-y-auto space-y-4">
                    <template x-for="pk in primaryKeys" :key="'edit-pk-' + pk">
                        <input type="hidden" :name="'pk[' + pk + ']'" :value="editPk[pk]">
                    </template>

                    <template x-for="col in columns" :key="'edit-' + col.Field">
                        <div class="grid grid-cols-4 gap-4 items-start">
                            <label class="col-span-1 text-sm font-mono font-medium text-gray-400 pt-2.5 text-right truncate"
                                   :title="col.Field" x-text="col.Field"></label>
                            <div class="col-span-3">
                                <template x-if="primaryKeys.includes(col.Field)">
                                    <div class="flex items-center">
                                        <div class="flex-1 px-3 py-2 bg-gray-800 border border-gray-700 rounded-lg text-sm font-mono text-gray-500"
                                             x-text="editRow[col.Field] === null ? 'NULL' : editRow[col.Field]"></div>
                                        <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-amber-500/10 text-amber-400 ring-1 ring-inset ring-amber-500/20">PK</span>
                                    </div>
                                </template>
                                <template x-if="!primaryKeys.includes(col.Field)">
                                    <div>
                                        <input type="text"
                                               :name="'data[' + col.Field + ']'"
                                               :value="editRow[col.Field]"
                                               @input="editRow[col.Field] = $event.target.value"
                                               class="block w-full rounded-lg bg-gray-800 border-gray-700 text-gray-100 text-sm font-mono focus:border-indigo-500 focus:ring-indigo-500"
                                               :placeholder="col.Type">
                                        <p class="mt-1 text-xs text-gray-600" x-text="col.Type"></p>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </template>
                </div>

                <div class="px-6 py-4 border-t border-gray-800 bg-gray-800/50 flex justify-end gap-3 rounded-b-xl">
                    <x-secondary-button x-on:click="$dispatch('close')">Annuler</x-secondary-button>
                    <button type="submit"
                            class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-500 transition">
                        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        Enregistrer
                    </button>
                </div>
            </form>
        </x-modal>

        {{-- ==================== MODAL ADD ==================== --}}
        <x-modal name="add-row" maxWidth="2xl" focusable>
            <div class="px-6 py-4 border-b border-gray-800 bg-gradient-to-r from-emerald-500/10 to-transparent">
                <h3 class="text-lg font-semibold text-gray-100 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                    Ajouter une ligne
                </h3>
                <p class="text-sm text-gray-500 mt-1">Inserer une nouvelle ligne dans <code class="text-xs bg-gray-800 px-1.5 py-0.5 rounded font-mono text-gray-300">{{ $table }}</code></p>
            </div>
            <form method="POST" action="{{ route('databases.explorer.store', [$database, $databaseUser, $table]) }}"
                  @submit="document.querySelectorAll('[data-add-skip]').forEach(el => { if (el.dataset.addSkip === 'true') el.remove(); });">
                @csrf
                <input type="hidden" name="page" value="{{ $page }}">

                <div class="px-6 py-5 max-h-[60vh] overflow-y-auto space-y-4">
                    <template x-for="(field, index) in addFields" :key="'add-' + field.column">
                        <div class="grid grid-cols-4 gap-4 items-start" :data-add-skip="field.skip">
                            <label class="col-span-1 text-sm font-mono font-medium text-gray-400 pt-2.5 text-right truncate" :title="field.column">
                                <span x-text="field.column"></span>
                                <template x-if="field.autoIncrement">
                                    <span class="block text-xs text-gray-600 font-normal font-sans">auto_increment</span>
                                </template>
                            </label>
                            <div class="col-span-3">
                                <div class="flex items-center gap-3">
                                    <div class="flex-1">
                                        <template x-if="field.skip">
                                            <div class="px-3 py-2 bg-gray-800 border border-gray-700 rounded-lg text-sm font-mono text-gray-600 italic">Auto-genere</div>
                                        </template>
                                        <template x-if="!field.skip && field.useNull">
                                            <div>
                                                <input type="hidden" :name="'fields[' + index + '][column]'" :value="field.column">
                                                <input type="hidden" :name="'fields[' + index + '][value]'" value="">
                                                <div class="px-3 py-2 bg-gray-800 border border-gray-700 rounded-lg text-sm font-mono text-gray-600 italic">NULL</div>
                                            </div>
                                        </template>
                                        <template x-if="!field.skip && !field.useNull">
                                            <div>
                                                <input type="hidden" :name="'fields[' + index + '][column]'" :value="field.column">
                                                <input type="text"
                                                       :name="'fields[' + index + '][value]'"
                                                       x-model="field.value"
                                                       class="block w-full rounded-lg bg-gray-800 border-gray-700 text-gray-100 text-sm font-mono focus:border-emerald-500 focus:ring-emerald-500"
                                                       :placeholder="field.type">
                                            </div>
                                        </template>
                                    </div>
                                    <div class="flex items-center gap-2 flex-shrink-0">
                                        <template x-if="field.nullable || field.autoIncrement">
                                            <label class="flex items-center gap-1.5 text-xs text-gray-500 cursor-pointer select-none whitespace-nowrap">
                                                <input type="checkbox" x-model="field.skip"
                                                       class="rounded border-gray-600 text-gray-600 bg-gray-800 focus:ring-emerald-500"
                                                       @change="if(field.skip) field.useNull = false">
                                                <span>Ignorer</span>
                                            </label>
                                        </template>
                                        <template x-if="field.nullable && !field.skip">
                                            <label class="flex items-center gap-1.5 text-xs text-gray-500 cursor-pointer select-none whitespace-nowrap">
                                                <input type="checkbox" x-model="field.useNull"
                                                       class="rounded border-gray-600 text-gray-600 bg-gray-800 focus:ring-emerald-500">
                                                <span>NULL</span>
                                            </label>
                                        </template>
                                    </div>
                                </div>
                                <p class="mt-1 text-xs text-gray-600" x-text="field.type"></p>
                            </div>
                        </div>
                    </template>
                </div>

                <div class="px-6 py-4 border-t border-gray-800 bg-gray-800/50 flex justify-end gap-3 rounded-b-xl">
                    <x-secondary-button x-on:click="$dispatch('close')">Annuler</x-secondary-button>
                    <button type="submit"
                            class="inline-flex items-center px-4 py-2 bg-emerald-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-emerald-500 transition">
                        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                        Ajouter
                    </button>
                </div>
            </form>
        </x-modal>

        {{-- ==================== MODAL DELETE ==================== --}}
        <x-modal name="delete-row" maxWidth="md">
            <div class="px-6 py-4 border-b border-gray-800 bg-gradient-to-r from-red-500/10 to-transparent">
                <h3 class="text-lg font-semibold text-gray-100 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    Confirmer la suppression
                </h3>
            </div>
            <form method="POST" action="{{ route('databases.explorer.delete', [$database, $databaseUser, $table]) }}">
                @csrf
                @method('DELETE')
                <input type="hidden" name="page" value="{{ $page }}">

                <div class="px-6 py-5">
                    <p class="text-sm text-gray-300 mb-4">
                        Etes-vous sur de vouloir supprimer cette ligne ? Cette action est <strong class="text-red-400">irreversible</strong>.
                    </p>
                    <div class="bg-red-500/5 border border-red-500/20 rounded-lg p-4">
                        <pre class="text-xs font-mono text-red-300 whitespace-pre-wrap break-all" x-text="deleteRowPreview"></pre>
                    </div>

                    <template x-for="pk in primaryKeys" :key="'del-pk-' + pk">
                        <input type="hidden" :name="'pk[' + pk + ']'" :value="deleteRowPk[pk]">
                    </template>
                </div>

                <div class="px-6 py-4 border-t border-gray-800 bg-gray-800/50 flex justify-end gap-3 rounded-b-xl">
                    <x-secondary-button x-on:click="$dispatch('close')">Annuler</x-secondary-button>
                    <x-danger-button type="submit">
                        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        Supprimer
                    </x-danger-button>
                </div>
            </form>
        </x-modal>

    </div>
</x-app-layout>
