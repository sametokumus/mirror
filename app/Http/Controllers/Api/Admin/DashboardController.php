<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function getDashboard(Request $request)
    {
        try {

            $total_user = User::query()->where('active', 1)->count();
            $total_order = Order::query()->where('is_paid', 1)->count();
            $total_cost_all = Order::query()
                ->leftJoin('payments', 'payments.order_id', '=', 'orders.order_id')
                ->where('orders.is_paid', 1)
                ->toSql();
            return response(['message' => 'Hatalı sorgu.', 'status' => 'query-001', 'sql' => $total_cost_all]);
            $total_cost = 0;
            foreach ($total_cost_all as $tc){
                $total_cost += $tc->paid_price;
            }
            $total_product = Product::query()->where('active', 1)->count();

            return response(['message' => 'İşlem Başarılı.', 'status' => 'success', 'object' => ['total_user' => $total_user, 'total_order' => $total_order, 'total_cost' => $total_cost, 'total_product' => $total_product]]);
        } catch (QueryException $queryException) {
            return response(['message' => 'Hatalı sorgu.', 'status' => 'query-001', 'a' => $queryException->getMessage()]);
        }
    }
}
