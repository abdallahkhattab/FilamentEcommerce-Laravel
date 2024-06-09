<?php

namespace App\Livewire;

use App\Models\Brand;
use App\Models\Category;
use Livewire\Component;
use Livewire\Attributes\Title;


#[Title('Home Page - Abecommerce')]
class HomePage extends Component
{
    public function render()
    {
       // $brands = Brand::all();

       // if is active value = 1
       //display it
       $brands = Brand::where('is_active',1)->get();
       $categories = Category::where('is_active',1)->get();

      // dd($brands);
        return view('livewire.home-page',[
            'brands' => $brands,
            'categories' => $categories
        ]);
    }
}
