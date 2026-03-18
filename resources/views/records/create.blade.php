<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('records.index') }}" class="text-gray-400 hover:text-gray-600 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Create Record') }}
            </h2>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm sm:rounded-lg p-6 sm:p-8">

                {{-- Alpine.js handles client-side validation before the form submits --}}
                <form method="POST" action="{{ route('records.store') }}"
                      x-data="{
                          fields: {
                              first_name: '{{ old('first_name') }}',
                              last_name:  '{{ old('last_name') }}',
                              email:      '{{ old('email') }}',
                              phone:      '{{ old('phone') }}',
                              department: '{{ old('department') }}',
                              notes:      '{{ old('notes') }}'
                          },
                          errors: {},
                          validate() {
                              this.errors = {};
                              if (!this.fields.first_name.trim())
                                  this.errors.first_name = 'First name is required.';
                              if (!this.fields.last_name.trim())
                                  this.errors.last_name = 'Last name is required.';
                              if (!this.fields.email.trim()) {
                                  this.errors.email = 'Email is required.';
                              } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(this.fields.email)) {
                                  this.errors.email = 'Please enter a valid email address.';
                              }
                              if (this.fields.phone && !/^[\d\s\+\-\(\)]{7,20}$/.test(this.fields.phone))
                                  this.errors.phone = 'Please enter a valid phone number.';
                              return Object.keys(this.errors).length === 0;
                          },
                          submit(e) {
                              if (!this.validate()) e.preventDefault();
                          }
                      }"
                      @submit="submit($event)"
                      novalidate>
                    @csrf

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">

                        {{-- First Name --}}
                        <div>
                            <x-input-label for="first_name" :value="__('First Name *')" />
                            <x-text-input id="first_name" name="first_name" type="text"
                                class="mt-1 block w-full"
                                x-model="fields.first_name"
                                :value="old('first_name')"
                                autocomplete="given-name" />
                            <p x-show="errors.first_name" x-text="errors.first_name"
                               class="mt-1 text-sm text-red-600" x-cloak></p>
                            <x-input-error :messages="$errors->get('first_name')" class="mt-2" />
                        </div>

                        {{-- Last Name --}}
                        <div>
                            <x-input-label for="last_name" :value="__('Last Name *')" />
                            <x-text-input id="last_name" name="last_name" type="text"
                                class="mt-1 block w-full"
                                x-model="fields.last_name"
                                :value="old('last_name')"
                                autocomplete="family-name" />
                            <p x-show="errors.last_name" x-text="errors.last_name"
                               class="mt-1 text-sm text-red-600" x-cloak></p>
                            <x-input-error :messages="$errors->get('last_name')" class="mt-2" />
                        </div>

                        {{-- Email --}}
                        <div class="sm:col-span-2">
                            <x-input-label for="email" :value="__('Email *')" />
                            <x-text-input id="email" name="email" type="email"
                                class="mt-1 block w-full"
                                x-model="fields.email"
                                :value="old('email')"
                                autocomplete="email" />
                            <p x-show="errors.email" x-text="errors.email"
                               class="mt-1 text-sm text-red-600" x-cloak></p>
                            <x-input-error :messages="$errors->get('email')" class="mt-2" />
                        </div>

                        {{-- Phone --}}
                        <div>
                            <x-input-label for="phone" :value="__('Phone')" />
                            <x-text-input id="phone" name="phone" type="tel"
                                class="mt-1 block w-full"
                                x-model="fields.phone"
                                :value="old('phone')"
                                autocomplete="tel" />
                            <p x-show="errors.phone" x-text="errors.phone"
                               class="mt-1 text-sm text-red-600" x-cloak></p>
                            <x-input-error :messages="$errors->get('phone')" class="mt-2" />
                        </div>

                        {{-- Department --}}
                        <div>
                            <x-input-label for="department" :value="__('Department')" />
                            <x-text-input id="department" name="department" type="text"
                                class="mt-1 block w-full"
                                x-model="fields.department"
                                :value="old('department')" />
                            <x-input-error :messages="$errors->get('department')" class="mt-2" />
                        </div>

                        {{-- Notes --}}
                        <div class="sm:col-span-2">
                            <x-input-label for="notes" :value="__('Notes')" />
                            <textarea id="notes" name="notes" rows="3"
                                x-model="fields.notes"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"
                            >{{ old('notes') }}</textarea>
                            <x-input-error :messages="$errors->get('notes')" class="mt-2" />
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-3 mt-6 pt-6 border-t border-gray-100">
                        <a href="{{ route('records.index') }}"
                           class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition">
                            Cancel
                        </a>
                        <x-primary-button>
                            {{ __('Create Record') }}
                        </x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
