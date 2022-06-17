<x-guest-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('VIP Gallery') }}
        </h2>
    </x-slot>

    <div>
        @if ($guest)
            <div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8">
                @livewire('qlink.invite-only-view', ['show_auth' => false])
            </div>
        @else
            <div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8">
                @livewire('qlink.invite-only-view')
            </div>
        @endif
    </div>
</x-guest-layout>
