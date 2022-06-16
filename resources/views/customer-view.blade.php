<x-guest-layout>

    @if (!$protected)
        @livewire('customer.jacquemus-email')
    @endif

    @if ($protected)
        <livewire:customer.jacquemus-protected />
    @endif

</x-guest-layout>
