<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Document;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;

class DocumentController extends Controller
{
    /**
     * Ambil semua dokumen (admin) atau dokumen user tertentu
     */
    public function index(Request $request): JsonResponse
    {
        $query = Document::query();

        // Filter by user_id jika ada
        if ($request->has('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        $documents = $query->orderBy('created_at', 'desc')->get();

        // Group by user untuk admin view
        if ($request->has('group_by_user') && $request->group_by_user) {
            $grouped = $documents->groupBy('user_id')->map(function ($docs, $userId) {
                $first = $docs->first();
                return [
                    'userId' => $userId,
                    'userName' => $first->user_name,
                    'email' => $first->user_email,
                    'documents' => $docs->map(function ($doc) {
                        return [
                            'id' => $doc->id,
                            'name' => $doc->file_name,
                            'type' => $doc->file_type,
                            'size' => $doc->file_size,
                            'date' => $doc->created_at->format('d M Y'),
                            'status' => $doc->status,
                        ];
                    }),
                ];
            })->values();

            return response()->json([
                'success' => true,
                'data' => $grouped,
            ]);
        }

        return response()->json([
            'success' => true,
            'data' => $documents,
        ]);
    }

    /**
     * Upload dokumen baru
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'file' => 'required|file|max:10240', // Max 10MB
            'user_id' => 'required|string',
            'user_name' => 'required|string',
            'user_email' => 'required|email',
        ]);

        $file = $request->file('file');
        $fileName = $file->getClientOriginalName();
        $fileType = strtoupper(pathinfo($fileName, PATHINFO_EXTENSION));
        $fileSize = $this->formatFileSize($file->getSize());

        // Simpan file ke storage
        $path = $file->storeAs(
            "documents/{$request->user_id}",
            $fileName,
            'public'
        );

        $document = Document::create([
            'user_id' => $request->user_id,
            'user_name' => $request->user_name,
            'user_email' => $request->user_email,
            'file_name' => $fileName,
            'file_type' => $fileType,
            'file_size' => $fileSize,
            'file_path' => $path,
            'status' => 'Tersimpan',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Dokumen berhasil diunggah',
            'data' => $document,
        ], 201);
    }

    /**
     * Hapus dokumen
     */
    public function destroy(string $id): JsonResponse
    {
        $document = Document::findOrFail($id);

        // Hapus file dari storage
        if (Storage::disk('public')->exists($document->file_path)) {
            Storage::disk('public')->delete($document->file_path);
        }

        $document->delete();

        return response()->json([
            'success' => true,
            'message' => 'Dokumen berhasil dihapus',
        ]);
    }

    /**
     * Download dokumen
     */
    public function download(string $id): JsonResponse
    {
        $document = Document::findOrFail($id);

        if (!Storage::disk('public')->exists($document->file_path)) {
            return response()->json([
                'success' => false,
                'message' => 'File tidak ditemukan',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'url' => Storage::disk('public')->url($document->file_path),
                'name' => $document->file_name,
            ],
        ]);
    }

    /**
     * Format ukuran file
     */
    private function formatFileSize(int $bytes): string
    {
        if ($bytes >= 1048576) {
            return round($bytes / 1048576, 1) . ' MB';
        } elseif ($bytes >= 1024) {
            return round($bytes / 1024) . ' KB';
        }
        return $bytes . ' B';
    }
}
