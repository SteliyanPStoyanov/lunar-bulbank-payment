<div>
    <form wire:submit.prevent="send">
        <div class="p-4 text-sm text-center text-blue-700 rounded-lg bg-blue-50">
            {{ __('checkout.payment-is-bul-bank') }}
        </div>
        <div class="mt-3">
            <label for="checkbox-1">
                <input type="checkbox" wire:model="terms" name="terms" value="1">
                <span class="">
Съгласен съм с <a target="_blank" href="/pages/terms" title="" class="underline">общите условия</a>
</span>
            </label>
            <br/>
            @if($errors->has('terms'))
                <strong class="text-sm font-medium text-red-600">{{ $errors->first('terms') }}</strong>
            @endif
        </div>
        <button
            class="px-5 py-3 mt-4 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-500"
            type="submit" wire:key="send_submit_btn">
                        <span wire:loading.remove.delay wire:target="send">
                            {{ __('checkout.submit-order') }}
                        </span>
            <span wire:loading.delay wire:target="send">
                            <svg class="w-5 h-5 text-white animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none"
                                 viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                        stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor"
                                      d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                </path>
                            </svg>
                        </span>
        </button>
    </form>
    <div class="hidden">
        {{-- Conditionally render the Borica form and auto-submit it --}}
        @if ($boricaFormHtml)
            <div x-data x-init="document.getElementById('borica3dsRedirectForm').submit()">
                {!! $boricaFormHtml !!}
            </div>
        @endif
    </div>


</div>
