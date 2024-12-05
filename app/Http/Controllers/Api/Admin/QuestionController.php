<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Answer;
use App\Models\Cart;
use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\Screen;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class QuestionController extends Controller
{
    public function addQuestion(Request $request)
    {
        try {
            $validated = $request->validate([
                'group' => 'nullable|string', // Soru grubu
                'type' => 'required|string', // Cevap türü
                'question_text' => 'required|string',
                'screen_id' => 'required|integer',
                'options' => 'nullable|array', // Seçenekler
                'is_you' => 'nullable|integer', // Seçenekler
                'mirror' => 'nullable|integer', // Seçenekler
            ]);

            // Soru oluştur
            $question = Question::create([
                'screen_id' => $validated['screen_id'],
                'question_text' => $validated['question_text'],
                'type' => $validated['type'],
                'group' => $validated['group'],
                'is_you' => $validated['is_you'],
                'mirror' => $validated['mirror'],
            ]);

            if ($validated['is_you'] == 1){
                Question::where('id', $validated['mirror'])->update([
                    'is_you' => 0,
                    'mirror' => $question->id
                ]);
            }

            // Eğer seçenekler varsa, bu seçenekleri de ekle
            if (!empty($validated['options'])) {
                foreach ($validated['options'] as $option) {
                    QuestionOption::create([
                        'question_id' => $question->id,
                        'option_text' => $option,
                    ]);
                }
            }

            return response(['message' => 'İşlem Başarılı.', 'status' => 'success']);
        } catch (QueryException $queryException) {
            return response(['message' => 'Hatalı sorgu.', 'status' => 'query-001', 'e' => $queryException->getMessage()]);
        }
    }

    public function updateQuestion(Request $request, $question_id)
    {
        try {
            $validated = $request->validate([
                'group' => 'nullable|string',
                'type' => 'required|string',
                'question_text' => 'required|string',
                'screen_id' => 'required|integer',
                'options' => 'nullable|array',
                'is_you' => 'nullable|integer',
                'mirror' => 'nullable|integer',
            ]);

            // Güncellenecek soruyu bul
            $question = Question::findOrFail($question_id);

            // Soru detaylarını güncelle
            $question->update([
                'screen_id' => $validated['screen_id'],
                'question_text' => $validated['question_text'],
                'type' => $validated['type'],
                'group' => $validated['group'],
                'is_you' => $validated['is_you'],
                'mirror' => $validated['mirror'],
            ]);

            if ($validated['is_you'] == 1){
                Question::where('id', $validated['mirror'])->update([
                    'is_you' => 0,
                    'mirror' => $question_id
                ]);
            }

            if (!empty($validated['options'])) {
                // Mevcut seçenekleri al
                $existingOptions = QuestionOption::where('question_id', $question_id)->get()->keyBy('option_text');

                // İşlenen mevcut seçeneklerin listesini tut
                $processedOptions = [];

                foreach ($validated['options'] as $optionText) {
                    if ($existingOptions->has($optionText)) {
                        // Mevcut seçenek varsa, aktif hale getir ve güncelle
                        $existingOption = $existingOptions[$optionText];
                        $existingOption->update(['active' => 1]); // 'active' alanını güncelle (aktif)
                    } else {
                        // Yeni bir seçenek ekle
                        QuestionOption::create([
                            'question_id' => $question_id,
                            'option_text' => $optionText,
                            'active' => 1, // Yeni seçenek aktif olarak eklenir
                        ]);
                    }

                    // İşlenen seçenekleri kaydet
                    $processedOptions[] = $optionText;
                }

                // Silinmiş olan seçeneklerin aktif durumunu 0 yap
                QuestionOption::where('question_id', $question_id)
                    ->whereNotIn('option_text', $processedOptions)
                    ->update(['active' => 0]);
            }


            return response([
                'message' => 'Soru başarıyla güncellendi.',
                'status' => 'success'
            ]);

        } catch (ModelNotFoundException $e) {
            return response([
                'message' => 'Soru bulunamadı.',
                'status' => 'error',
                'code' => 'not_found',
                'e' => $e->getMessage()
            ], 404);
        } catch (QueryException $queryException) {
            return response([
                'message' => 'Hatalı sorgu.',
                'status' => 'error',
                'code' => 'query-001',
                'e' => $queryException->getMessage()
            ], 500);
        } catch (\Exception $e) {
            return response([
                'message' => 'Bir hata oluştu.',
                'status' => 'error',
                'e' => $e->getMessage()
            ], 500);
        }
    }


    public function getQuestions()
    {
        try {
            $questions = Question::where('active', 1)
            ->with([
                'options' => function ($query) {
                    $query->where('active', 1);
                }
            ])
                ->get();
            return response(['message' => 'İşlem Başarılı.', 'status' => 'success', 'object' => ['questions' => $questions]]);
        } catch (QueryException $queryException) {
            return response(['message' => 'Hatalı sorgu.', 'status' => 'query-001']);
        }
    }

    public function getQuestionById($question_id)
    {
        try {
            $questions = Question::where('id', $question_id)
                ->where('active', 1)
                ->with([
                    'options' => function ($query) {
                        $query->where('active', 1);
                    }
                ])
                ->first();
            return response(['message' => 'İşlem Başarılı.', 'status' => 'success', 'object' => ['questions' => $questions]]);
        } catch (QueryException $queryException) {
            return response(['message' => 'Hatalı sorgu.', 'status' => 'query-001']);
        }
    }

    public function getQuestionsByScreenId($screen_id)
    {
        try {
            $questions = Question::where('screen_id', $screen_id)
                ->where('active', 1)
                ->with([
                    'options' => function ($query) {
                        $query->where('active', 1);
                    }
                ])
                ->get();
            return response(['message' => 'İşlem Başarılı.', 'status' => 'success', 'object' => ['questions' => $questions]]);
        } catch (QueryException $queryException) {
            return response(['message' => 'Hatalı sorgu.', 'status' => 'query-001']);
        }
    }

    // Ekran oluşturma
    public function addScreen(Request $request)
    {
        try {
            $validated = $request->validate([
                'title' => 'required|string',
                'type' => 'required|string|in:info,question_single,question_multiple',
                'content' => 'nullable|string',
                'is_required' => 'required|integer',
            ]);

            $maxSequence = Screen::max('sequence') ?? 9999;
            $screen = Screen::create(array_merge($validated, [
                'sequence' => $maxSequence + 1,
            ]));

            return response([
                'message' => 'İşlem Başarılı.',
                'status' => 'success',
                'object' => ['screen' => $screen]
            ]);
        } catch (QueryException $queryException) {
            return response([
                'message' => 'Hatalı sorgu.',
                'status' => 'query-001',
                'e' => $queryException->getMessage()
            ], 500);
        }
    }

    public function updateScreen(Request $request)
    {
        try {
            $validated = $request->validate([
                'title' => 'required|string',
                'type' => 'required|string|in:info,question_single,question_multiple',
                'content' => 'nullable|string',
                'is_required' => 'required|integer',
            ]);

            $screen = Screen::where('id', $request->screen_id)->update($validated);

            return response(['message' => 'İşlem Başarılı.', 'status' => 'success', 'object' => ['screen' => $screen]]);
        } catch (QueryException $queryException) {
            return response(['message' => 'Hatalı sorgu.', 'status' => 'query-001']);
        }
    }

    public function updateScreenSequence(Request $request)
    {
        try {
            $sortedIDs = json_decode($request->input('sortedIDs'), true);

//            foreach ($sortedIDs as $index => $id) {
//                Screen::where('id', $id)->update(['sequence' => $index + 1]);
//            }

            return response()->json([
                'status' => 'success',
                'message' => 'Sıralama başarıyla güncellendi.',
                'sda' => $request->input('sortedIDs'),
                'all_data' => $request->all(), // Gelen tüm veriyi kontrol et
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Sıralama güncellenirken bir hata oluştu.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Tüm ekranları listeleme
    public function getScreens()
    {
        try {
            $screens = Screen::where('active', 1)
                ->with([
                    'questions' => function ($query) {
                        $query->where('active', 1)->with([
                            'options' => function ($query) {
                                $query->where('active', 1);
                            }
                        ]);
                    }
                ])
                ->get();
            return response(['message' => 'İşlem Başarılı.', 'status' => 'success', 'object' => ['screens' => $screens]]);
        } catch (QueryException $queryException) {
            return response(['message' => 'Hatalı sorgu.', 'status' => 'query-001']);
        }
    }
    public function getScreenById($screen_id)
    {
        try {
            $screen = Screen::where('id', $screen_id)
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
            return response(['message' => 'İşlem Başarılı.', 'status' => 'success', 'object' => ['screen' => $screen]]);
        } catch (QueryException $queryException) {
            return response(['message' => 'Hatalı sorgu.', 'status' => 'query-001']);
        }
    }
    public function getDeleteScreen($screen_id)
    {
        try {
            $screen = Screen::where('id', $screen_id)->update([
                'active' => 0
            ]);
            return response(['message' => 'İşlem Başarılı.', 'status' => 'success', 'object' => ['screen' => $screen]]);
        } catch (QueryException $queryException) {
            return response(['message' => 'Hatalı sorgu.', 'status' => 'query-001']);
        }
    }
    public function getNextScreen($last_screen_id)
    {
        try {
            $screen = Screen::where('id', '>', $last_screen_id)
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
                ->orderBy('sequence')
                ->first();

            return response(['message' => 'İşlem Başarılı.', 'status' => 'success', 'object' => ['screen' => $screen]]);
        } catch (QueryException $queryException) {
            return response(['message' => 'Hatalı sorgu.', 'status' => 'query-001']);
        } catch (Exception $exception) {
            return response(['message' => 'Hatalı sorgu.', 'status' => 'query-001']);
        }
    }

    //Filter Questions
    public function getFilterQuestions()
    {
        try {
            $questions = Question::where('active', 1)
                ->with([
                    'options' => function ($query) {
                        $query->where('active', 1);
                    }
                ])
                ->get();
            return response(['message' => 'İşlem Başarılı.', 'status' => 'success', 'object' => ['questions' => $questions]]);
        } catch (QueryException $queryException) {
            return response(['message' => 'Hatalı sorgu.', 'status' => 'query-001']);
        }
    }
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
                ->orderBy('sequence')
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
                ->orderBy('sequence')
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
