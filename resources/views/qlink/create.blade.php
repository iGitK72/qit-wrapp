<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Create Qlink') }}
        </h2>
    </x-slot>
    <div>
        <div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8">
            @livewire('qlink.config-qlink-form', ['read_only' => true])
        </div>
    </div>

    <div>
        <div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8">
            @livewire('qlink.create-qlink-form', ['read_only' => true])
        </div>
    </div>
    {{-- <div>
        <div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8">
             @livewire('qlink.upload-qlink-form',['read_only'=>true]) 
        </div>
    </div> --}}
</x-app-layout>
