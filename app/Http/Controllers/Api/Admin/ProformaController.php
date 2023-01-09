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
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Nette\Schema\ValidationException;

class ProformaController extends Controller
{
    public function addProformaOrder(Request $request){

        try {
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
            $order_quid = Uuid::uuid();
            $this->addOrder($user_id, $cart_id, $order_quid, $request->shipping_address_id, $request->billing_address_id, $request->shipping_type);

            return response(['message' => 'Proforma sipariş ekleme işlemi başarılı.', 'status' => 'success', 'object' => ['order_id' => $order_quid, 'cart_id' => $cart_id]]);
        } catch (ValidationException $validationException) {
            return response(['message' => 'Lütfen girdiğiniz bilgileri kontrol ediniz.', 'status' => 'validation-001']);
        } catch (QueryException $queryException) {
            return response(['message' => 'Hatalı sorgu.', 'status' => 'query-001', 'e' => $queryException->getMessage()]);
        } catch (\Throwable $throwable) {
            return response(['message' => 'Hatalı işlem.', 'status' => 'error-001', 'e' => $throwable->getMessage()]);
        }
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

    private function addOrder($user_id, $cart_id, $order_quid, $shipping_address_id, $billing_address_id, $shipping_type){

        $cart = Cart::query()->where('cart_id', $cart_id)->where('active', 1)->first();


            $order_status = OrderStatus::query()->where('is_default', 1)->first();

            $shipping_id = $shipping_address_id;
            $billing_id = $billing_address_id;
            $shipping = Address::query()->where('id', $shipping_id)->first();
            $country = Country::query()->where('id', $shipping->country_id)->first();
            $city = City::query()->where('id', $shipping->city_id)->first();
            $district = District::query()->where('id', $shipping->district_id)->first();

            $shipping_address = $shipping->name . " " . $shipping->surname . " - " . $shipping->address_1 . " " . $shipping->address_2 . " - " . $shipping->postal_code . " - " . $shipping->phone . " - " . $district->name . " / " . $city->name . " / " . $country->name;
            if ($shipping->type == 2){
                $shipping_corporate_address = CorporateAddresses::query()->where('address_id',$shipping_id)->first();
                $shipping_address = $shipping_address." - ".$shipping_corporate_address->tax_number." - ".$shipping_corporate_address->tax_office." - ".$shipping_corporate_address->company_name;
            }


            $billing = Address::query()->where('id', $billing_id)->first();
            $billing_country = Country::query()->where('id', $billing->country_id)->first();
            $billing_city = City::query()->where('id', $billing->city_id)->first();
            $billing_district = District::query()->where('id', $billing->district_id)->first();
            $billing_address = $billing->name . " " . $billing->surname . " - " . $billing->address_1 . " " . $billing->address_2 . " - " . $billing->postal_code . " - " . $billing->phone . " - " . $billing_district->name . " / " . $billing_city->name . " / " . $billing_country->name;

            if ($shipping->type == 2){
                $billing_corporate_address = CorporateAddresses::query()->where('address_id',$billing_id)->first();
                $billing_address = $billing_address." - ".$billing_corporate_address->tax_number." - ".$billing_corporate_address->tax_office." - ".$billing_corporate_address->company_name;
            }

            $order_id = Order::query()->insertGetId([
                'order_id' => $order_quid,
                'user_id' => $user_id,
                'carrier_id' => 0,
                'cart_id' => $cart_id,
                'status_id' => $order_status->id,
                'shipping_address_id' => $shipping_address_id,
                'billing_address_id' => $billing_address_id,
                'shipping_address' => $shipping_address,
                'billing_address' => $billing_address,
                'comment' => "",
                'shipping_type' => $shipping_type,
                'payment_method' => 3,
                'shipping_price' => 0,
                'subtotal' => 0,
                'total' => 0,
                'is_partial' => 0,
                'is_paid' => 0,
                'coupon_code' => "null"
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
