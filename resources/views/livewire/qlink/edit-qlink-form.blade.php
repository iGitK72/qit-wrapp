<div>
    <x-jet-form-section submit="update">
        <x-slot name="title">
            {{ __('Link Information') }}
        </x-slot>

        <x-slot name="description">
            {{ __('View and update key and related information about the access link using our REST-API.') }}
        </x-slot>

        <x-slot name="form">
            <!-- Invite Only Waiting Room ID -->
            <div class="col-span-6 sm:col-span-4">
                <x-jet-label for="qlink.event_id" value="{{ __('IOWR Event ID') }}" />

                <x-jet-input id="qlink.event_id" type="text" class="text-gray-400 mt-1 block w-full"
                             wire:model.defer="qlink.event_id" :disabled="true" />

                <x-jet-input-error for="qlink.event_id" class="mt-2" />
            </div>

            <!-- IOWR Access Link (use this or the Event ID above) -->
            <div class="col-span-6 sm:col-span-4">
                <x-jet-label for="qlink.access_link" value="{{ __('IOWR Access Link') }}" />

                <x-jet-input id="qlink.access_link" type="text" class="text-gray-400 mt-1 block w-full"
                             wire:model.defer="qlink.access_link" :disabled="true" />

                <x-jet-input-error for="qlink.access_link" class="mt-2" />
            </div>

            <!-- IOWR Visitor Identity Key -->
            <div class="col-span-6 sm:col-span-4">
                <x-jet-label for="qlink.visitor_identity_key" value="{{ __('Visitor Identity Key') }}" />

                <x-jet-input id="qlink.visitor_identity_key" type="text" class="text-gray-400 mt-1 block w-full"
                             wire:model.defer="qlink.visitor_identity_key" :disabled="true" />

                <x-jet-input-error for="qlink.visitor_identity_key" class="mt-2" />
            </div>

            <!-- Token Identifier -->
            <div class="col-span-6 sm:col-span-4">
                <x-jet-label for="qlink.token_identifier" value="{{ __('Token Identifier') }}" />

                <x-jet-input id="qlink.token_identifier" type="text" class="text-gray-400 mt-1 block w-full"
                             wire:model.defer="qlink.token_identifier" :disabled="true" />

                <span wire:click="$toggle('show_info_by_ti')">
                    <div class="mt-3 flex items-center text-sm font-semibold text-indigo-700">
                        <div class="{{ !$show_info_by_ti ? '' : 'hidden' }}"> Show QueueItem Info by Token</div>
                        <div class="{{ $show_info_by_ti ? '' : 'hidden' }}"> Hide QueueItem Info by Token</div>
                    </div>
                </span>

                <x-jet-input-error for="qlink.token_identifier" class="mt-2" />
            </div>
            <!-- QueueItem by Token Identifier -->
            <div class="{{ $show_info_by_ti ? '' : 'hidden' }} col-span-6 sm:col-span-4">
                <x-jet-label for="queue_item_details" value="{{ __('QueueItem Details') }}" />
                @foreach ($queue_item_details as $heading => $queue_item_detail)
                    @if (is_array($queue_item_detail))
                        @foreach ($queue_item_detail as $more_detail)
                            {{ $more_detail }}
                            <br>
                        @endforeach
                    @else
                        {{ $heading . ' => ' . $queue_item_detail }}
                    @endif
                    <br>
                @endforeach
                <x-jet-input-error for="queue_item_details" class="mt-2" />
            </div>

            <!-- Visitor ID -->
            <div class="col-span-6 sm:col-span-4">
                <x-jet-label for="qlink.visitor_id" value="{{ __('Visitor User ID') }}" />

                <x-jet-input id="qlink.visitor_id" type="text" class="mt-1 block w-full"
                             wire:model.defer="qlink.visitor_id" />

                <x-jet-input-error for="qlink.visitor_id" class="mt-2" />
            </div>

            <!-- Request Link Waiting Room ID -->
            <div class="col-span-6 sm:col-span-4">
                <x-jet-label for="qlink.rlwr_event_id" value="{{ __('Request Link Waiting Room ID') }}" />

                <x-jet-input id="qlink.rlwr_event_id" type="text" class="mt-1 block w-full"
                             wire:model.defer="qlink.rlwr_event_id" {{-- :disabled="! Gate::check('update', $qlink) " --}} />

                <x-jet-input-error for="qlink.rlwr_event_id" class="mt-2" />

                <x-jet-label for="rlwr_event_id_allow_overwrite"
                             value="{{ __('Check to update/overwrite the RLWR') }}" />
                <x-jet-checkbox id="rlwr_event_id_allow_overwrite" wire:model.defer="rlwr_event_id_allow_overwrite"
                                class="mt-2" />
            </div>

        </x-slot>

        <x-slot name="actions">
            <x-jet-action-message class="mr-3" on="saved">
                {{ __('Link saved.') }}
            </x-jet-action-message>

            <x-jet-button>
                {{ __('Update') }}
            </x-jet-button>
        </x-slot>
    </x-jet-form-section>
</div>
