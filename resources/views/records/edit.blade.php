<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('records.index') }}" class="text-gray-400 hover:text-gray-600 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Edit Record') }}
            </h2>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm sm:rounded-lg p-6 sm:p-8">
                <form method="POST" action="{{ route('records.update', $record->id) }}" novalidate>
                    @csrf
                    @method('PUT')

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                        {{-- First Name --}}
                        <div>
                            <x-input-label for="first_name" :value="__('First Name')" />
                            <x-text-input id="first_name" name="first_name" type="text"
                                class="mt-1 block w-full"
                                :value="old('first_name', $record->first_name)"
                                required autofocus autocomplete="given-name" />
                            <x-input-error :messages="$errors->get('first_name')" class="mt-2" />
                        </div>

                        {{-- Last Name --}}
                        <div>
                            <x-input-label for="last_name" :value="__('Last Name')" />
                            <x-text-input id="last_name" name="last_name" type="text"
                                class="mt-1 block w-full"
                                :value="old('last_name', $record->last_name)"
                                required autocomplete="family-name" />
                            <x-input-error :messages="$errors->get('last_name')" class="mt-2" />
                        </div>

                        {{-- Email --}}
                        <div class="sm:col-span-2">
                            <x-input-label for="email" :value="__('Email')" />
                            <x-text-input id="email" name="email" type="email"
                                class="mt-1 block w-full"
                                :value="old('email', $record->email)"
                                required autocomplete="email" />
                            <x-input-error :messages="$errors->get('email')" class="mt-2" />
                        </div>

                        {{-- Phone --}}
                        <div>
                            <x-input-label for="phone" :value="__('Phone')" />
                            <x-text-input id="phone" name="phone" type="text"
                                class="mt-1 block w-full"
                                :value="old('phone', $record->phone)"
                                autocomplete="tel" />
                            <x-input-error :messages="$errors->get('phone')" class="mt-2" />
                        </div>

                        {{-- Department --}}
                        <div>
                            <x-input-label for="department" :value="__('Department')" />
                            <x-text-input id="department" name="department" type="text"
                                class="mt-1 block w-full"
                                :value="old('department', $record->department)" />
                            <x-input-error :messages="$errors->get('department')" class="mt-2" />
                        </div>

                        {{-- Notes --}}
                        <div class="sm:col-span-2">
                            <x-input-label for="notes" :value="__('Notes')" />
                            <textarea id="notes" name="notes" rows="3"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"
                            >{{ old('notes', $record->notes) }}</textarea>
                            <x-input-error :messages="$errors->get('notes')" class="mt-2" />
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-3 mt-6 pt-6 border-t border-gray-100">
                        <a href="{{ route('records.index') }}"
                           class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition">
                            Cancel
                        </a>
                        <x-primary-button>
                            {{ __('Save Changes') }}
                        </x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
