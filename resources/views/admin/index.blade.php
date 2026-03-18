<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-red-100 text-red-800">
                Admin
            </span>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Admin Panel') }}
            </h2>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">

            {{-- Flash messages --}}
            @if(session('error'))
                <div class="flex items-center gap-3 p-4 bg-red-50 border border-red-200 text-red-800 rounded-lg text-sm">
                    <svg class="w-5 h-5 shrink-0 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm-1-9v4a1 1 0 002 0V9a1 1 0 00-2 0zm0-4a1 1 0 112 0 1 1 0 01-2 0z" clip-rule="evenodd"/>
                    </svg>
                    {{ session('error') }}
                </div>
            @endif
            @if(session('success'))
                <div class="flex items-center gap-3 p-4 bg-green-50 border border-green-200 text-green-800 rounded-lg text-sm">
                    <svg class="w-5 h-5 shrink-0 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    {{ session('success') }}
                </div>
            @endif

            {{-- Users table --}}
            <div class="bg-white shadow-sm sm:rounded-lg overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100">
                    <h3 class="font-semibold text-gray-800">All Users</h3>
                    <p class="text-sm text-gray-500 mt-0.5">Manage user roles. You cannot change your own role.</p>
                </div>
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left font-semibold text-gray-600 uppercase tracking-wider">Name</th>
                            <th class="px-6 py-3 text-left font-semibold text-gray-600 uppercase tracking-wider">Email</th>
                            <th class="px-6 py-3 text-left font-semibold text-gray-600 uppercase tracking-wider">Role</th>
                            <th class="px-6 py-3 text-left font-semibold text-gray-600 uppercase tracking-wider">Joined</th>
                            <th class="px-6 py-3 text-right font-semibold text-gray-600 uppercase tracking-wider">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($users as $u)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 font-medium text-gray-900">{{ e($u->name) }}</td>
                                <td class="px-6 py-4 text-gray-600">{{ e($u->email) }}</td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                        {{ $u->role === 'admin' ? 'bg-red-100 text-red-800' : 'bg-gray-100 text-gray-700' }}">
                                        {{ ucfirst(e($u->role)) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-gray-500">{{ date('M d, Y', strtotime($u->created_at)) }}</td>
                                <td class="px-6 py-4 text-right">
                                    @if($u->id !== auth()->id())
                                        <form method="POST"
                                              action="{{ route('admin.toggle-role', $u->id) }}"
                                              onsubmit="return confirm('Change role for {{ e($u->name) }}?')">
                                            @csrf
                                            <button type="submit"
                                                    class="text-xs font-medium text-indigo-600 hover:text-indigo-800 underline">
                                                {{ $u->role === 'admin' ? 'Demote to User' : 'Promote to Admin' }}
                                            </button>
                                        </form>
                                    @else
                                        <span class="text-xs text-gray-400">You</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- All Records --}}
            <div class="bg-white shadow-sm sm:rounded-lg overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                    <div>
                        <h3 class="font-semibold text-gray-800">All Records</h3>
                        <p class="text-sm text-gray-500 mt-0.5">View-only. Records belong to individual users.</p>
                    </div>
                    <form method="GET" action="{{ route('admin.index') }}" class="flex gap-2">
                        <input type="text" name="search" value="{{ $search }}" placeholder="Search…"
                               class="rounded-lg border-gray-300 shadow-sm text-sm focus:border-indigo-500 focus:ring-indigo-500" />
                        <button type="submit"
                                class="px-3 py-1.5 bg-indigo-600 text-white text-sm rounded-lg hover:bg-indigo-700 transition">
                            Search
                        </button>
                        @if($search)
                            <a href="{{ route('admin.index') }}"
                               class="px-3 py-1.5 bg-gray-100 text-gray-700 text-sm rounded-lg hover:bg-gray-200 transition">
                                Clear
                            </a>
                        @endif
                    </form>
                </div>

                @if(count($records) > 0)
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left font-semibold text-gray-600 uppercase tracking-wider">Record Name</th>
                                <th class="px-6 py-3 text-left font-semibold text-gray-600 uppercase tracking-wider">Email</th>
                                <th class="px-6 py-3 text-left font-semibold text-gray-600 uppercase tracking-wider">Department</th>
                                <th class="px-6 py-3 text-left font-semibold text-gray-600 uppercase tracking-wider">Owner</th>
                                <th class="px-6 py-3 text-left font-semibold text-gray-600 uppercase tracking-wider">Created</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($records as $record)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 font-medium text-gray-900">
                                        {{ e($record->first_name) }} {{ e($record->last_name) }}
                                    </td>
                                    <td class="px-6 py-4 text-gray-600">{{ e($record->email) }}</td>
                                    <td class="px-6 py-4 text-gray-600">{{ e($record->department ?? '—') }}</td>
                                    <td class="px-6 py-4 text-gray-600">
                                        <span class="font-medium">{{ e($record->owner_name) }}</span>
                                        <span class="text-gray-400 text-xs block">{{ e($record->owner_email) }}</span>
                                    </td>
                                    <td class="px-6 py-4 text-gray-500">{{ date('M d, Y', strtotime($record->created_at)) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <div class="flex flex-col items-center justify-center py-12 text-gray-400">
                        <p class="text-sm">No records found.</p>
                    </div>
                @endif
            </div>

        </div>
    </div>
</x-app-layout>
