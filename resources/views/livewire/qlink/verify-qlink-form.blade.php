<div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8">

    <section class="gradient w-full mx-auto text-center pt-6 pb-12">
        @if (!$confirmed && !session('alert-status'))
            <h2 class="w-full my-2 text-5xl font-black leading-tight text-center text-blue-600/75">
                Your 80% Off Spree is ready!
            </h2>
            <div class="w-full mb-4">
                <div class="h-1 mx-auto bg-white w-1/6 opacity-25 my-0 py-0 rounded-t"></div>
            </div>

            <h3 class="my-4 text-3xl text-gray-400 font-extrabold">
                Enter your user id/email to confirm your discount and start your 2 hour spree.
            </h3>

            <!-- Visitor ID -->
            <div class="w-full">

                <x-jet-input id="visitor_id" type="text" class="mt-1 block w-1/2 mx-auto"
                             wire:model.defer="visitor_id" />
                <x-jet-label for="visitor_id"
                             value="{{ __('Enter the user id/email that was used to claim your special access link.') }}" />
                <x-jet-input-error for="visitor_id" class="mt-2" />
            </div>

            <button wire:click="confirm()"
                    class="mx-auto lg:mx-0 hover:underline bg-white text-gray-800 font-bold rounded my-6 py-4 px-8 shadow-lg">
                Confirm!
            </button>
        @endif
        @if ($confirmed)
            <h3 class="my-4 text-3xl text-gray-400 font-extrabold">
                Your shopping spree has started.
            </h3>
        @endif

        @if (session('alert-status'))
            <div class="mt-2 text-red-600">
                <h3 class="my-4 text-3xl text-gray-400 font-extrabold">
                    {{ session('alert-status') }}
                </h3>
                <a href="/invite-only-with-auth"
                   class="mx-auto lg:mx-0 hover:underline bg-white text-gray-800 font-bold rounded my-6 py-4 px-8 shadow-lg">
                    Go to Login
                </a>
            </div>
        @endif

    </section>
</div>
