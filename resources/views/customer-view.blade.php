<x-guest-layout>

    @if (!$protected)
        @livewire('customer.jacquemus-email', ['use_test_wr' => $use_test_wr])
    @endif

    @if ($protected)
        <livewire:customer.jacquemus-protected />
    @endif

</x-guest-layout>
