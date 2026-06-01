<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Article;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ArticleController extends Controller
{
    /**
     * Ambil semua artikel (untuk user & admin)
     */
    public function index(): JsonResponse
    {
        $articles = Article::where('is_published', true)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $articles,
        ]);
    }

    /**
     * Ambil semua artikel termasuk unpublished (admin only)
     */
    public function all(): JsonResponse
    {
        $articles = Article::orderBy('created_at', 'desc')->get();

        return response()->json([
            'success' => true,
            'data' => $articles,
        ]);
    }

    /**
     * Simpan artikel baru (admin only)
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'emoji' => 'nullable|string|max:10',
            'title' => 'required|string|max:255',
            'excerpt' => 'required|string',
            'content' => 'nullable|string',
            'is_published' => 'nullable|boolean',
        ]);

        $article = Article::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Artikel berhasil ditambahkan',
            'data' => $article,
        ], 201);
    }

    /**
     * Ambil detail artikel
     */
    public function show(string $id): JsonResponse
    {
        $article = Article::findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $article,
        ]);
    }

    /**
     * Update artikel (admin only)
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $article = Article::findOrFail($id);

        $validated = $request->validate([
            'emoji' => 'nullable|string|max:10',
            'title' => 'required|string|max:255',
            'excerpt' => 'required|string',
            'content' => 'nullable|string',
            'is_published' => 'nullable|boolean',
        ]);

        $article->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Artikel berhasil diperbarui',
            'data' => $article,
        ]);
    }

    /**
     * Hapus artikel (admin only)
     */
    public function destroy(string $id): JsonResponse
    {
        $article = Article::findOrFail($id);
        $article->delete();

        return response()->json([
            'success' => true,
            'message' => 'Artikel berhasil dihapus',
        ]);
    }
}
