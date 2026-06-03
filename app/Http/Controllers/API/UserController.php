<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\Document;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Http;

class UserController extends Controller
{
    private string $supabaseUrl;
    private string $supabaseServiceKey;

    public function __construct()
    {
        $this->supabaseUrl = config('services.supabase.url');
        $this->supabaseServiceKey = config('services.supabase.service_key');
    }

    /**
     * Dashboard stats
     */
    public function stats(): JsonResponse
    {
        // Hitung total user dari Supabase
        $totalUsers = 0;
        $recentUsers = [];
        $todayUsers = 0;

        try {
            $response = Http::withHeaders([
                'Authorization' => "Bearer {$this->supabaseServiceKey}",
                'apikey' => $this->supabaseServiceKey,
            ])->withoutVerifying()->get("{$this->supabaseUrl}/auth/v1/admin/users", [
                'page' => 1,
                'per_page' => 100,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $totalUsers = $data['total'] ?? count($data['users'] ?? []);

                // Ambil 5 user terbaru
                $recentUsers = collect($data['users'] ?? [])
                    ->sortByDesc('created_at')
                    ->take(5)
                    ->map(function ($user) {
                        return [
                            'name' => $user['user_metadata']['name'] ?? $user['user_metadata']['full_name'] ?? '-',
                            'email' => $user['email'] ?? '-',
                            'joined' => isset($user['created_at'])
                                ? date('d M Y', strtotime($user['created_at']))
                                : '-',
                        ];
                    })
                    ->values();

                // Hitung user aktif hari ini
                $todayUsers = collect($data['users'] ?? [])->filter(function ($user) {
                    return date('Y-m-d', strtotime($user['created_at'])) === date('Y-m-d');
                })->count();
            }
        } catch (\Exception $e) {
            // Jika gagal, gunakan default 0
            \Log::error('Gagal mengambil data user dari Supabase: ' . $e->getMessage());
        }

        // Hitung total artikel
        $totalArticles = Article::count();

        // Hitung total dokumen
        $totalDocuments = Document::count();

        return response()->json([
            'success' => true,
            'data' => [
                'total_users' => $totalUsers,
                'active_today' => $todayUsers,
                'total_articles' => $totalArticles,
                'total_documents' => $totalDocuments,
                'recent_users' => $recentUsers,
            ],
        ]);
    }

    /**
     * Ambil semua user dari Supabase (admin only)
     */
    public function index(): JsonResponse
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => "Bearer {$this->supabaseServiceKey}",
                'apikey' => $this->supabaseServiceKey,
            ])->withoutVerifying()->get("{$this->supabaseUrl}/auth/v1/admin/users", [
                'page' => 1,
                'per_page' => 100,
            ]);

            if ($response->failed()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal mengambil data user',
                ], 500);
            }

            $data = $response->json();
            $users = collect($data['users'] ?? [])->map(function ($user) {
                return [
                    'id' => substr($user['id'], 0, 8),
                    'full_id' => $user['id'],
                    'name' => $user['user_metadata']['name'] ?? $user['user_metadata']['full_name'] ?? '-',
                    'email' => $user['email'] ?? '-',
                    'role' => $user['user_metadata']['role'] ?? 'user',
                    'usaha' => $user['user_metadata']['nama_usaha'] ?? '-',
                    'joined' => isset($user['created_at'])
                        ? date('d M Y', strtotime($user['created_at']))
                        : '-',
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $users,
                'total' => $data['total'] ?? count($users),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data user: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update role user (admin only)
     */
    public function updateRole(Request $request): JsonResponse
    {
        $request->validate([
            'user_id' => 'required|string',
            'role' => 'required|string|in:admin,user',
        ]);

        try {
            // Ambil user metadata saat ini
            $getUserResponse = Http::withHeaders([
                'Authorization' => "Bearer {$this->supabaseServiceKey}",
                'apikey' => $this->supabaseServiceKey,
            ])->withoutVerifying()->get("{$this->supabaseUrl}/auth/v1/admin/users/{$request->user_id}");

            if ($getUserResponse->failed()) {
                return response()->json([
                    'success' => false,
                    'message' => 'User tidak ditemukan',
                ], 404);
            }

            $currentUser = $getUserResponse->json();
            $currentMeta = $currentUser['user_metadata'] ?? [];
            $currentMeta['role'] = $request->role;

            // Update user metadata
            $updateResponse = Http::withHeaders([
                'Authorization' => "Bearer {$this->supabaseServiceKey}",
                'apikey' => $this->supabaseServiceKey,
            ])->withoutVerifying()->put("{$this->supabaseUrl}/auth/v1/admin/users/{$request->user_id}", [
                'user_metadata' => $currentMeta,
            ]);

            if ($updateResponse->failed()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal mengubah role user',
                ], 500);
            }

            return response()->json([
                'success' => true,
                'message' => "Role user berhasil diubah menjadi {$request->role}",
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengubah role: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Hapus user dari Supabase (admin atau user sendiri)
     */
    public function destroy(Request $request): JsonResponse
    {
        $request->validate([
            'user_id' => 'required|string',
        ]);

        try {
            $response = Http::withHeaders([
                'Authorization' => "Bearer {$this->supabaseServiceKey}",
                'apikey' => $this->supabaseServiceKey,
            ])->withoutVerifying()->delete("{$this->supabaseUrl}/auth/v1/admin/users/{$request->user_id}");

            if ($response->failed()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal menghapus user',
                ], 500);
            }

            // Hapus juga dokumen dan data terkait
            Document::where('user_id', $request->user_id)->delete();

            return response()->json([
                'success' => true,
                'message' => 'User berhasil dihapus',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus user: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Cek apakah user masih ada di Supabase
     */
    public function checkUser(Request $request): JsonResponse
    {
        $request->validate([
            'user_id' => 'required|string',
        ]);

        try {
            $response = Http::withHeaders([
                'Authorization' => "Bearer {$this->supabaseServiceKey}",
                'apikey' => $this->supabaseServiceKey,
            ])->withoutVerifying()->get("{$this->supabaseUrl}/auth/v1/admin/users/{$request->user_id}");

            if ($response->successful()) {
                $data = $response->json();
                // Supabase mengembalikan data user langsung (ada 'id' field)
                if (isset($data['id'])) {
                    return response()->json([
                        'success' => true,
                        'exists' => true,
                    ]);
                }
            }

            // Jika 404 atau tidak ada data = user tidak ditemukan
            return response()->json([
                'success' => true,
                'exists' => false,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'exists' => false,
                'message' => 'Gagal mengecek user: ' . $e->getMessage(),
            ], 500);
        }
    }
}
