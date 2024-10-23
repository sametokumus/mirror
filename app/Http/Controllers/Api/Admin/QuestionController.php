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
                'screen' => 'required|string',
                'question_text' => 'required|string',
                'type' => 'required|string', // Cevap türü
                'group' => 'nullable|string', // Soru grubu
                'options' => 'nullable|array', // Seçenekler
            ]);

            // Soru oluştur
            $question = Question::create([
                'screen' => $validated['screen'],
                'question_text' => $validated['question_text'],
                'type' => $validated['type'],
                'group' => $validated['group'],
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

    // Ekran oluşturma
    public function addScreen(Request $request)
    {
        try {
            $validated = $request->validate([
                'title' => 'required|string',
                'type' => 'required|string|in:info,question_single,question_multiple', // Ekran türü
                'content' => 'nullable|string', // Info ekranı için içerik
            ]);

            $screen = Screen::create($validated);

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
}

