<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="font-semibold text-xl text-gray-100 leading-tight">
                    Explorer :
                    <span class="font-mono text-orange-400">{{ $database->database_name }}</span>
                </h2>
                <p class="text-sm text-gray-500 mt-1">
                    Connecte en tant que
                    <span class="font-mono font-medium text-gray-300">{{ $databaseUser->username }}@{{ $databaseUser->host }}</span>
                </p>
            </div>
            <a href="{{ route('databases.show', $database) }}"
               class="inline-flex items-center px-4 py-2 bg-gray-800 border border-gray-600 rounded-lg font-semibold text-xs text-gray-300 uppercase tracking-widest hover:bg-gray-700 transition">
                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                Retour
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            @if($errors->any())
                <div class="mb-6 bg-red-500/10 border border-red-500/20 text-red-400 px-4 py-3 rounded-lg">
                    {{ $errors->first() }}
                </div>
            @endif

            <div class="bg-gray-900 border border-gray-800 overflow-hidden rounded-xl">
                <div class="p-6">
                    <h3 class="text-sm font-medium text-gray-400 uppercase tracking-wider mb-5">
                        Tables
                        <span class="ml-2 text-gray-500 normal-case">({{ count($tables) }})</span>
                    </h3>

                    @if(count($tables) > 0)
                        <div class="overflow-x-auto rounded-lg border border-gray-800">
                            <table class="min-w-full divide-y divide-gray-800">
                                <thead>
                                    <tr class="bg-gray-800/50">
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Table</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Lignes</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Moteur</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Collation</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Taille</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-800">
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
                                        <tr class="hover:bg-gray-800/50 transition-colors">
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <a href="{{ route('databases.explorer.table', [$database, $databaseUser, $name]) }}"
                                                   class="font-mono font-medium text-orange-400 hover:text-orange-300 transition">
                                                    {{ $name }}
                                                </a>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-400 tabular-nums">
                                                {{ $rows }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-400">
                                                <code class="text-xs bg-gray-800 text-gray-300 px-1.5 py-0.5 rounded">{{ $engine }}</code>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 font-mono text-xs">
                                                {{ $collation }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-400 tabular-nums">
                                                {{ $size }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                <div class="flex items-center gap-3">
                                                    <a href="{{ route('databases.explorer.table', [$database, $databaseUser, $name]) }}"
                                                       class="text-gray-400 hover:text-blue-400 transition">Structure</a>
                                                    <a href="{{ route('databases.explorer.table', [$database, $databaseUser, $name]) }}?tab=browse"
                                                       class="text-gray-400 hover:text-emerald-400 transition">Parcourir</a>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-12">
                            <p class="text-gray-500 text-lg">Aucune table dans cette base de donnees.</p>
                        </div>
                    @endif
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
