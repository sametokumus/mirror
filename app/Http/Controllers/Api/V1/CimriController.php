<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\User;
use App\Models\UserTypeDiscount;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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

}
