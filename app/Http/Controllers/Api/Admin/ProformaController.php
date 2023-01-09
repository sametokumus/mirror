<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Address;
use App\Models\Cart;
use App\Models\CartDetail;
use App\Models\City;
use App\Models\CorporateAddresses;
use App\Models\Country;
use App\Models\Coupons;
use App\Models\District;
use App\Models\Order;
use App\Models\OrderProduct;
use App\Models\OrderStatus;
use App\Models\OrderStatusHistory;
use App\Models\Product;
use App\Models\ProductRule;
use App\Models\ProductVariation;
use App\Models\User;
use App\Models\UserTypeDiscount;
use Faker\Provider\Uuid;
use Illuminate\Http\Request;

class ProformaController extends Controller
{
    public function addProformaOrder(Request $request){

        //createCart
        $user_id = $request->user_id;
        $cart_id = Uuid::uuid();
        $added_cart_id = Cart::query()->insertGetId([
            'cart_id' => $cart_id
        ]);

        foreach ($request->products as $product){
            $this->addCart($user_id, $cart_id, $product['product_id'], $product['variation_id'], $product['quantity']);
        }


        //createOrder
        $this->addOrder($user_id, $cart_id);

    }

    private function addCart($user_id, $cart_id, $product_id, $variation_id, $quantity){

        $rule = ProductRule::query()->where('variation_id',$variation_id)->first();
        if (!empty($user_id)) {
            $product = Product::query()->where('id', $product_id)->first();

            $user = User::query()->where('id', $user_id)->where('active', 1)->first();
            $total_user_discount = $user->user_discount;

            $type_discount = UserTypeDiscount::query()->where('user_type_id', $user->user_type)->where('brand_id', $product->brand_id)->where('type_id', $product->type_id)->where('active', 1)->first();
            if (!empty($type_discount)) {
                $total_user_discount = $total_user_discount + $type_discount->discount;
            }

            if ($total_user_discount > 0) {
                if ($rule->discount_rate > 0) {
                    $price = $rule->regular_price - ($rule->regular_price / 100 * ($total_user_discount + $rule->discount_rate));
                } else {
                    $price = $rule->regular_price - ($rule->regular_price / 100 * $total_user_discount);
                }
            } else {
                if ($rule->discount_rate > 0) {
                    $price = $rule->discounted_price;
                } else {
                    $price = $rule->regular_price;
                }
            }
        }else{
            if ($rule->discount_rate > 0) {
                $price = $rule->discounted_price;
            } else {
                $price = $rule->regular_price;
            }
        }
        $cart_detail = CartDetail::query()->where('variation_id',$variation_id)
            ->where('cart_id',$cart_id)
            ->where('product_id',$product_id)
            ->where('active',1)
            ->first();
        if (isset($cart_detail)){
            $quantity = $cart_detail->quantity+$quantity;
            CartDetail::query()->where('cart_id',$cart_id)
                ->where('variation_id',$variation_id)
                ->where('product_id',$product_id)
                ->update([
                    'quantity' => $quantity
                ]);
        }else{
            CartDetail::query()->insert([
                'cart_id' => $cart_id,
                'product_id' => $product_id,
                'variation_id' => $variation_id,
                'quantity' => $quantity,
                'price' => $price,
            ]);
        }

        if (!empty($user_id)){
            Cart::query()->where('cart_id',$cart_id)->update([
                'user_id' => $user_id
            ]);
        }
    }

    private function addOrder($user_id, $cart_id){

        $cart = Cart::query()->where('cart_id', $cart_id)->where('active', 1)->first();


            $order_status = OrderStatus::query()->where('is_default', 1)->first();
            $order_quid = Uuid::uuid();


            $order_id = Order::query()->insertGetId([
                'order_id' => $order_quid,
                'user_id' => $user_id,
                'carrier_id' => 0,
                'cart_id' => $cart_id,
                'status_id' => $order_status->id,
                'shipping_address_id' => 0,
                'billing_address_id' => 0,
                'shipping_address' => "",
                'billing_address' => "",
                'comment' => "",
                'shipping_type' => 0,
                'payment_method' => 3,
                'shipping_price' => 0,
                'subtotal' => 0,
                'total' => 0,
                'is_partial' => 0,
                'is_paid' => 0,
                'coupon_code' => ""
            ]);

            Cart::query()->where('cart_id', $cart_id)->update([
                'user_id' => $user_id,
                'is_order' => 1,
                'active' => 0
            ]);
            $user_discount = User::query()->where('id', $user_id)->first()->user_discount;
            $carts = CartDetail::query()->where('cart_id', $cart_id)->get();
            foreach ($carts as $cart) {
                $product = Product::query()->where('id', $cart->product_id)->first();
                $variation = ProductVariation::query()->where('id', $cart->variation_id)->first();
                $rule = ProductRule::query()->where('variation_id', $variation->id)->first();
                if ($rule->discounted_price == null || $rule->discount_rate == 0){
                    $price = $rule->regular_price - ($rule->regular_price / 100 * $user_discount);
                    $tax = $price / 100 * $rule->tax_rate;
                    $total = ($price + $tax) * $cart->quantity;
                }else{
                    $price = $rule->regular_price - ($rule->regular_price / 100 * ($user_discount + $rule->discount_rate));
                    $tax = $price / 100 * $rule->tax_rate;
                    $total = ($price + $tax) * $cart->quantity;
                }
                OrderProduct::query()->insert([
                    'order_id' => $order_quid,
                    'product_id' => $product->id,
                    'variation_id' => $variation->id,
                    'name' => $product->name,
                    'sku' => $variation->sku,
                    'regular_price' => $rule->regular_price,
                    'regular_tax' => $rule->regular_tax,
                    'discounted_price' => $rule->discounted_price,
                    'discounted_tax' => $rule->discounted_tax,
                    'discount_rate' => $rule->discount_rate,
                    'tax_rate' => $rule->tax_rate,
                    'user_discount' => $user_discount,
                    'quantity' => $cart->quantity,
                    'total' => $total
                ]);
            }

            OrderStatusHistory::query()->insert([
                'order_id' => $order_quid,
                'status_id' => $order_status->id
            ]);

    }
}
