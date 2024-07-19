<div>
    <form wire:submit.prevent="send" id="checkoutFormBulbank">

        <div class="flex p-2">
            <input class="custom-radio" type="radio"
                   wire:model="terms" name="terms" value="1"
                   id="terms"/>
            <label class="flex items-center justify-between text-sm font-medium cursor-pointer ml-2 "
                   for="terms">
                        <span>
                            Съгласен съм с<a target="_blank" href="/pages/terms" title="" class="underline">общите условия</a>
                        </span>
            </label>
        </div>
        @if($errors->has('terms'))
            <strong class="text-sm font-medium text-red-600">{{ $errors->first('terms') }}</strong>
        @endif
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

