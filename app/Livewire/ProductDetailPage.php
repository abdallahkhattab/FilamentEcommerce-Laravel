<?php

namespace App\Livewire;

use App\Models\Product;
use Livewire\Component;
use Livewire\Attributes\Title;
use App\Helpers\CartManagement;
use App\Livewire\Partials\Navbar;
use Jantinnerezo\LivewireAlert\LivewireAlert;

#[Title('Product Detail - ABEcommerce')]
class ProductDetailPage extends Component
{
    use LivewireAlert;
    public $slug;
    public $quantity = 1;


    public function mount($slug)
    {
        $this->slug = $slug;
    }

    public function increaseQty()
    {
        $this->quantity++;
    }

    public function addToCart($product_id){
        $total_count = CartManagement::addItemToCartWithQty($product_id,$this->quantity);
  
        $this->dispatch('update-cart-count',$total_count)->to( Navbar::class);
        
        $this->alert('success', 'Product added to the cart successfully!', [
          'position' => 'bottom-end',
          'timer' => 3000,
          'toast' => true,
         ]);
      }
      
    public function decreaseQty()
    {
        $this->quantity = max(1, $this->quantity - 1); // Ensure quantity doesn't go below 1
    }

    public function render()
    {
        return view('livewire.product-detail-page', [
            'product' => Product::where('slug', $this->slug)->firstOrFail(),
        ]);
    }
}
