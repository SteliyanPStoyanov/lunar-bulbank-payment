<?php

namespace Lunar\BulBank\Http\Livewire;

use Livewire\Component;
use Lunar\Models\Cart;

class Payment extends Component
{

    public Cart $cart;


    /**
     * {@inheritDoc}
     */
    public function render()
    {
        return view('bulbank::livewire.payment');
    }
}
