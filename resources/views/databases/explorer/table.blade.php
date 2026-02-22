<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    <a href="{{ route('databases.explorer', [$database, $databaseUser]) }}"
                       class="text-orange-600 hover:text-orange-700 font-mono">{{ $database->database_name }}</a>
                    <span class="text-gray-400 mx-1">›</span>
                    <span class="font-mono">{{ $table }}</span>
                </h2>
                <p class="text-sm text-gray-500 mt-0.5">
                    Connecté en tant que
                    <span class="font-mono font-medium text-gray-700">{{ $databaseUser->username }}@{{ $databaseUser->host }}</span>
                    &mdash; {{ number_format($total) }} ligne{{ $total > 1 ? 's' : '' }}
                </p>
            </div>
            <a href="{{ route('databases.explorer', [$database, $databaseUser]) }}"
               class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                ← Tables
            </a>
        </div>
    </x-slot>

    <div class="py-8" x-data="{ tab: '{{ request('tab', 'structure') }}' }">
        <div class="max-w-full mx-auto sm:px-6 lg:px-8">

            @if($errors->any())
                <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                    {{ $errors->first() }}
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
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Clé</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Défaut</th>
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
                                                        <span class="mr-1" title="Clé primaire">🔑</span>
                                                    @elseif($col['Key'] === 'MUL')
                                                        <span class="mr-1" title="Index">🔗</span>
                                                    @elseif($col['Key'] === 'UNI')
                                                        <span class="mr-1" title="Unique">✦</span>
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
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Cardinalité</th>
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

                        {{-- En-tête de l'onglet --}}
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-base font-semibold text-gray-700">
                                Données
                                @if($total > 0)
                                    <span class="text-sm font-normal text-gray-400">
                                        &mdash; lignes {{ ($page - 1) * $perPage + 1 }}–{{ min($page * $perPage, $total) }}
                                        sur {{ number_format($total) }}
                                    </span>
                                @endif
                            </h3>
                        </div>

                        @if($rows->count() > 0)
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200 text-sm">
                                    <thead class="bg-gray-50">
                                        <tr>
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
                                        {{-- Première --}}
                                        @if($page > 1)
                                            <a href="{{ request()->fullUrlWithQuery(['page' => 1, 'tab' => 'browse']) }}"
                                               class="px-3 py-1.5 text-xs rounded bg-gray-100 hover:bg-gray-200 text-gray-600">«</a>
                                            <a href="{{ request()->fullUrlWithQuery(['page' => $page - 1, 'tab' => 'browse']) }}"
                                               class="px-3 py-1.5 text-xs rounded bg-gray-100 hover:bg-gray-200 text-gray-600">‹ Précédent</a>
                                        @endif

                                        {{-- Pages autour de la courante --}}
                                        @for($p = max(1, $page - 2); $p <= min($lastPage, $page + 2); $p++)
                                            <a href="{{ request()->fullUrlWithQuery(['page' => $p, 'tab' => 'browse']) }}"
                                               class="px-3 py-1.5 text-xs rounded {{ $p === $page ? 'bg-green-600 text-white font-bold' : 'bg-gray-100 hover:bg-gray-200 text-gray-600' }}">
                                                {{ $p }}
                                            </a>
                                        @endfor

                                        {{-- Dernière --}}
                                        @if($page < $lastPage)
                                            <a href="{{ request()->fullUrlWithQuery(['page' => $page + 1, 'tab' => 'browse']) }}"
                                               class="px-3 py-1.5 text-xs rounded bg-gray-100 hover:bg-gray-200 text-gray-600">Suivant ›</a>
                                            <a href="{{ request()->fullUrlWithQuery(['page' => $lastPage, 'tab' => 'browse']) }}"
                                               class="px-3 py-1.5 text-xs rounded bg-gray-100 hover:bg-gray-200 text-gray-600">»</a>
                                        @endif
                                    </div>
                                </div>
                            @endif

                        @else
                            <div class="text-center py-12">
                                <p class="text-gray-500">Aucune donnée dans cette table.</p>
                            </div>
                        @endif

                    </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
