<?php

namespace App\Livewire;

use App\Helpers\CartManagement;
use App\Livewire\Partials\Navbar;
use App\Models\Brand;
use App\Models\Product;
use Livewire\Component;
use App\Models\Category;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Livewire\Attributes\Url;
use Livewire\WithPagination;
use Livewire\Attributes\Title;

#[Title('products - Abecommerce')]

class ProductsPage extends Component
{
    use WithPagination;
    use LivewireAlert;

    #[Url]
    public $selected_categories = [];

    #[Url]
    public $selected_brands = [];

    #[Url]
    public $featured;

    #[Url]
    public $on_sale;

    #[Url]
    public $price_range = 300000;

    #[Url]
    public $sort = 'latest';


    // add product to cart method

    public function addToCart($product_id){
      $total_count = CartManagement::addItemToCart($product_id);

      $this->dispatch('update-cart-count',$total_count)->to( Navbar::class);
      
      $this->alert('success', 'Product added to the cart successfully!', [
        'position' => 'bottom-end',
        'timer' => 3000,
        'toast' => true,
       ]);
    }

 
   
    public function render()
    {

               // Fetch categories and brands
               $categories = Category::where('is_active', 1)->get(['id', 'name', 'slug']);
               $brands = Brand::where('is_active', 1)->get(['id', 'name', 'slug']);
               
               // Start building the products query
               $query = Product::where('is_active', 1);
       
               // Apply category filtering if selected
               if (!empty($this->selected_categories)) {
                   $query->whereIn('category_id', $this->selected_categories);
               }

               if (!empty($this->selected_brands)) {
                $query->whereIn('brand_id', $this->selected_brands);
            }

              if($this->featured){
                $query->where('is_featured',1);
              }

              if($this->on_sale){
                $query->where('on_sale',1);
              }

              if($this->price_range){
                $query->wherebetween('price',[0,$this->price_range]);
              }

              if($this->sort == 'latest'){
                $query->latest();
              }

              if($this->sort == 'price'){
                $query->orderBy('price');
              }


       
       
               // Fetch the paginated products
               $products = $query->paginate(3);
       
        return view('livewire.products-page',[
            'categories'=>$categories,
            'brands'=>Brand::where('is_active',1)->get(['id','name','slug']),
            'products'=>$products,
            
        ]);
    }
}
