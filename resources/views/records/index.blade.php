<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Records') }}
            </h2>
            <a href="{{ route('records.create') }}"
               class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white text-sm font-semibold rounded-lg shadow hover:bg-indigo-700 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                New Record
            </a>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-4">

            {{-- Flash messages --}}
            @if(session('success'))
                <div class="flex items-center gap-3 p-4 bg-green-50 border border-green-200 text-green-800 rounded-lg text-sm">
                    <svg class="w-5 h-5 shrink-0 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    {{ session('success') }}
                </div>
            @endif

            {{-- Search --}}
            <div class="bg-white shadow-sm sm:rounded-lg p-4">
                <form method="GET" action="{{ route('records.index') }}" class="flex gap-3">
                    <input
                        type="text"
                        name="search"
                        value="{{ $search }}"
                        placeholder="Search by name, email, department…"
                        class="flex-1 rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"
                    />
                    <button type="submit"
                            class="px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 transition">
                        Search
                    </button>
                    @if($search)
                        <a href="{{ route('records.index') }}"
                           class="px-4 py-2 bg-gray-100 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-200 transition">
                            Clear
                        </a>
                    @endif
                </form>
            </div>

            {{-- Table --}}
            <div class="bg-white shadow-sm sm:rounded-lg overflow-hidden">
                @if(count($records) > 0)
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left font-semibold text-gray-600 uppercase tracking-wider">Name</th>
                                <th class="px-6 py-3 text-left font-semibold text-gray-600 uppercase tracking-wider">Email</th>
                                <th class="px-6 py-3 text-left font-semibold text-gray-600 uppercase tracking-wider">Phone</th>
                                <th class="px-6 py-3 text-left font-semibold text-gray-600 uppercase tracking-wider">Department</th>
                                <th class="px-6 py-3 text-left font-semibold text-gray-600 uppercase tracking-wider">Created</th>
                                <th class="px-6 py-3 text-right font-semibold text-gray-600 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 bg-white">
                            @foreach($records as $record)
                                <tr class="hover:bg-gray-50 transition">
                                    <td class="px-6 py-4 font-medium text-gray-900">
                                        {{ e($record->first_name) }} {{ e($record->last_name) }}
                                    </td>
                                    <td class="px-6 py-4 text-gray-600">{{ e($record->email) }}</td>
                                    <td class="px-6 py-4 text-gray-600">{{ e($record->phone ?? '—') }}</td>
                                    <td class="px-6 py-4 text-gray-600">
                                        @if($record->department)
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800">
                                                {{ e($record->department) }}
                                            </span>
                                        @else
                                            —
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-gray-500">
                                        {{ date('M d, Y', strtotime($record->created_at)) }}
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            <a href="{{ route('records.edit', $record->id) }}"
                                               class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-medium text-indigo-700 bg-indigo-50 rounded-md hover:bg-indigo-100 transition">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536M9 13l6.586-6.586a2 2 0 112.828 2.828L11.828 15.828a2 2 0 01-1.414.586H9v-1.414a2 2 0 01.586-1.414z"/>
                                                </svg>
                                                Edit
                                            </a>

                                            {{-- Svelte delete confirmation component --}}
                                            <div data-svelte-component="DeleteConfirm"
                                                 data-svelte-props="{{ json_encode([
                                                     'recordId'   => $record->id,
                                                     'recordName' => e($record->first_name) . ' ' . e($record->last_name),
                                                     'deleteUrl'  => route('records.destroy', $record->id),
                                                     'csrfToken'  => csrf_token(),
                                                 ]) }}">
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <div class="flex flex-col items-center justify-center py-16 text-gray-400">
                        <svg class="w-12 h-12 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        <p class="text-lg font-medium">No records yet</p>
                        <p class="text-sm mt-1">
                            <a href="{{ route('records.create') }}" class="text-indigo-600 hover:underline">Create your first record</a>
                        </p>
                    </div>
                @endif
            </div>

        </div>
    </div>
</x-app-layout>
