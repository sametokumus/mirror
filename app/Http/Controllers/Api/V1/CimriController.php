<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\CimriProduct;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductRule;
use App\Models\ProductVariation;
use App\Models\ProductVariationGroup;
use App\Models\User;
use App\Models\UserTypeDiscount;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Nette\Schema\ValidationException;

class CimriController extends Controller
{
    public function getCimriProductsByFilter(Request $request)
    {
        try {
            $products = Product::query()
                ->leftJoin('brands', 'brands.id', '=', 'products.brand_id')
                ->leftJoin('product_types', 'product_types.id', '=', 'products.type_id')
                ->leftJoin('product_variations', 'product_variations.id', '=', 'products.featured_variation')
                ->select(DB::raw('(select image from product_images where variation_id = product_variations.id order by id asc limit 1) as image'))
                ->leftJoin('product_rules', 'product_rules.variation_id', '=', 'product_variations.id')
                ->selectRaw('product_rules.*, brands.name as brand_name,product_types.name as type_name, products.*')
                ->where('products.cimri', $request->cimri_status)
                ->where('products.active', 1);

            if ($request->brands != ""){
                $brands = explode(',',$request->brands);
                $products = $products->where(function ($query) use ($products, $brands){
                    foreach ($brands as $brand){
                        $query = $query->orWhere('brands.id', $brand);
                    }
                });
            }
            if ($request->types != ""){
                $types = explode(',',$request->types);
                $products = $products->where(function ($query) use ($products, $types){
                    foreach ($types as $type){
                        $query = $query->orWhere('product_types.id', $type);
                    }
                });
            }

            $products = $products->get();

            return response(['message' => 'İşlem Başarılı.', 'status' => 'success', 'object' => ['products' => $products]]);
        } catch (QueryException $queryException) {
            return response(['message' => 'Hatalı sorgu.', 'status' => 'query-001', 'a' => $queryException->getMessage()]);
        }
    }

    public function addProductCimri(Request $request)
    {
        try {

            $ids = explode(',',$request->ids);

            foreach ($ids as $product_id) {
                Product::query()->where('id', $product_id)->update([
                    'cimri' => 1
                ]);
                $has_cimri = CimriProduct::query()->where('merchantItemId', $product_id)->count();
                if ($has_cimri > 0){
                    CimriProduct::query()->where('merchantItemId', $product_id)->update([
                        'active' => 1
                    ]);
                }else{
                    $product = Product::query()
                        ->leftJoin('brands', 'brands.id', '=', 'products.brand_id')
                        ->leftJoin('product_types', 'product_types.id', '=', 'products.type_id')
                        ->leftJoin('product_variations', 'product_variations.id', '=', 'products.featured_variation')
                        ->select(DB::raw('(select image from product_images where variation_id = product_variations.id order by id asc limit 1) as image'))
                        ->leftJoin('product_rules', 'product_rules.variation_id', '=', 'product_variations.id')
                        ->selectRaw('product_rules.*, brands.name as brand_name,product_types.name as type_name, products.*')
                        ->where('products.id', $product_id)
                        ->first();
                    $product_category = ProductCategory::query()
                        ->leftJoin('categories', 'categories.id', '=', 'product_categories.category_id')
                        ->selectRaw('categories.*')
                        ->selectRaw('product_categories.name as category_name, product_categories.id as category_id')
                        ->where('product_id', $product->id)
                        ->where('product_categories.active', 1)
                        ->where('product_categories.category_id', '!=', 0)
                        ->first();
                    $total_price = $product->regular_price + $product->regular_tax;
                    CimriProduct::query()->insert([
                        'merchantItemId' => $product_id,
                        'merchantItemCategoryId' => $product_category->category_id,
                        'merchantItemCategoryName' => $product_category->category_name,
                        'brand' => $product->brand_name,
                        'itemTitle' => $product->name,
                        'itemUrl' => "https://kablocu.wimco.com.tr/urun-detay/".$product_id."/".$product->featured_variation,
                        'itemImageUrl' => $product->image,
                        'price3T' => $total_price,
                        'price6T' => $total_price,
                        'priceEft' => $total_price,
                        'pricePlusTax' => $total_price,
                    ]);
                }
            }
            return response(['message' => 'Cimri listesi güncelleme işlemi başarılı.', 'status' => 'success']);
        } catch (ValidationException $validationException) {
            return response(['message' => 'Lütfen girdiğiniz bilgileri kontrol ediniz.', 'status' => 'validation-001']);
        } catch (QueryException $queryException) {
            return response(['message' => 'Hatalı sorgu.', 'status' => 'query-001', 'a' => $queryException->getMessage()]);
        } catch (\Throwable $throwable) {
            return response(['message' => 'Hatalı işlem.', 'status' => 'error-001', 'er' => $throwable->getMessage()]);
        }
    }

    public function deleteProductCimri(Request $request)
    {
        try {

            $ids = explode(',',$request->ids);

            foreach ($ids as $product_id) {
                Product::query()->where('id', $product_id)->update([
                    'cimri' => 0
                ]);
                CimriProduct::query()->where('merchantItemId', $product_id)->update([
                    'active' => 0
                ]);
            }
            return response(['message' => 'Cimri listesi güncelleme işlemi başarılı.', 'status' => 'success']);
        } catch (ValidationException $validationException) {
            return response(['message' => 'Lütfen girdiğiniz bilgileri kontrol ediniz.', 'status' => 'validation-001']);
        } catch (QueryException $queryException) {
            return response(['message' => 'Hatalı sorgu.', 'status' => 'query-001', 'a' => $queryException->getMessage()]);
        } catch (\Throwable $throwable) {
            return response(['message' => 'Hatalı işlem.', 'status' => 'error-001', 'er' => $throwable->getMessage()]);
        }
    }
}
