<?php

namespace App\Livewire;

use App\Models\Order;
use App\Models\Address;
use Livewire\Component;
use Livewire\Attributes\Title;
use App\Helpers\CartManagement;
use Stripe\Checkout\Session;
use Stripe\Stripe;

class CheckoutPage extends Component
{
    #[Title('checkout')]

    public $first_name;
    public $last_name;
    public $phone;
    public $street_address;
    public $city;
    public $state;
    public $zip_code;
    public $payment_method;

    public function placeOrder()
    {
        $this->validate([
            'first_name' => 'required',
            'last_name' => 'required',
            'phone' => 'required',
            'street_address' => 'required',
            'city' => 'required',
            'state' => 'required',
            'zip_code' => 'required',
            'payment_method' => 'required',
        ]);
    
        $cart_items = CartManagement::getCartItemsFromCookie();
        $line_items = [];
    
        foreach ($cart_items as $item) {
            $line_items[] = [
                'price_data' => [
                    'currency' => 'ILS', // Assuming currency is ILS, adjust as needed
                    'unit_amount' => $item['unit_amount'] * 100,
                    'product_data' => [
                        'name' => $item['name'],
                    ],
                ],
                'quantity' => $item['quantity'],
            ];
        }
    
        $order = new Order();
        $order->user_id = auth()->user()->id;
        //$order->first_name = $this->first_name;
        //$order->last_name = $this->last_name;
      //  $order->phone = $this->phone;
        $order->payment_method = $this->payment_method;
        $order->payment_status = 'pending';
        $order->currency = 'ILS';
        $order->shipping_amount = 0;
        $order->shipping_method = 'none';
        $order->notes = 'Order Placed by ' . auth()->user()->name;
      //  $order->line_items = json_encode($line_items);
       // $order->total_amount = CartManagement::calculateGrandTotal($cart_items);
        $order->save(); // Save the order first to get the ID
    
        $address = new Address();
        $address->street_address = $this->street_address;
        $address->city = $this->city;
        $address->state = $this->state;
        $address->zip_code = $this->zip_code;
        $address->order_id = $order->id; // Set the order ID after saving the order
    
        if ($this->payment_method == 'stripe') {
            Stripe::setApiKey(env('STRIPE_SECRET'));
    
            $session = Session::create([
                'payment_method_types' => ['card'],
                'customer_email' => auth()->user()->email,
                'line_items' => $line_items,
                'mode' => 'payment',
                'success_url' => route('success') . '?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url' => route('cancel'),
            ]);
    
            // Save the Stripe session ID to the order
            $order->stripe_session_id = $session->id;
            $order->save();
    
            // Save the address after setting the order ID
            $address->save();
    
            // Clear the cart
            CartManagement::clearCartItems();
    
            // Redirect to Stripe checkout
            return redirect($session->url);
        } else {
            // Save the address for non-Stripe payments
            $address->save();
    
            // Clear the cart
            CartManagement::clearCartItems();
    
            // Redirect or show a success message
            session()->flash('message', 'Order placed successfully!');
            return redirect()->route('success'); // Assuming a route for successful order
        }
    }
    

    public function render()
    {
        $cart_items = CartManagement::getCartItemsFromCookie();
        $grand_total = CartManagement::calculateGrandTotal($cart_items);

        return view('livewire.checkout-page', [
            'cart_items' => $cart_items,
            'grand_total' => $grand_total,
        ]);
    }
}
