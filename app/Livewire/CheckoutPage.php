<?php

namespace App\Livewire;

use App\Helpers\CartManagement;
use Livewire\Component;
use Livewire\Attributes\Title;

class CheckoutPage extends Component
{
    #[Title('checkout')]
    public function render()
    {

        $cart_items = CartManagement::getCartItemsFromCookie();
        $grand_total = CartManagement::calculateGrandTotal($cart_items);
        return view('livewire.checkout-page',[
            'cart_items'=>$cart_items,
            'grand_total'=>$grand_total
        ]);
    }
}
