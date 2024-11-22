<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Answer;
use App\Models\Order;
use App\Models\OrderRefund;
use App\Models\Screen;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class QuestionController extends Controller
{

    public function addAnswer(Request $request, $screen_id){
        try {
            $user_id = Auth::user()->id;

            Answer::query()->insertGetId([
                'user_id' => $user_id,
                'question_id' => $request->question_id,
                'option_id' => $request->option_id,
                'answer' => $request->answer
            ]);

            $screen = Screen::where('id', '>', $screen_id)
                ->with('questions.options')
                ->orderBy('id')
                ->first();

            if (!$screen){
                return response(['message' => 'İşlem Başarılı.', 'status' => 'screen_not_found']);
            }
            return response(['message' => 'İşlem Başarılı.', 'status' => 'success', 'object' => ['screen' => $screen]]);
        } catch (QueryException $queryException){
            return  response(['message' => 'Hatalı sorgu.','status' => 'query-001','err' => $queryException->getMessage()]);
        }
    }

    public function getScreen($screen_id)
    {
        try {
            $screen = Screen::where('id', '=', $screen_id)
                ->with('questions.options')
                ->orderBy('id')
                ->first();

            if (!$screen){
                return response(['message' => 'İşlem Başarılı.', 'status' => 'screen_not_found']);
            }

            return response(['message' => 'İşlem Başarılı.', 'status' => 'success', 'object' => ['screen' => $screen]]);
        } catch (QueryException $queryException) {
            return response(['message' => 'Hatalı sorgu.', 'status' => 'query-001']);
        } catch (Exception $exception) {
            return response(['message' => 'Hatalı sorgu.', 'status' => 'query-001']);
        }
    }
}
