<div>
    <x-jet-form-section submit="update">
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

                <x-jet-input id="rlwr_event_id" type="text" class="mt-1 block w-full" wire:model.defer="rlwr_event_id"
                             {{-- :disabled="! Gate::check('update', $qlink) " --}} />

                <x-jet-label for="rlwr_event_id_allow_overwrite"
                             value="{{ __('Check to update/overwrite the RLWR') }}" />
                <x-jet-checkbox id="rlwr_event_id_allow_overwrite" wire:model.defer="rlwr_event_id_allow_overwrite"
                                class="mt-2" />

                <x-jet-input-error for="rlwr_event_id" class="mt-2" />
            </div>


            <!-- Invite Only Waiting Room ID -->
            <div class="col-span-6 sm:col-span-4">
                <x-jet-label for="iowr_event_id" value="{{ __('IOWR Event ID') }}" />

                <x-jet-input id="iowr_event_id" type="text" class="mt-1 block w-full" wire:model.defer="iowr_event_id"
                             :disabled="true" />

                <x-jet-input-error for="iowr_event_id" class="mt-2" />
            </div>

            <!-- IOWR CSV Info -->
            <div class="col-span-6 sm:col-span-4">

                <span class="italic"> Enter value from Invite Only csv or Access Link</span>
                <x-jet-label for="iowr_csv_value" value="{{ __('IOWR CSV Value') }}" />

                <x-jet-input id="iowr_csv_value" type="text" class="mt-1 block w-full" wire:model.defer="iowr_csv_value"
                             {{-- :disabled="! Gate::check('update', $qlink) " --}} />

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

        </x-slot>

        <x-slot name="actions">
            <x-jet-action-message class="mr-3" on="saved">
                {{ __('New link saved.') }}
            </x-jet-action-message>

            <x-jet-button>
                {{ __('Update') }}
            </x-jet-button>
        </x-slot>
    </x-jet-form-section>
</div>
