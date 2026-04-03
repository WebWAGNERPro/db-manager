@php $currentTable = $table; @endphp
@extends('layouts.explorer')

@section('content')
@php
    $primaryKeys = collect($columns)->filter(fn($col) => ((array) $col)['Key'] === 'PRI')->map(fn($col) => ((array) $col)['Field'])->values()->toArray();
    $columnList = collect($columns)->map(fn($col) => (array) $col)->values()->toArray();
    $hasPk = count($primaryKeys) > 0;
@endphp

<div x-data="{
    tab: '{{ request('tab', 'browse') }}',
    editRow: {},
    editPk: {},
    deleteRowPk: {},
    deleteRowPreview: '',
    addFields: [],
    columns: @js($columnList),
    primaryKeys: @js($primaryKeys),
    selectedRows: [],
    allRows: @js($rows->map(fn($r) => (array) $r)->values()->toArray()),
    get allSelected() {
        return this.allRows.length > 0 && this.selectedRows.length === this.allRows.length;
    },
    toggleAll() {
        if (this.allSelected) {
            this.selectedRows = [];
        } else {
            this.selectedRows = this.allRows.map(row => {
                let pk = {};
                this.primaryKeys.forEach(k => pk[k] = row[k]);
                return pk;
            });
        }
    },
    toggleRow(row) {
        let pk = {};
        this.primaryKeys.forEach(k => pk[k] = row[k]);
        let idx = this.selectedRows.findIndex(s => this.primaryKeys.every(k => s[k] === pk[k]));
        if (idx > -1) {
            this.selectedRows.splice(idx, 1);
        } else {
            this.selectedRows.push(pk);
        }
    },
    isSelected(row) {
        return this.selectedRows.some(s => this.primaryKeys.every(k => s[k] == row[k]));
    },
    openEdit(row) {
        this.editRow = { ...row };
        this.editPk = {};
        this.primaryKeys.forEach(pk => { this.editPk[pk] = row[pk]; });
        $dispatch('open-modal', 'edit-row');
    },
    openDelete(row) {
        this.deleteRowPk = {};
        this.primaryKeys.forEach(pk => { this.deleteRowPk[pk] = row[pk]; });
        let e = Object.entries(row).slice(0, 4);
        this.deleteRowPreview = e.map(([k,v]) => k + ' = ' + (v === null ? 'NULL' : v)).join('\n');
        if (Object.keys(row).length > 4) this.deleteRowPreview += '\n...';
        $dispatch('open-modal', 'delete-row');
    },
    openAdd() {
        this.addFields = this.columns.map(col => ({
            column: col.Field, type: col.Type, nullable: col.Null === 'YES',
            autoIncrement: col.Extra.includes('auto_increment'),
            value: '', useNull: false, skip: col.Extra.includes('auto_increment')
        }));
        $dispatch('open-modal', 'add-row');
    }
}">

    {{-- Table header bar --}}
    <div class="sticky top-0 z-10 bg-gray-900/95 backdrop-blur border-b border-gray-800 px-5 py-2.5 flex items-center justify-between">
        <div class="flex items-center gap-3 min-w-0">
            <span class="font-mono text-xs text-orange-400">{{ $database->database_name }}</span>
            <svg class="w-3 h-3 text-gray-700 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            <span class="font-mono text-sm font-semibold text-gray-100 truncate">{{ $table }}</span>
            <span class="text-xs text-gray-500 tabular-nums">({{ number_format($total) }} ligne{{ $total > 1 ? 's' : '' }})</span>
        </div>

        {{-- Tabs --}}
        <div class="flex items-center gap-1 bg-gray-800 rounded-lg p-0.5">
            <button @click="tab = 'browse'"
                    :class="tab === 'browse' ? 'bg-gray-700 text-emerald-400 shadow-sm' : 'text-gray-400 hover:text-gray-200'"
                    class="px-3 py-1 rounded-md text-xs font-medium transition-all">
                Donnees
            </button>
            <button @click="tab = 'structure'"
                    :class="tab === 'structure' ? 'bg-gray-700 text-indigo-400 shadow-sm' : 'text-gray-400 hover:text-gray-200'"
                    class="px-3 py-1 rounded-md text-xs font-medium transition-all">
                Structure
            </button>
        </div>
    </div>

    {{-- Flash --}}
    @if($errors->any())
        <div class="mx-5 mt-3 flex items-center bg-red-500/10 border border-red-500/20 text-red-400 px-4 py-2.5 rounded-lg text-xs">
            <svg class="w-4 h-4 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>
            {{ $errors->first() }}
        </div>
    @endif
    @if(session('success'))
        <div class="mx-5 mt-3 flex items-center bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 px-4 py-2.5 rounded-lg text-xs">
            <svg class="w-4 h-4 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
            {{ session('success') }}
        </div>
    @endif

    {{-- ===== BROWSE TAB ===== --}}
    <div x-show="tab === 'browse'" x-cloak class="p-5">

        @if($hasPk)
            <div class="mb-3 flex items-center justify-between">
                <div x-show="selectedRows.length > 0" x-cloak x-transition
                     class="flex items-center gap-3 bg-red-500/10 border border-red-500/20 rounded-lg px-4 py-2">
                    <span class="text-xs text-red-300">
                        <span class="font-semibold" x-text="selectedRows.length"></span> ligne(s) selectionnee(s)
                    </span>
                    <button @click="$dispatch('open-modal', 'bulk-delete')"
                            class="inline-flex items-center px-3 py-1 bg-red-600 rounded-lg font-semibold text-xs text-white hover:bg-red-500 transition">
                        <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        Supprimer
                    </button>
                    <button @click="selectedRows = []" class="text-xs text-gray-400 hover:text-gray-200 transition">
                        Deselectionner
                    </button>
                </div>
                <div x-show="selectedRows.length === 0"></div>
                <button @click="openAdd()" class="inline-flex items-center px-3 py-1.5 bg-emerald-600 rounded-lg font-semibold text-xs text-white hover:bg-emerald-500 transition">
                    <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                    Ajouter
                </button>
            </div>
        @endif

        @if($rows->count() > 0)
            <div class="overflow-x-auto rounded-lg border border-gray-800">
                <table class="min-w-full divide-y divide-gray-800 text-xs">
                    <thead>
                        <tr class="bg-gray-800/50">
                            @if($hasPk)
                                <th class="px-2 py-2 text-center w-8">
                                    <input type="checkbox" @click="toggleAll()" :checked="allSelected"
                                           class="rounded border-gray-600 text-emerald-600 bg-gray-800 w-3.5 h-3.5 cursor-pointer focus:ring-emerald-500">
                                </th>
                                <th class="px-2 py-2 text-center text-[10px] font-medium text-gray-500 uppercase w-16"></th>
                            @endif
                            @foreach(array_keys((array) $rows->first()) as $colName)
                                <th class="px-3 py-2 text-left text-[10px] font-medium text-gray-500 uppercase font-mono whitespace-nowrap">
                                    @if(in_array($colName, $primaryKeys))
                                        <span class="inline-flex items-center gap-0.5">
                                            <svg class="w-2.5 h-2.5 text-amber-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 8a6 6 0 01-7.743 5.743L10 14l-1 1-1 1H6v2H2v-4l4.257-4.257A6 6 0 1118 8zm-6-4a1 1 0 100 2 2 2 0 012 2 1 1 0 102 0 4 4 0 00-4-4z" clip-rule="evenodd"/></svg>
                                            {{ $colName }}
                                        </span>
                                    @else
                                        {{ $colName }}
                                    @endif
                                </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-800/50">
                        @foreach($rows as $row)
                            <tr class="hover:bg-gray-800/30 transition-colors group" :class="isSelected(@js((array) $row)) && 'bg-emerald-500/5'">
                                @if($hasPk)
                                    <td class="px-2 py-1.5 text-center">
                                        <input type="checkbox" @click="toggleRow(@js((array) $row))" :checked="isSelected(@js((array) $row))"
                                               class="rounded border-gray-600 text-emerald-600 bg-gray-800 w-3.5 h-3.5 cursor-pointer focus:ring-emerald-500">
                                    </td>
                                    <td class="px-2 py-1.5 text-center">
                                        <div class="inline-flex opacity-0 group-hover:opacity-100 transition-opacity">
                                            <button @click="openEdit(@js((array) $row))" class="p-1 text-gray-600 hover:text-indigo-400 transition" title="Modifier">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                            </button>
                                            <button @click="openDelete(@js((array) $row))" class="p-1 text-gray-600 hover:text-red-400 transition" title="Supprimer">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            </button>
                                        </div>
                                    </td>
                                @endif
                                @foreach((array) $row as $value)
                                    <td class="px-3 py-1.5 font-mono whitespace-nowrap max-w-xs truncate {{ is_null($value) ? 'text-gray-600 italic' : 'text-gray-300' }}"
                                        title="{{ is_null($value) ? 'NULL' : e($value) }}">
                                        @if(is_null($value))
                                            NULL
                                        @elseif(strlen((string) $value) > 80)
                                            {{ Str::limit((string) $value, 80) }}
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
                <div class="mt-4 flex items-center justify-between text-xs">
                    <span class="text-gray-500">Page <span class="text-gray-300 font-medium">{{ $page }}</span> / {{ number_format($lastPage) }}</span>
                    <nav class="flex items-center gap-1">
                        @if($page > 1)
                            <a href="{{ request()->fullUrlWithQuery(['page' => $page - 1, 'tab' => 'browse']) }}" class="px-2 py-1 rounded bg-gray-800 text-gray-400 hover:bg-gray-700 hover:text-gray-200 transition">&lsaquo; Prec</a>
                        @endif
                        @for($p = max(1, $page - 2); $p <= min($lastPage, $page + 2); $p++)
                            <a href="{{ request()->fullUrlWithQuery(['page' => $p, 'tab' => 'browse']) }}"
                               class="px-2.5 py-1 rounded font-medium transition {{ $p === $page ? 'bg-emerald-600 text-white' : 'bg-gray-800 text-gray-400 hover:bg-gray-700' }}">{{ $p }}</a>
                        @endfor
                        @if($page < $lastPage)
                            <a href="{{ request()->fullUrlWithQuery(['page' => $page + 1, 'tab' => 'browse']) }}" class="px-2 py-1 rounded bg-gray-800 text-gray-400 hover:bg-gray-700 hover:text-gray-200 transition">Suiv &rsaquo;</a>
                        @endif
                    </nav>
                </div>
            @endif
        @else
            <div class="text-center py-16">
                <p class="text-gray-500 text-sm">Table vide.</p>
                @if($hasPk)
                    <button @click="openAdd()" class="mt-4 inline-flex items-center px-3 py-1.5 bg-emerald-600 rounded-lg text-xs text-white font-semibold hover:bg-emerald-500 transition">+ Ajouter une ligne</button>
                @endif
            </div>
        @endif
    </div>

    {{-- ===== STRUCTURE TAB ===== --}}
    <div x-show="tab === 'structure'" x-cloak class="p-5">
        <div class="overflow-x-auto rounded-lg border border-gray-800 mb-6">
            <table class="min-w-full divide-y divide-gray-800 text-xs">
                <thead>
                    <tr class="bg-gray-800/50">
                        <th class="px-3 py-2 text-left text-[10px] font-medium text-gray-500 uppercase">#</th>
                        <th class="px-3 py-2 text-left text-[10px] font-medium text-gray-500 uppercase">Nom</th>
                        <th class="px-3 py-2 text-left text-[10px] font-medium text-gray-500 uppercase">Type</th>
                        <th class="px-3 py-2 text-left text-[10px] font-medium text-gray-500 uppercase">Null</th>
                        <th class="px-3 py-2 text-left text-[10px] font-medium text-gray-500 uppercase">Cle</th>
                        <th class="px-3 py-2 text-left text-[10px] font-medium text-gray-500 uppercase">Defaut</th>
                        <th class="px-3 py-2 text-left text-[10px] font-medium text-gray-500 uppercase">Extra</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-800/50">
                    @foreach($columns as $i => $col)
                        @php $col = (array) $col; @endphp
                        <tr class="hover:bg-gray-800/30 transition-colors">
                            <td class="px-3 py-2 text-gray-600 tabular-nums">{{ $i + 1 }}</td>
                            <td class="px-3 py-2 font-mono font-medium text-gray-200">
                                @if($col['Key'] === 'PRI')<span class="text-amber-500 mr-1" title="PK">&#9670;</span>@endif
                                {{ $col['Field'] }}
                            </td>
                            <td class="px-3 py-2"><code class="text-blue-400 bg-blue-500/10 px-1 py-0.5 rounded text-[10px]">{{ $col['Type'] }}</code></td>
                            <td class="px-3 py-2 {{ $col['Null'] === 'YES' ? 'text-gray-600 italic' : 'text-red-400' }}">
                                {{ $col['Null'] === 'YES' ? 'nullable' : 'NOT NULL' }}
                            </td>
                            <td class="px-3 py-2">
                                @if($col['Key'] === 'PRI')<span class="text-amber-400 bg-amber-500/10 px-1.5 py-0.5 rounded text-[10px]">PRI</span>
                                @elseif($col['Key'] === 'MUL')<span class="text-blue-400 bg-blue-500/10 px-1.5 py-0.5 rounded text-[10px]">IDX</span>
                                @elseif($col['Key'] === 'UNI')<span class="text-purple-400 bg-purple-500/10 px-1.5 py-0.5 rounded text-[10px]">UNI</span>
                                @else<span class="text-gray-700">&mdash;</span>@endif
                            </td>
                            <td class="px-3 py-2 font-mono text-gray-500">{{ is_null($col['Default']) ? 'NULL' : $col['Default'] }}</td>
                            <td class="px-3 py-2">
                                @if($col['Extra'])<code class="text-gray-400 bg-gray-800 px-1 py-0.5 rounded text-[10px]">{{ $col['Extra'] }}</code>@else<span class="text-gray-700">&mdash;</span>@endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if(count($indexes) > 0)
            <h4 class="text-[10px] uppercase tracking-wider text-gray-500 font-medium mb-2">Index</h4>
            <div class="overflow-x-auto rounded-lg border border-gray-800">
                <table class="min-w-full divide-y divide-gray-800 text-xs">
                    <thead>
                        <tr class="bg-gray-800/50">
                            <th class="px-3 py-2 text-left text-[10px] font-medium text-gray-500 uppercase">Nom</th>
                            <th class="px-3 py-2 text-left text-[10px] font-medium text-gray-500 uppercase">Colonne</th>
                            <th class="px-3 py-2 text-left text-[10px] font-medium text-gray-500 uppercase">Type</th>
                            <th class="px-3 py-2 text-left text-[10px] font-medium text-gray-500 uppercase">Unique</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-800/50">
                        @foreach($indexes as $idx)
                            @php $idx = (array) $idx; @endphp
                            <tr class="hover:bg-gray-800/30 transition-colors">
                                <td class="px-3 py-2 font-mono font-medium text-gray-200">{{ $idx['Key_name'] }}</td>
                                <td class="px-3 py-2 font-mono text-gray-400">{{ $idx['Column_name'] }}</td>
                                <td class="px-3 py-2"><code class="text-gray-400 bg-gray-800 px-1 py-0.5 rounded text-[10px]">{{ $idx['Index_type'] }}</code></td>
                                <td class="px-3 py-2">
                                    @if($idx['Non_unique'] == 0)<span class="text-emerald-400 text-[10px]">Oui</span>@else<span class="text-gray-600">Non</span>@endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    {{-- ===== MODAL EDIT ===== --}}
    <x-modal name="edit-row" maxWidth="2xl" focusable>
        <div class="px-6 py-3 border-b border-gray-800 bg-indigo-500/5">
            <h3 class="text-sm font-semibold text-gray-100">Modifier la ligne</h3>
        </div>
        <form method="POST" action="{{ route('explorer.update', [$database, $databaseUser, $table]) }}">
            @csrf
            @method('PUT')
            <input type="hidden" name="page" value="{{ $page }}">
            <div class="px-6 py-4 max-h-[60vh] overflow-y-auto space-y-3">
                <template x-for="pk in primaryKeys" :key="'epk-' + pk">
                    <input type="hidden" :name="'pk[' + pk + ']'" :value="editPk[pk]">
                </template>
                <template x-for="col in columns" :key="'e-' + col.Field">
                    <div class="flex items-start gap-3">
                        <label class="w-32 flex-shrink-0 text-xs font-mono text-gray-400 pt-2 text-right truncate" :title="col.Field" x-text="col.Field"></label>
                        <div class="flex-1">
                            <template x-if="primaryKeys.includes(col.Field)">
                                <div class="px-3 py-1.5 bg-gray-800 border border-gray-700 rounded text-xs font-mono text-gray-500" x-text="editRow[col.Field] === null ? 'NULL' : editRow[col.Field]"></div>
                            </template>
                            <template x-if="!primaryKeys.includes(col.Field)">
                                <input type="text" :name="'data[' + col.Field + ']'" :value="editRow[col.Field]" @input="editRow[col.Field] = $event.target.value"
                                       class="w-full rounded bg-gray-800 border-gray-700 text-gray-100 text-xs font-mono focus:border-indigo-500 focus:ring-indigo-500 py-1.5" :placeholder="col.Type">
                            </template>
                        </div>
                    </div>
                </template>
            </div>
            <div class="px-6 py-3 border-t border-gray-800 bg-gray-800/30 flex justify-end gap-2">
                <x-secondary-button x-on:click="$dispatch('close')">Annuler</x-secondary-button>
                <button type="submit" class="inline-flex items-center px-3 py-1.5 bg-indigo-600 rounded-lg text-xs text-white font-semibold hover:bg-indigo-500 transition">Enregistrer</button>
            </div>
        </form>
    </x-modal>

    {{-- ===== MODAL ADD ===== --}}
    <x-modal name="add-row" maxWidth="2xl" focusable>
        <div class="px-6 py-3 border-b border-gray-800 bg-emerald-500/5">
            <h3 class="text-sm font-semibold text-gray-100">Ajouter une ligne</h3>
        </div>
        <form method="POST" action="{{ route('explorer.store', [$database, $databaseUser, $table]) }}"
              @submit="document.querySelectorAll('[data-add-skip]').forEach(el => { if (el.dataset.addSkip === 'true') el.remove(); });">
            @csrf
            <input type="hidden" name="page" value="{{ $page }}">
            <div class="px-6 py-4 max-h-[60vh] overflow-y-auto space-y-3">
                <template x-for="(field, index) in addFields" :key="'a-' + field.column">
                    <div class="flex items-start gap-3" :data-add-skip="field.skip">
                        <label class="w-32 flex-shrink-0 text-xs font-mono text-gray-400 pt-2 text-right truncate" :title="field.column">
                            <span x-text="field.column"></span>
                            <template x-if="field.autoIncrement"><span class="block text-[9px] text-gray-600 font-sans">auto</span></template>
                        </label>
                        <div class="flex-1 flex items-center gap-2">
                            <template x-if="field.skip">
                                <div class="flex-1 px-3 py-1.5 bg-gray-800 border border-gray-700 rounded text-xs font-mono text-gray-600 italic">Auto</div>
                            </template>
                            <template x-if="!field.skip && field.useNull">
                                <div class="flex-1">
                                    <input type="hidden" :name="'fields[' + index + '][column]'" :value="field.column">
                                    <input type="hidden" :name="'fields[' + index + '][value]'" value="">
                                    <div class="px-3 py-1.5 bg-gray-800 border border-gray-700 rounded text-xs font-mono text-gray-600 italic">NULL</div>
                                </div>
                            </template>
                            <template x-if="!field.skip && !field.useNull">
                                <div class="flex-1">
                                    <input type="hidden" :name="'fields[' + index + '][column]'" :value="field.column">
                                    <input type="text" :name="'fields[' + index + '][value]'" x-model="field.value"
                                           class="w-full rounded bg-gray-800 border-gray-700 text-gray-100 text-xs font-mono focus:border-emerald-500 focus:ring-emerald-500 py-1.5" :placeholder="field.type">
                                </div>
                            </template>
                            <template x-if="field.nullable || field.autoIncrement">
                                <label class="flex items-center gap-1 text-[10px] text-gray-500 cursor-pointer whitespace-nowrap">
                                    <input type="checkbox" x-model="field.skip" class="rounded border-gray-600 text-gray-600 bg-gray-800 w-3 h-3" @change="if(field.skip) field.useNull = false">
                                    Skip
                                </label>
                            </template>
                            <template x-if="field.nullable && !field.skip">
                                <label class="flex items-center gap-1 text-[10px] text-gray-500 cursor-pointer whitespace-nowrap">
                                    <input type="checkbox" x-model="field.useNull" class="rounded border-gray-600 text-gray-600 bg-gray-800 w-3 h-3">
                                    NULL
                                </label>
                            </template>
                        </div>
                    </div>
                </template>
            </div>
            <div class="px-6 py-3 border-t border-gray-800 bg-gray-800/30 flex justify-end gap-2">
                <x-secondary-button x-on:click="$dispatch('close')">Annuler</x-secondary-button>
                <button type="submit" class="inline-flex items-center px-3 py-1.5 bg-emerald-600 rounded-lg text-xs text-white font-semibold hover:bg-emerald-500 transition">Ajouter</button>
            </div>
        </form>
    </x-modal>

    {{-- ===== MODAL DELETE ===== --}}
    <x-modal name="delete-row" maxWidth="md">
        <div class="px-6 py-3 border-b border-gray-800 bg-red-500/5">
            <h3 class="text-sm font-semibold text-gray-100">Supprimer la ligne</h3>
        </div>
        <form method="POST" action="{{ route('explorer.delete', [$database, $databaseUser, $table]) }}">
            @csrf
            @method('DELETE')
            <input type="hidden" name="page" value="{{ $page }}">
            <div class="px-6 py-4">
                <p class="text-xs text-gray-400 mb-3">Cette action est <strong class="text-red-400">irreversible</strong>.</p>
                <div class="bg-red-500/5 border border-red-500/20 rounded-lg p-3">
                    <pre class="text-[11px] font-mono text-red-300 whitespace-pre-wrap break-all" x-text="deleteRowPreview"></pre>
                </div>
                <template x-for="pk in primaryKeys" :key="'dpk-' + pk">
                    <input type="hidden" :name="'pk[' + pk + ']'" :value="deleteRowPk[pk]">
                </template>
            </div>
            <div class="px-6 py-3 border-t border-gray-800 bg-gray-800/30 flex justify-end gap-2">
                <x-secondary-button x-on:click="$dispatch('close')">Annuler</x-secondary-button>
                <x-danger-button type="submit">Supprimer</x-danger-button>
            </div>
        </form>
    </x-modal>

    {{-- ===== MODAL BULK DELETE ===== --}}
    <x-modal name="bulk-delete" maxWidth="md">
        <div class="px-6 py-3 border-b border-gray-800 bg-red-500/5">
            <h3 class="text-sm font-semibold text-gray-100">Suppression en masse</h3>
        </div>
        <form method="POST" action="{{ route('explorer.bulk-delete', [$database, $databaseUser, $table]) }}">
            @csrf
            @method('DELETE')
            <input type="hidden" name="page" value="{{ $page }}">
            <div class="px-6 py-4">
                <p class="text-xs text-gray-400 mb-3">
                    Vous allez supprimer <strong class="text-red-400" x-text="selectedRows.length"></strong> ligne(s).
                    Cette action est <strong class="text-red-400">irreversible</strong>.
                </p>
                <template x-for="(row, rowIndex) in selectedRows" :key="'bulk-' + rowIndex">
                    <template x-for="pk in primaryKeys" :key="'bulk-' + rowIndex + '-' + pk">
                        <input type="hidden" :name="'rows[' + rowIndex + '][' + pk + ']'" :value="row[pk]">
                    </template>
                </template>
            </div>
            <div class="px-6 py-3 border-t border-gray-800 bg-gray-800/30 flex justify-end gap-2">
                <x-secondary-button x-on:click="$dispatch('close')">Annuler</x-secondary-button>
                <x-danger-button type="submit">Supprimer</x-danger-button>
            </div>
        </form>
    </x-modal>

</div>
@endsection
