<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Database Manager Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Database Users Card -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <h3 class="text-lg font-semibold mb-4">Database Users</h3>
                        <p class="text-3xl font-bold text-blue-600">{{ \App\Models\DatabaseUser::count() }}</p>
                        <div class="mt-4">
                            <a href="{{ route('database-users.index') }}" class="text-blue-600 hover:text-blue-800">
                                View all users →
                            </a>
                        </div>
                        <div class="mt-2">
                            <a href="{{ route('database-users.create') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700">
                                Create New User
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Databases Card -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <h3 class="text-lg font-semibold mb-4">Databases</h3>
                        <p class="text-3xl font-bold text-green-600">{{ \App\Models\ManagedDatabase::count() }}</p>
                        <div class="mt-4">
                            <a href="{{ route('databases.index') }}" class="text-green-600 hover:text-green-800">
                                View all databases →
                            </a>
                        </div>
                        <div class="mt-2">
                            <a href="{{ route('databases.create') }}" class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700">
                                Create New Database
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Activity -->
            <div class="mt-6 bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-semibold mb-4">Quick Actions</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <a href="{{ route('database-users.create') }}" class="p-4 border-2 border-blue-200 rounded-lg hover:border-blue-400 transition">
                            <div class="text-blue-600 font-semibold">Create User</div>
                            <div class="text-sm text-gray-600">Add a new database user</div>
                        </a>
                        <a href="{{ route('databases.create') }}" class="p-4 border-2 border-green-200 rounded-lg hover:border-green-400 transition">
                            <div class="text-green-600 font-semibold">Create Database</div>
                            <div class="text-sm text-gray-600">Add a new database</div>
                        </a>
                        <a href="{{ route('database-users.index') }}" class="p-4 border-2 border-purple-200 rounded-lg hover:border-purple-400 transition">
                            <div class="text-purple-600 font-semibold">Manage Permissions</div>
                            <div class="text-sm text-gray-600">Assign user permissions</div>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
