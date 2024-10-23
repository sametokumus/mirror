<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\Screen;
use Illuminate\Http\Request;

class QuestionController extends Controller
{
    public function addQuestion(Request $request)
    {
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

        return response()->json(['message' => 'Question and options created successfully', 'question' => $question], 201);
    }

    public function getQuestions()
    {
        $questions = Question::with('options')->get();
        return response()->json($questions);
    }

    // Ekran oluşturma
    public function addScreen(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string',
            'type' => 'required|string|in:info,question_single,question_multiple', // Ekran türü
            'content' => 'nullable|string', // Info ekranı için içerik
        ]);

        $screen = Screen::create($validated);

        return response()->json(['message' => 'Screen created successfully', 'screen' => $screen], 201);
    }

    // Tüm ekranları listeleme
    public function getScreens()
    {
        $screens = Screen::with('questions')->get();
        return response()->json($screens);
    }
}

