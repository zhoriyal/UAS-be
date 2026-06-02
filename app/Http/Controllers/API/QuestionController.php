<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Question;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class QuestionController extends Controller
{
    /**
     * User: Ambil pertanyaan milik user
     */
    public function index(Request $request): JsonResponse
    {
        $query = Question::orderBy('created_at', 'desc');

        // Jika ada user_id, filter milik user tertentu
        if ($request->has('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        $questions = $query->get()->map(function ($q) {
            return [
                'id' => $q->id,
                'user_id' => $q->user_id,
                'user_name' => $q->user_name,
                'question' => $q->question,
                'answer' => $q->answer,
                'status' => $q->status,
                'created_at' => $q->created_at->format('Y-m-d H:i'),
                'answered_at' => $q->updated_at ? $q->updated_at->format('Y-m-d H:i') : null,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $questions,
        ]);
    }

    /**
     * User: Ajukan pertanyaan baru
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'user_id' => 'required|string',
            'user_name' => 'required|string',
            'question' => 'required|string|min:10',
        ]);

        $question = Question::create([
            'user_id' => $request->user_id,
            'user_name' => $request->user_name,
            'question' => $request->question,
            'status' => 'pending',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Pertanyaan berhasil dikirim. Admin akan segera menjawab.',
            'data' => $question,
        ], 201);
    }

    /**
     * Admin: Ambil semua pertanyaan
     */
    public function all(): JsonResponse
    {
        $questions = Question::orderBy('created_at', 'desc')
            ->get()
            ->map(function ($q) {
                return [
                    'id' => $q->id,
                    'user_id' => $q->user_id,
                    'user_name' => $q->user_name,
                    'question' => $q->question,
                    'answer' => $q->answer,
                    'status' => $q->status,
                    'created_at' => $q->created_at->format('Y-m-d H:i'),
                    'answered_at' => $q->updated_at ? $q->updated_at->format('Y-m-d H:i') : null,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $questions,
        ]);
    }

    /**
     * Admin: Jawab pertanyaan
     */
    public function answer(Request $request, string $id): JsonResponse
    {
        $request->validate([
            'answer' => 'required|string|min:5',
        ]);

        $question = Question::findOrFail($id);
        $question->update([
            'answer' => $request->answer,
            'status' => 'answered',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Jawaban berhasil dikirim',
            'data' => $question,
        ]);
    }

    /**
     * Hapus pertanyaan
     */
    public function destroy(string $id): JsonResponse
    {
        $question = Question::findOrFail($id);
        $question->delete();

        return response()->json([
            'success' => true,
            'message' => 'Pertanyaan berhasil dihapus',
        ]);
    }
}
