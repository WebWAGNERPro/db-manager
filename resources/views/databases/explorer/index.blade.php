<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Explorer :
                    <span class="font-mono text-orange-600">{{ $database->database_name }}</span>
                </h2>
                <p class="text-sm text-gray-500 mt-0.5">
                    Connecté en tant que
                    <span class="font-mono font-medium text-gray-700">{{ $databaseUser->username }}@{{ $databaseUser->host }}</span>
                </p>
            </div>
            <a href="{{ route('databases.show', $database) }}"
               class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                ← Retour à la base
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            @if($errors->any())
                <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                    {{ $errors->first() }}
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold mb-4">
                        Tables
                        <span class="ml-2 text-sm font-normal text-gray-500">({{ count($tables) }})</span>
                    </h3>

                    @if(count($tables) > 0)
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Table</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Lignes</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Moteur</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Collation</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Taille</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($tables as $t)
                                        @php
                                            $t      = (array) $t;
                                            $name   = $t['Name'];
                                            $rows   = number_format($t['Rows'] ?? 0);
                                            $engine = $t['Engine'] ?? '-';
                                            $collation = $t['Collation'] ?? '-';
                                            $sizeBytes = ($t['Data_length'] ?? 0) + ($t['Index_length'] ?? 0);
                                            $size   = $sizeBytes >= 1048576
                                                ? number_format($sizeBytes / 1048576, 2) . ' MB'
                                                : number_format($sizeBytes / 1024, 1) . ' KB';
                                        @endphp
                                        <tr class="hover:bg-orange-50 transition-colors">
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <a href="{{ route('databases.explorer.table', [$database, $databaseUser, $name]) }}"
                                                   class="font-mono font-medium text-blue-600 hover:text-blue-800">
                                                    {{ $name }}
                                                </a>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                                {{ $rows }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                                {{ $engine }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 font-mono text-xs">
                                                {{ $collation }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                                {{ $size }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-3">
                                                <a href="{{ route('databases.explorer.table', [$database, $databaseUser, $name]) }}"
                                                   class="text-blue-600 hover:text-blue-800">Structure</a>
                                                <a href="{{ route('databases.explorer.table', [$database, $databaseUser, $name]) }}?tab=browse"
                                                   class="text-green-600 hover:text-green-800">Parcourir</a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-12">
                            <p class="text-gray-500 text-lg">Aucune table dans cette base de données.</p>
                        </div>
                    @endif
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
