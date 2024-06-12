<?php
namespace App\Helpers;

use App\Models\Product;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Log;

class CartManagement {

    protected $cookieExpiration;

    public function __construct($cookieExpiration = 60 * 24 * 30) {
        $this->cookieExpiration = $cookieExpiration;
    }

    // Add item to cart
    public function addItemToCart($product_id) {
        $cart_items = $this->getCartItemsFromCookie();

        $existing_item = array_search($product_id, array_column($cart_items, 'product_id'));

        if ($existing_item !== false) {
            $this->updateCartItemQuantity($cart_items, $product_id, 1);
        } else {
            $product = Product::find($product_id, ['id', 'name', 'price', 'images']);
            if ($product) {
                $cart_items[] = [
                    'product_id' => $product_id,
                    'name' => $product->name,
                    'image' => $product->images[0] ?? null,
                    'quantity' => 1,
                    'unit_amount' => $product->price,
                    'total_amount' => $product->price
                ];
            } else {
                Log::error("Product with ID {$product_id} not found.");
                return null;
            }
        }

        $this->addCartItemsToCookie($cart_items);
        return count($cart_items);
    }

    // Remove item from cart
    public function removeItemFromCart($product_id) {
        $cart_items = $this->getCartItemsFromCookie();

        foreach ($cart_items as $key => $item) {
            if ($item['product_id'] == $product_id) {
                unset($cart_items[$key]);
                break;
            }
        }

        $cart_items = array_values($cart_items); // Reindex array after removal
        $this->addCartItemsToCookie($cart_items);
    }

    // Add cart items to cookie
    protected function addCartItemsToCookie($cart_items) {
        Cookie::queue('cart_items', json_encode($cart_items), $this->cookieExpiration);
    }

    // Clear cart items from cookie
    public function clearCartItems() {
        Cookie::queue(Cookie::forget('cart_items'));
    }

    // Get all cart items from cookie
    public function getCartItemsFromCookie() {
        $cart_items = json_decode(Cookie::get('cart_items'), true);
        return $cart_items ?: [];
    }

    // Increment item quantity
    public function incrementItemQuantityToCartItem($product_id) {
        $cart_items = $this->getCartItemsFromCookie();
        $this->updateCartItemQuantity($cart_items, $product_id, 1);
        $this->addCartItemsToCookie($cart_items);
    }

    // Decrement item quantity
    public function decrementItemQuantityToCartItem($product_id) {
        $cart_items = $this->getCartItemsFromCookie();
        $this->updateCartItemQuantity($cart_items, $product_id, -1);
        $this->addCartItemsToCookie($cart_items);
    }

    // Calculate grand total
    public function calculateGrandTotal() {
        $cart_items = $this->getCartItemsFromCookie();
        $grand_total = 0;

        foreach ($cart_items as $item) {
            $grand_total += $item['total_amount'];
        }

        return $grand_total;
    }

    // Update cart item quantity
    protected function updateCartItemQuantity(&$cart_items, $product_id, $quantityChange) {
        foreach ($cart_items as $key => $item) {
            if ($item['product_id'] == $product_id) {
                $cart_items[$key]['quantity'] += $quantityChange;
                if ($cart_items[$key]['quantity'] <= 0) {
                    unset($cart_items[$key]);
                } else {
                    $cart_items[$key]['total_amount'] = $cart_items[$key]['quantity'] * $cart_items[$key]['unit_amount'];
                }
                break;
            }
        }
        $cart_items = array_values($cart_items); // Reindex array after modification
    }
}

// Usage example
/*
$cart = new \App\Helpers\CartManagement();
$cart->addItemToCart(1); // Add product with ID 1
$cart->incrementItemQuantityToCartItem(1); // Increment quantity of product with ID 1
$cart->decrementItemQuantityToCartItem(1); // Decrement quantity of product with ID 1
$cart->removeItemFromCart(1); // Remove product with ID 1
print_r($cart->getCartItemsFromCookie());
$cart->clearCartItems(); // Clear all items from the cart
*/
?>
