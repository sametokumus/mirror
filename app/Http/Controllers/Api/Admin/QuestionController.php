<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\Screen;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;

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

            // Eğer seçenekler varsa, bu seçenekleri de ekle
            if (!empty($validated['options'])) {
                foreach ($validated['options'] as $option) {
                    QuestionOption::create([
                        'question_id' => $question->id,
                        'option_text' => $option,
                    ]);
                }
            }

            return response(['message' => 'İşlem Başarılı.', 'status' => 'success', 'object' => ['question' => $question]]);
        } catch (QueryException $queryException) {
            return response(['message' => 'Hatalı sorgu.', 'status' => 'query-001']);
        }
    }

    public function getQuestions()
    {
        try {
            $questions = Question::with('options')->get();
            return response(['message' => 'İşlem Başarılı.', 'status' => 'success', 'object' => ['questions' => $questions]]);
        } catch (QueryException $queryException) {
            return response(['message' => 'Hatalı sorgu.', 'status' => 'query-001']);
        }
    }

    public function getQuestionsByScreenId($screen_id)
    {
        try {
            $questions = Question::with('options')->where('screen_id', $screen_id)->where('active', 1)->get();
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

            $screen = Screen::create($validated);

            return response(['message' => 'İşlem Başarılı.', 'status' => 'success', 'object' => ['screen' => $screen]]);
        } catch (QueryException $queryException) {
            return response(['message' => 'Hatalı sorgu.', 'status' => 'query-001']);
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

    // Tüm ekranları listeleme
    public function getScreens()
    {
        try {
            $screens = Screen::with('questions')->get();
            return response(['message' => 'İşlem Başarılı.', 'status' => 'success', 'object' => ['screens' => $screens]]);
        } catch (QueryException $queryException) {
            return response(['message' => 'Hatalı sorgu.', 'status' => 'query-001']);
        }
    }
    public function getScreenById($screen_id)
    {
        try {
            $screen = Screen::where('id', $screen_id)->with('questions')->with('question_options')->first();
            return response(['message' => 'İşlem Başarılı.', 'status' => 'success', 'object' => ['screen' => $screen]]);
        } catch (QueryException $queryException) {
            return response(['message' => 'Hatalı sorgu.', 'status' => 'query-001']);
        }
    }

    //Filter Questions
    public function getFilterQuestions()
    {
        try {
            $questions = Question::with('options')->get();
            return response(['message' => 'İşlem Başarılı.', 'status' => 'success', 'object' => ['questions' => $questions]]);
        } catch (QueryException $queryException) {
            return response(['message' => 'Hatalı sorgu.', 'status' => 'query-001']);
        }
    }
}

