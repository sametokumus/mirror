<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Slider;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;

class SliderController extends Controller
{
    public function getSliders()
    {
        try {
            $sliders = Slider::query()
                ->leftJoin('user_types', 'user_types.id', '=', 'sliders.user_type')
                ->selectRaw('sliders.*, user_types.name as user_type_name')
                ->where('active',1)->get();
            return response(['message' => 'İşlem Başarılı.', 'status' => 'success', 'object' => ['sliders' => $sliders]]);
        } catch (QueryException $queryException) {
            return response(['message' => 'Hatalı sorgu.', 'status' => 'query-001']);
        }
    }

    public function getSliderById($slider_id){
        try {
            $sliders = Slider::query()
                ->leftJoin('user_types', 'user_types.id', '=', 'sliders.user_type')
                ->selectRaw('sliders.*, user_types.name as user_type_name')
                ->where('id',$slider_id)->first();
            return response(['message' => 'İşlem Başarılı.', 'status' => 'success', 'object' => ['sliders' => $sliders]]);
        } catch (QueryException $queryException) {
            return response(['message' => 'Hatalı sorgu.', 'status' => 'query-001']);
        }
    }
}
