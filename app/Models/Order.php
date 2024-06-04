<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'grand_total',
        'payment_status',
        'payment_method',
        'status',
        'currency',
        'shipping_amount',
        'shipping_method',
        'notes',
    ];


    public function user(){
        return $this->belongsTo(User::class);
    }

    public function items(){
        return $this->hasMany(OrderItem::class);
    }

    public function address(){

        return $this->hasOne(Address::class);

    }

     // Method to calculate grand total
     public function calculateGrandTotal()
     {
         return $this->items->sum('total_amount');
     }
 
     // Override save method to ensure grand total is saved
  
}
