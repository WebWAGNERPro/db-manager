<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    <a href="{{ route('databases.explorer', [$database, $databaseUser]) }}"
                       class="text-orange-600 hover:text-orange-700 font-mono">{{ $database->database_name }}</a>
                    <span class="text-gray-400 mx-1">></span>
                    <span class="font-mono">{{ $table }}</span>
                </h2>
                <p class="text-sm text-gray-500 mt-0.5">
                    Connecte en tant que
                    <span class="font-mono font-medium text-gray-700">{{ $databaseUser->username }}@{{ $databaseUser->host }}</span>
                    &mdash; {{ number_format($total) }} ligne{{ $total > 1 ? 's' : '' }}
                </p>
            </div>
            <a href="{{ route('databases.explorer', [$database, $databaseUser]) }}"
               class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                &larr; Tables
            </a>
        </div>
    </x-slot>

    @php
        $primaryKeys = collect($columns)->filter(fn($col) => ((array) $col)['Key'] === 'PRI')->map(fn($col) => ((array) $col)['Field'])->values()->toArray();
        $columnList = collect($columns)->map(fn($col) => (array) $col)->values()->toArray();
    @endphp

    <div class="py-8" x-data="{
        tab: '{{ request('tab', 'structure') }}',
        showEditModal: false,
        showAddModal: false,
        showDeleteModal: false,
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
            this.showEditModal = true;
        },
        openDelete(row) {
            this.deleteRowPk = {};
            this.primaryKeys.forEach(pk => { this.deleteRowPk[pk] = row[pk]; });
            let preview = Object.entries(row).slice(0, 3).map(([k,v]) => k + '=' + (v === null ? 'NULL' : v)).join(', ');
            if (Object.keys(row).length > 3) preview += ', ...';
            this.deleteRowPreview = preview;
            this.showDeleteModal = true;
        },
        openAdd() {
            this.addFields = this.columns.map(col => ({
                column: col.Field,
                type: col.Type,
                nullable: col.Null === 'YES',
                hasDefault: col.Default !== null || col.Extra.includes('auto_increment'),
                autoIncrement: col.Extra.includes('auto_increment'),
                value: '',
                useNull: false,
                skip: col.Extra.includes('auto_increment')
            }));
            this.showAddModal = true;
        }
    }">
        <div class="max-w-full mx-auto sm:px-6 lg:px-8">

            @if($errors->any())
                <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                    {{ $errors->first() }}
                </div>
            @endif

            @if(session('success'))
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                    {{ session('success') }}
                </div>
            @endif

            {{-- Onglets --}}
            <div class="mb-0 bg-white shadow-sm sm:rounded-t-lg border-b border-gray-200 px-6">
                <nav class="-mb-px flex space-x-8">
                    <button @click="tab = 'structure'"
                            :class="tab === 'structure'
                                ? 'border-blue-500 text-blue-600'
                                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                            class="py-4 px-1 border-b-2 font-medium text-sm transition-colors">
                        Structure
                        <span class="ml-1.5 bg-gray-100 text-gray-600 py-0.5 px-2 rounded-full text-xs">
                            {{ count($columns) }}
                        </span>
                    </button>
                    <button @click="tab = 'browse'"
                            :class="tab === 'browse'
                                ? 'border-green-500 text-green-600'
                                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                            class="py-4 px-1 border-b-2 font-medium text-sm transition-colors">
                        Parcourir
                        <span class="ml-1.5 bg-gray-100 text-gray-600 py-0.5 px-2 rounded-full text-xs">
                            {{ number_format($total) }}
                        </span>
                    </button>
                </nav>
            </div>

            {{-- ---- ONGLET STRUCTURE ---- --}}
            <div x-show="tab === 'structure'" x-cloak>

                {{-- Colonnes --}}
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-b-lg mb-6">
                    <div class="p-6">
                        <h3 class="text-base font-semibold text-gray-700 mb-4">Colonnes</h3>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 text-sm">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">#</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nom</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Null</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Cle</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Defaut</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Extra</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Commentaire</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($columns as $i => $col)
                                        @php $col = (array) $col; @endphp
                                        <tr class="{{ $i % 2 === 0 ? 'bg-white' : 'bg-gray-50' }} hover:bg-blue-50 transition-colors">
                                            <td class="px-4 py-3 text-gray-400 text-xs">{{ $i + 1 }}</td>
                                            <td class="px-4 py-3">
                                                <span class="font-mono font-medium {{ $col['Key'] === 'PRI' ? 'text-yellow-700' : 'text-gray-900' }}">
                                                    @if($col['Key'] === 'PRI')
                                                        <span class="mr-1" title="Cle primaire">&#128273;</span>
                                                    @elseif($col['Key'] === 'MUL')
                                                        <span class="mr-1" title="Index">&#128279;</span>
                                                    @elseif($col['Key'] === 'UNI')
                                                        <span class="mr-1" title="Unique">&#10022;</span>
                                                    @endif
                                                    {{ $col['Field'] }}
                                                </span>
                                            </td>
                                            <td class="px-4 py-3 font-mono text-xs text-blue-700">{{ $col['Type'] }}</td>
                                            <td class="px-4 py-3">
                                                @if($col['Null'] === 'YES')
                                                    <span class="text-gray-400 text-xs">NULL</span>
                                                @else
                                                    <span class="text-xs font-semibold text-red-600">NOT NULL</span>
                                                @endif
                                            </td>
                                            <td class="px-4 py-3">
                                                @if($col['Key'] === 'PRI')
                                                    <span class="px-2 py-0.5 text-xs bg-yellow-100 text-yellow-800 rounded-full">PRIMARY</span>
                                                @elseif($col['Key'] === 'MUL')
                                                    <span class="px-2 py-0.5 text-xs bg-blue-100 text-blue-800 rounded-full">INDEX</span>
                                                @elseif($col['Key'] === 'UNI')
                                                    <span class="px-2 py-0.5 text-xs bg-purple-100 text-purple-800 rounded-full">UNIQUE</span>
                                                @endif
                                            </td>
                                            <td class="px-4 py-3 font-mono text-xs text-gray-500">
                                                {{ is_null($col['Default']) ? 'NULL' : $col['Default'] }}
                                            </td>
                                            <td class="px-4 py-3 font-mono text-xs text-gray-500">{{ $col['Extra'] }}</td>
                                            <td class="px-4 py-3 text-xs text-gray-400">{{ $col['Comment'] }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                {{-- Index --}}
                @if(count($indexes) > 0)
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-base font-semibold text-gray-700 mb-4">Index</h3>
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200 text-sm">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nom</th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Colonne</th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Unique</th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Cardinalite</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @foreach($indexes as $idx)
                                            @php $idx = (array) $idx; @endphp
                                            <tr>
                                                <td class="px-4 py-3 font-mono text-sm font-medium text-gray-900">
                                                    {{ $idx['Key_name'] }}
                                                </td>
                                                <td class="px-4 py-3 font-mono text-sm text-gray-700">{{ $idx['Column_name'] }}</td>
                                                <td class="px-4 py-3 text-sm text-gray-600">{{ $idx['Index_type'] }}</td>
                                                <td class="px-4 py-3">
                                                    @if($idx['Non_unique'] == 0)
                                                        <span class="px-2 py-0.5 text-xs bg-green-100 text-green-800 rounded-full">Oui</span>
                                                    @else
                                                        <span class="text-gray-400 text-xs">Non</span>
                                                    @endif
                                                </td>
                                                <td class="px-4 py-3 text-sm text-gray-600">{{ $idx['Cardinality'] ?? '-' }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                @endif

            </div>

            {{-- ---- ONGLET PARCOURIR ---- --}}
            <div x-show="tab === 'browse'" x-cloak>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-b-lg">
                    <div class="p-6">

                        {{-- En-tete de l'onglet --}}
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-base font-semibold text-gray-700">
                                Donnees
                                @if($total > 0)
                                    <span class="text-sm font-normal text-gray-400">
                                        &mdash; lignes {{ ($page - 1) * $perPage + 1 }}&ndash;{{ min($page * $perPage, $total) }}
                                        sur {{ number_format($total) }}
                                    </span>
                                @endif
                            </h3>
                            @if(count($primaryKeys) > 0)
                                <button @click="openAdd()"
                                        class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 transition">
                                    + Ajouter une ligne
                                </button>
                            @endif
                        </div>

                        @if($rows->count() > 0)
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200 text-sm">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            @if(count($primaryKeys) > 0)
                                                <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                                            @endif
                                            @foreach(array_keys((array) $rows->first()) as $colName)
                                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase font-mono whitespace-nowrap">
                                                    {{ $colName }}
                                                </th>
                                            @endforeach
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @foreach($rows as $i => $row)
                                            <tr class="{{ $i % 2 === 0 ? 'bg-white' : 'bg-gray-50' }} hover:bg-green-50 transition-colors">
                                                @if(count($primaryKeys) > 0)
                                                    <td class="px-3 py-2 whitespace-nowrap">
                                                        <div class="flex space-x-1">
                                                            <button @click="openEdit(@js((array) $row))"
                                                                    class="inline-flex items-center px-2 py-1 bg-blue-100 text-blue-700 rounded hover:bg-blue-200 transition text-xs"
                                                                    title="Modifier">
                                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                                            </button>
                                                            <button @click="openDelete(@js((array) $row))"
                                                                    class="inline-flex items-center px-2 py-1 bg-red-100 text-red-700 rounded hover:bg-red-200 transition text-xs"
                                                                    title="Supprimer">
                                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                            </button>
                                                        </div>
                                                    </td>
                                                @endif
                                                @foreach((array) $row as $value)
                                                    <td class="px-4 py-2 font-mono text-xs whitespace-nowrap max-w-xs truncate
                                                        {{ is_null($value) ? 'text-gray-300 italic' : 'text-gray-800' }}"
                                                        title="{{ is_null($value) ? 'NULL' : e($value) }}">
                                                        @if(is_null($value))
                                                            <span class="italic">NULL</span>
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
                                <div class="mt-6 flex items-center justify-between border-t pt-4">
                                    <p class="text-sm text-gray-500">
                                        Page <span class="font-medium">{{ $page }}</span>
                                        sur <span class="font-medium">{{ $lastPage }}</span>
                                    </p>
                                    <div class="flex items-center space-x-1">
                                        @if($page > 1)
                                            <a href="{{ request()->fullUrlWithQuery(['page' => 1, 'tab' => 'browse']) }}"
                                               class="px-3 py-1.5 text-xs rounded bg-gray-100 hover:bg-gray-200 text-gray-600">&laquo;</a>
                                            <a href="{{ request()->fullUrlWithQuery(['page' => $page - 1, 'tab' => 'browse']) }}"
                                               class="px-3 py-1.5 text-xs rounded bg-gray-100 hover:bg-gray-200 text-gray-600">&lsaquo; Precedent</a>
                                        @endif

                                        @for($p = max(1, $page - 2); $p <= min($lastPage, $page + 2); $p++)
                                            <a href="{{ request()->fullUrlWithQuery(['page' => $p, 'tab' => 'browse']) }}"
                                               class="px-3 py-1.5 text-xs rounded {{ $p === $page ? 'bg-green-600 text-white font-bold' : 'bg-gray-100 hover:bg-gray-200 text-gray-600' }}">
                                                {{ $p }}
                                            </a>
                                        @endfor

                                        @if($page < $lastPage)
                                            <a href="{{ request()->fullUrlWithQuery(['page' => $page + 1, 'tab' => 'browse']) }}"
                                               class="px-3 py-1.5 text-xs rounded bg-gray-100 hover:bg-gray-200 text-gray-600">Suivant &rsaquo;</a>
                                            <a href="{{ request()->fullUrlWithQuery(['page' => $lastPage, 'tab' => 'browse']) }}"
                                               class="px-3 py-1.5 text-xs rounded bg-gray-100 hover:bg-gray-200 text-gray-600">&raquo;</a>
                                        @endif
                                    </div>
                                </div>
                            @endif

                        @else
                            <div class="text-center py-12">
                                <p class="text-gray-500">Aucune donnee dans cette table.</p>
                                @if(count($primaryKeys) > 0)
                                    <button @click="openAdd()"
                                            class="mt-4 inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 transition">
                                        + Ajouter une ligne
                                    </button>
                                @endif
                            </div>
                        @endif

                    </div>
                </div>
            </div>

        </div>

        {{-- ==================== MODAL EDIT ==================== --}}
        <div x-show="showEditModal" x-cloak
             class="fixed inset-0 z-50 flex items-center justify-center bg-black/50"
             @keydown.escape.window="showEditModal = false">
            <div @click.away="showEditModal = false"
                 class="bg-white rounded-lg shadow-xl w-full max-w-2xl max-h-[85vh] flex flex-col mx-4">
                <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
                    <h3 class="text-lg font-semibold text-gray-800">Modifier la ligne</h3>
                    <button @click="showEditModal = false" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <form method="POST" action="{{ route('databases.explorer.update', [$database, $databaseUser, $table]) }}"
                      class="flex flex-col overflow-hidden">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="page" value="{{ $page }}">

                    <div class="px-6 py-4 overflow-y-auto flex-1 space-y-3">
                        {{-- Primary key hidden fields --}}
                        <template x-for="pk in primaryKeys" :key="'edit-pk-' + pk">
                            <input type="hidden" :name="'pk[' + pk + ']'" :value="editPk[pk]">
                        </template>

                        {{-- Editable fields --}}
                        <template x-for="col in columns" :key="'edit-' + col.Field">
                            <div class="grid grid-cols-3 gap-3 items-start">
                                <label class="col-span-1 text-sm font-mono font-medium text-gray-700 pt-2"
                                       x-text="col.Field"></label>
                                <div class="col-span-2">
                                    <template x-if="primaryKeys.includes(col.Field)">
                                        <div class="px-3 py-2 bg-gray-100 rounded text-sm font-mono text-gray-500"
                                             x-text="editRow[col.Field] === null ? 'NULL' : editRow[col.Field]"></div>
                                    </template>
                                    <template x-if="!primaryKeys.includes(col.Field)">
                                        <div>
                                            <input type="text"
                                                   :name="'data[' + col.Field + ']'"
                                                   :value="editRow[col.Field]"
                                                   @input="editRow[col.Field] = $event.target.value"
                                                   class="w-full rounded-md border-gray-300 shadow-sm text-sm font-mono focus:border-blue-500 focus:ring-blue-500"
                                                   :placeholder="col.Type">
                                            <span class="text-xs text-gray-400 mt-0.5 block" x-text="col.Type"></span>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </template>
                    </div>

                    <div class="px-6 py-4 border-t border-gray-200 flex justify-end space-x-3 bg-gray-50">
                        <button type="button" @click="showEditModal = false"
                                class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50">
                            Annuler
                        </button>
                        <button type="submit"
                                class="px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md hover:bg-blue-700">
                            Enregistrer
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- ==================== MODAL ADD ==================== --}}
        <div x-show="showAddModal" x-cloak
             class="fixed inset-0 z-50 flex items-center justify-center bg-black/50"
             @keydown.escape.window="showAddModal = false">
            <div @click.away="showAddModal = false"
                 class="bg-white rounded-lg shadow-xl w-full max-w-2xl max-h-[85vh] flex flex-col mx-4">
                <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
                    <h3 class="text-lg font-semibold text-gray-800">Ajouter une ligne</h3>
                    <button @click="showAddModal = false" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <form method="POST" action="{{ route('databases.explorer.store', [$database, $databaseUser, $table]) }}"
                      class="flex flex-col overflow-hidden"
                      @submit="
                        {{-- Remove skipped fields before submitting --}}
                        document.querySelectorAll('[data-add-skip]').forEach(el => {
                            if (el.dataset.addSkip === 'true') el.remove();
                        });
                      ">
                    @csrf
                    <input type="hidden" name="page" value="{{ $page }}">

                    <div class="px-6 py-4 overflow-y-auto flex-1 space-y-3">
                        <template x-for="(field, index) in addFields" :key="'add-' + field.column">
                            <div class="grid grid-cols-3 gap-3 items-start" :data-add-skip="field.skip">
                                <label class="col-span-1 text-sm font-mono font-medium text-gray-700 pt-2">
                                    <span x-text="field.column"></span>
                                    <template x-if="field.autoIncrement">
                                        <span class="block text-xs text-gray-400 font-normal">auto_increment</span>
                                    </template>
                                </label>
                                <div class="col-span-2">
                                    <div class="flex items-center space-x-2">
                                        <template x-if="!field.skip">
                                            <div class="flex-1">
                                                <input type="hidden" :name="'fields[' + index + '][column]'" :value="field.column">
                                                <template x-if="!field.useNull">
                                                    <input type="text"
                                                           :name="'fields[' + index + '][value]'"
                                                           x-model="field.value"
                                                           class="w-full rounded-md border-gray-300 shadow-sm text-sm font-mono focus:border-green-500 focus:ring-green-500"
                                                           :placeholder="field.type">
                                                </template>
                                                <template x-if="field.useNull">
                                                    <div>
                                                        <input type="hidden" :name="'fields[' + index + '][value]'" value="">
                                                        <div class="px-3 py-2 bg-gray-100 rounded text-sm font-mono text-gray-400 italic">NULL</div>
                                                    </div>
                                                </template>
                                            </div>
                                        </template>
                                        <template x-if="field.skip">
                                            <div class="flex-1">
                                                <div class="px-3 py-2 bg-gray-100 rounded text-sm font-mono text-gray-400 italic">Auto</div>
                                            </div>
                                        </template>
                                        <template x-if="field.nullable || field.autoIncrement">
                                            <label class="flex items-center space-x-1 text-xs text-gray-500 whitespace-nowrap">
                                                <input type="checkbox" x-model="field.skip"
                                                       class="rounded border-gray-300 text-green-600 focus:ring-green-500"
                                                       @change="if(field.skip) field.useNull = false">
                                                <span>Ignorer</span>
                                            </label>
                                        </template>
                                        <template x-if="field.nullable && !field.skip">
                                            <label class="flex items-center space-x-1 text-xs text-gray-500 whitespace-nowrap">
                                                <input type="checkbox" x-model="field.useNull"
                                                       class="rounded border-gray-300 text-green-600 focus:ring-green-500">
                                                <span>NULL</span>
                                            </label>
                                        </template>
                                    </div>
                                    <span class="text-xs text-gray-400 mt-0.5 block" x-text="field.type"></span>
                                </div>
                            </div>
                        </template>
                    </div>

                    <div class="px-6 py-4 border-t border-gray-200 flex justify-end space-x-3 bg-gray-50">
                        <button type="button" @click="showAddModal = false"
                                class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50">
                            Annuler
                        </button>
                        <button type="submit"
                                class="px-4 py-2 text-sm font-medium text-white bg-green-600 border border-transparent rounded-md hover:bg-green-700">
                            Ajouter
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- ==================== MODAL DELETE ==================== --}}
        <div x-show="showDeleteModal" x-cloak
             class="fixed inset-0 z-50 flex items-center justify-center bg-black/50"
             @keydown.escape.window="showDeleteModal = false">
            <div @click.away="showDeleteModal = false"
                 class="bg-white rounded-lg shadow-xl w-full max-w-md mx-4">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-red-600">Confirmer la suppression</h3>
                </div>
                <form method="POST" action="{{ route('databases.explorer.delete', [$database, $databaseUser, $table]) }}">
                    @csrf
                    @method('DELETE')
                    <input type="hidden" name="page" value="{{ $page }}">

                    <div class="px-6 py-4">
                        <p class="text-sm text-gray-700 mb-3">
                            Etes-vous sur de vouloir supprimer cette ligne ?
                        </p>
                        <div class="bg-red-50 border border-red-200 rounded-md p-3">
                            <p class="text-xs font-mono text-red-800 break-all" x-text="deleteRowPreview"></p>
                        </div>
                        <p class="text-xs text-gray-500 mt-3">
                            Cette action est irreversible.
                        </p>

                        <template x-for="pk in primaryKeys" :key="'del-pk-' + pk">
                            <input type="hidden" :name="'pk[' + pk + ']'" :value="deleteRowPk[pk]">
                        </template>
                    </div>

                    <div class="px-6 py-4 border-t border-gray-200 flex justify-end space-x-3 bg-gray-50 rounded-b-lg">
                        <button type="button" @click="showDeleteModal = false"
                                class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50">
                            Annuler
                        </button>
                        <button type="submit"
                                class="px-4 py-2 text-sm font-medium text-white bg-red-600 border border-transparent rounded-md hover:bg-red-700">
                            Supprimer
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</x-app-layout>
