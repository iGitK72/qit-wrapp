@if (!$read_only)
    <x-slot name="header_top">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Qlinks Administration') }}
        </h2>
    </x-slot>
@endif

<div>
    <div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8">
        {{-- @livewire('teams.update-team-name-form', ['team' => $team]) --}}
        <x-jet-form-section submit="add">
            <x-slot name="title">
                {{ __('Link Information') }}
            </x-slot>

            <x-slot name="description">
                {{ __('Link the Invite Only WR access links to the User/Email and/or Request Link WR .') }}
            </x-slot>

            <x-slot name="form">
                <!-- Visitor ID -->
                <div class="col-span-6 sm:col-span-4">
                    <x-jet-label for="visitor_id" value="{{ __('Visitor User ID') }}" />

                    <x-jet-input id="visitor_id" type="text" class="mt-1 block w-full" wire:model.defer="visitor_id"
                                 {{-- :disabled="! Gate::check('update', $team)" --}} />

                    <x-jet-input-error for="visitor_id" class="mt-2" />
                </div>

                <!-- Request Link Waiting Room ID -->
                <div class="col-span-6 sm:col-span-4">
                    <x-jet-label for="rlwr_event_id" value="{{ __('Request Link Waiting Room ID') }}" />

                    <x-jet-input id="rlwr_event_id" type="text" class="mt-1 block w-full"
                                 wire:model.defer="rlwr_event_id" {{-- :disabled="! Gate::check('update', $qlink) " --}} />

                    <x-jet-label for="rlwr_event_id_allow_overwrite"
                                 value="{{ __('Check to update/overwrite the RLWR') }}" />
                    <x-jet-checkbox id="rlwr_event_id_allow_overwrite" wire:model.defer="rlwr_event_id_allow_overwrite"
                                    class="mt-2" />

                    <x-jet-input-error for="rlwr_event_id" class="mt-2" />
                </div>

                <!-- IOWR CSV Info -->
                <div class="col-span-6 sm:col-span-4">

                    <span class="italic"> Enter value from Invite Only csv or Access Link</span>
                    <x-jet-label for="iowr_csv_value" value="{{ __('IOWR CSV Value') }}" />

                    <x-jet-input id="iowr_csv_value" type="text" class="mt-1 block w-full"
                                 wire:model.defer="iowr_csv_value" {{-- :disabled="! Gate::check('update', $qlink) " --}} />

                    <x-jet-input-error for="iowr_csv_value" class="mt-2" />
                </div>

                <!-- IOWR Access Link (use this or the Event ID above) -->
                <div class="col-span-6 sm:col-span-4">
                    <x-jet-label for="iowr_access_link" value="{{ __('IOWR Access Link') }}" />

                    <x-jet-input id="iowr_access_link" type="text" class="mt-1 block w-full"
                                 wire:model.defer="iowr_access_link" {{-- :disabled="! Gate::check('update', $qlink) " --}} />

                    <x-jet-input-error for="iowr_access_link" class="mt-2" />
                </div>

                <!-- IOWR Visitor Identity Key -->
                <div class="col-span-6 sm:col-span-4">
                    <x-jet-label for="visitor_identity_key" value="{{ __('Visitor Identity Key') }}" />

                    <x-jet-input id="visitorIdentityKey" type="text" class="mt-1 block w-full"
                                 wire:model.defer="visitor_identity_key" :disabled="true" />

                    @if (session('alert-status'))
                        <div class="mt-2 text-red-600">
                            {{ session('alert-status') }}
                        </div>
                    @endif

                    <x-jet-input-error for="visitor_identity_key" class="mt-2" />
                </div>

                <!-- Invite Only Waiting Room ID -->
                <div class="col-span-6 sm:col-span-4">
                    <x-jet-label for="iowr_event_id" value="{{ __('IOWR Event ID') }}" />

                    <x-jet-input id="iowr_event_id" type="text" class="mt-1 block w-full"
                                 wire:model.defer="iowr_event_id" :disabled="true" />

                    <x-jet-input-error for="iowr_event_id" class="mt-2" />
                </div>


            </x-slot>

            <x-slot name="actions">
                <x-jet-action-message class="mr-3" on="saved">
                    {{ __('New link saved.') }}
                </x-jet-action-message>

                <x-jet-button>
                    {{ __('Add') }}
                </x-jet-button>
            </x-slot>
        </x-jet-form-section>

        <x-jet-form-section submit="save">
            <x-slot name="title">
                {{ __('Upload Access Link CSV') }}
            </x-slot>

            <x-slot name="description">
                {{ __('Upload a csv file with access link and visitor identity key information.') }}
            </x-slot>

            <x-slot name="form">
                <div x-data="{ isUploading: false, progress: 0 }" x-on:livewire-upload-start="isUploading = true"
                     x-on:livewire-upload-finish="isUploading = false" x-on:livewire-upload-error="isUploading = false"
                     x-on:livewire-upload-progress="progress = $event.detail.progress">

                    <!-- File Input -->

                    <input type="file" wire:model="csvfile">

                    <!-- Progress Bar -->
                    <div x-show="isUploading">
                        <progress max="100" x-bind:value="progress"></progress>
                    </div>

                </div>
                <div class="col-span-6 sm:col-span-4 mt-6">
                    <div>
                        <div class="mt-2 text-sm text-red-600" wire:loading wire:target="save">
                            Please be patient, processing may take a while. We will let you know when it is done.
                        </div>
                    </div>
                    @if (session('upload-status'))
                        <div class="mt-2 text-red-600">
                            {{ session('upload-status') }}
                        </div>
                    @endif
                </div>

            </x-slot>

            <x-slot name="actions">
                <x-jet-action-message class="mr-3" on="uploaded">
                    {{ __('File uploaded.') }}
                </x-jet-action-message>

                <x-jet-button>
                    {{ __('Upload') }}
                </x-jet-button>
            </x-slot>

        </x-jet-form-section>
    </div>
</div>
