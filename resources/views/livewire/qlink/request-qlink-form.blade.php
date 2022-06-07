    <div>
        @if (!$params_valid)
            <div class="col-span-6 sm:col-span-4">
                @foreach ($status_messages as $message)
                    <p class="font-semibold text-lg">{{ $message }}</p>
                @endforeach
            </div>
        @else
            <x-jet-form-section submit="next">
                <x-slot name="title">
                    {{ __('Enter Visitor User ID') }}
                </x-slot>

                <x-slot name="description">
                    {{ __('Start by entering your unique user ID or email address to claim your access link.') }}
                </x-slot>

                <x-slot name="form">
                    <!-- Visitor ID -->
                    <div class="col-span-6 sm:col-span-4">
                        <x-jet-label for="visitor_id" value="{{ __('Visitor User ID') }}" />

                        <x-jet-input id="visitor_id" type="text" class="mt-1 block w-full" wire:model.defer="visitor_id"
                                     :disabled="$show_claim_link" />

                        <x-jet-input-error for="visitor_id" class="mt-2" />
                    </div>
                </x-slot>

                <x-slot name="actions">
                    <x-jet-action-message class="mr-3" on="validated">
                        {{ __('Visitor User ID accepted.') }}
                    </x-jet-action-message>

                    @if (!$show_claim_link)
                        <x-jet-button>
                            {{ __('Next') }}
                        </x-jet-button>
                    @else
                        <a href="#" wire:click="resetForm"
                           class="mt-2 inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-900 focus:outline-none focus:border-gray-900 focus:ring focus:ring-gray-300 disabled:opacity-25 transition">
                            Reset
                        </a>
                    @endif
                </x-slot>

            </x-jet-form-section>

            @if ($show_claim_link)
                <x-jet-form-section submit="unlock">
                    <x-slot name="title">
                        {{ __('Lock & Key') }}
                    </x-slot>

                    <x-slot name="description">
                        {{ __('Use your key to reveal the special access link.') }}
                    </x-slot>

                    <x-slot name="form">
                        <!-- Lock -->
                        <div class="col-span-6 sm:col-span-4">
                            <x-jet-label for="lock" value="{{ __('Lock') }}" />

                            <textarea class="form-control
                                              block
                                              w-full
                                              px-3
                                              py-1.5
                                              text-base
                                              font-normal
                                              text-gray-700
                                              bg-white bg-clip-padding
                                              border border-solid border-gray-300
                                              rounded
                                              transition
                                              ease-in-out
                                              m-0
                                              focus:text-gray-700 focus:bg-white focus:border-blue-600 focus:outline-none"
          wire:model.defer="lock" id="lock" rows="3" placeholder="" disabled>
                                            </textarea>

                            <x-jet-input-error for="lock" class="mt-2" />
                        </div>
                        <!-- Key -->
                        <div class="col-span-6 sm:col-span-4">
                            <x-jet-label for="key" value="{!! $key_message !!}" />

                            <x-jet-input id="key" type="text" class="mt-1 block w-full" wire:model.defer="key"
                                         :disabled=false />

                            <x-jet-input-error for="key" class="mt-2" />
                        </div>
                    </x-slot>

                    <x-slot name="actions">
                        <x-jet-action-message class="mr-3" on="unlocked">
                            {{ __('Unlocked.') }}
                        </x-jet-action-message>

                        <x-jet-button>
                            {{ __('Unlock') }}
                        </x-jet-button>
                    </x-slot>
                </x-jet-form-section>
            @endif

            @if ($show_copy_link)
                <x-jet-form-section submit="copy">
                    <x-slot name="title">
                        {{ __('Special Access Link') }}
                    </x-slot>

                    <x-slot name="description">
                        {{ __('Copy your unique, special access link and paste it into a new window in your browser.') }}
                    </x-slot>

                    <x-slot name="form">
                        <!-- Visitor ID -->
                        <div class="col-span-6 sm:col-span-4">
                            <x-jet-label for="iowr_access_link" value="{{ __('Special Access Link') }}" />

                            <x-jet-input id="iowr_access_link" type="text" class="mt-1 block w-full"
                                         wire:model.defer="iowr_access_link" :disabled=false />

                            <x-jet-input-error for="iowr_access_link" class="mt-2" />
                        </div>
                    </x-slot>

                    <x-slot name="actions">
                        <x-jet-action-message class="mr-3" on="copied">
                            {{ __('Copied.') }}
                        </x-jet-action-message>

                        <button value="copy" onclick="copyToClipboard('iowr_access_link')"
                                class="mt-2 inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-900 focus:outline-none focus:border-gray-900 focus:ring focus:ring-gray-300 disabled:opacity-25 transition">
                            Copy
                        </button>
                    </x-slot>
                </x-jet-form-section>
            @endif
        @endif
    </div>
    @push('scripts')
        <script>
            function copyToClipboard(id) {
                document.getElementById(id).select();
                document.execCommand('copy');
            }
        </script>
    @endpush
