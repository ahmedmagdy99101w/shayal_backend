<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Question;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class QuestionController extends Controller
{
    public function index(Request $request)
    {
        $questions = Question::paginate($request->get('per_page', 50));
        return response()->json(['data'=> $questions], 200);

    }

    public function store(Request $request)
    {
        $question = Question::create($request->all());
        return response()->json(['data'=> $question], 200);

    }

    public function show(Question $question)
    {
        if ($question) {
            return response()->json($question, Response::HTTP_OK);
        } else {
            return response()->json(['message' => 'Question not found'], Response::HTTP_NOT_FOUND);
        }
    }

    public function update(Request $request, Question $question)
    {
        $question->update($request->all());
        return response()->json(['data'=> $question], 200);

    }

    public function destroy(Question $question)
    {
        if (!$question) {
            return response()->json(['message' => 'Question not found'], 404);
        }

        $question->delete();

        return response()->json(['message' => 'Question deleted successfully'], 204);
    }
}
