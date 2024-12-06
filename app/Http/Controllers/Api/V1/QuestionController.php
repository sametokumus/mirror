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

            if ($request->skip_this != 1) {
                // Gelen option_ids dizisini kontrol ediyoruz
                if (is_array($request->option_ids)) {
                    foreach ($request->option_ids as $option_id) {
                        Answer::query()->insertGetId([
                            'user_id' => $user_id,
                            'question_id' => $request->question_id,
                            'option_id' => $option_id,
                            'answer' => $request->answer
                        ]);
                    }
                }
            }

            $screen = Screen::query()->where('id', $screen_id)->first();

            $new_screen = Screen::where('sequence', '>', $screen->sequence)
                ->with([
                    'questions' => function ($query) {
                        $query->where('active', 1)->with([
                            'options' => function ($query) {
                                $query->where('active', 1);
                            }
                        ]);
                    }
                ])
                ->where('active', 1)
                ->orderBy('id')
                ->first();

            if (!$new_screen){
                return response(['message' => 'İşlem Başarılı.', 'status' => 'screen_not_found']);
            }
            return response(['message' => 'İşlem Başarılı.', 'status' => 'success', 'object' => ['screen' => $new_screen]]);
        } catch (QueryException $queryException){
            return  response(['message' => 'Hatalı sorgu.','status' => 'query-001','err' => $queryException->getMessage()]);
        }
    }

    public function getScreen($screen_id)
    {
        try {
            $screen = Screen::where('id', '=', $screen_id)
                ->where('active', 1)
                ->with([
                    'questions' => function ($query) {
                        $query->where('active', 1)->with([
                            'options' => function ($query) {
                                $query->where('active', 1);
                            }
                        ]);
                    }
                ])
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

    public function getScreenFirst()
    {
        try {
            $screen = Screen::query()
                ->where('active', 1)
                ->with([
                    'questions' => function ($query) {
                        $query->where('active', 1)->with([
                            'options' => function ($query) {
                                $query->where('active', 1);
                            }
                        ]);
                    }
                ])
                ->orderBy('id')
                ->first();

            if (!$screen){
                return response(['message' => 'İşlem Başarılı.', 'status' => 'screen_not_found']);
            }

            $screen_count = Screen::query()
                ->where('active', 1)->count();

            return response(['message' => 'İşlem Başarılı.', 'status' => 'success', 'object' => ['screen' => $screen, 'screen_count' => $screen_count]]);
        } catch (QueryException $queryException) {
            return response(['message' => 'Hatalı sorgu.', 'status' => 'query-001']);
        } catch (Exception $exception) {
            return response(['message' => 'Hatalı sorgu.', 'status' => 'query-001']);
        }
    }
}
