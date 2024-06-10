<?php

namespace App\Livewire;

use App\Models\Brand;
use App\Models\Product;
use Livewire\Component;
use App\Models\Category;
use Livewire\Attributes\Title;
use Livewire\WithPagination;

#[Title('products - Abecommerce')]

class ProductsPage extends Component
{
    use WithPagination;
   
    public function render()
    {

       
        $categories = Category::where('is_active',1)->get(['id','name','slug']);
        $brands = Brand::where('is_active',1)->get();
        $products = Product::where('is_active',1)->paginate(1);


        return view('livewire.products-page',[
            'categories'=>$categories,
            'brands'=>Brand::where('is_active',1)->get(['id','name','slug']),
            'products'=>$products,
            
        ]);
    }
}
