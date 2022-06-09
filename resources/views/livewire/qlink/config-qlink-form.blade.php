@if (!$read_only)
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Customer Settings') }}
        </h2>
    </x-slot>
@endif

<div>
    <div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8">
        {{-- @livewire('teams.update-team-name-form', ['team' => $team]) --}}
        <x-jet-form-section submit="update">
            <x-slot name="title">
                {{ __('GO Customer Info') }}
            </x-slot>

            <x-slot name="description">
                {{ __('Customer ID and API access key of the WR account.') }}
            </x-slot>

            <x-slot name="form">
                <!-- Customer Information -->
                <div class="col-span-6 sm:col-span-4">
                    <x-jet-label for="customer_id" value="{{ __('Customer ID') }}" />

                    <x-jet-input id="customer_id" type="text" class="mt-1 block w-full" wire:model.defer="customer_id"
                                 :disabled="$read_only" {{-- :disabled="! Gate::check('update', $qlink) " --}} />

                    <x-jet-input-error for="customer_id" class="mt-2" />
                </div>

                <!-- API Access Key -->
                <div class="col-span-6 sm:col-span-4">
                    <x-jet-label for="api_access_key" value="{{ __('API Access Key') }}" />
                    @if ($read_only)
                        <x-jet-input id="api_access_key" type="password" class="mt-1 block w-full"
                                     wire:model.defer="api_access_key" :disabled="$read_only" />
                    @else
                        @if ($hide_key)
                            <x-jet-input id="api_access_key" type="password" class="mt-1 block w-full"
                                         wire:model.defer="api_access_key" :disabled="$read_only" />
                        @else
                            <x-jet-input id="api_access_key" type="text" class="mt-1 block w-full"
                                         wire:model.defer="api_access_key" :disabled="$read_only" />
                        @endif
                        <a wire:click="hideKey"
                           class="text-blue-600 underline">{{ $hide_key ? __('Reveal') : __('Hide') }}</a>
                    @endif
                    @if (session('alert-status'))
                        <div class="mt-2 text-red-400">
                            {{ session('alert-status') }}
                        </div>
                    @endif
                    <x-jet-input-error for="api_access_key" class="mt-2" />
                </div>
            </x-slot>


            {{-- @if (Gate::check('update', $team)) --}}
            @if (!$read_only)
                <x-slot name="actions">

                    <x-jet-action-message class="mr-3" on="saved">
                        {{ __('Saved.') }}
                    </x-jet-action-message>

                    <x-jet-button>
                        {{ __('Save') }}
                    </x-jet-button>
                </x-slot>
            @endif
        </x-jet-form-section>

        @if (auth()->user()->email == 'kevinlhall72@gmail.com')
            <x-jet-button wire:click="getNewIntConfig()" class="mt-4" type="button">
                {{ __('Get new integrationconfig.json?') }}
            </x-jet-button>
            <x-jet-action-message class="mr-3" on="downloaded">
                {{ __('Integration config loaded.') }}
            </x-jet-action-message>
        @endif
    </div>
</div>
