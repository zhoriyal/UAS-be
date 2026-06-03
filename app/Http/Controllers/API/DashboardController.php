<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Deadline;
use App\Models\Finance;
use App\Models\Report;
use App\Models\Summary;
use App\Models\Document;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Smalot\PdfParser\Parser as PdfParser;

class DashboardController extends Controller
{
    // ─── Deadlines (Tenggat Waktu) ───

    public function deadlines(Request $request): JsonResponse
    {
        $query = Deadline::where('user_id', $request->user_id)
            ->orderBy('due_date', 'asc');

        $deadlines = $query->get()->map(function ($d) {
            $daysLeft = now()->diffInDays($d->due_date, false);
            return [
                'id' => $d->id,
                'label' => $d->label,
                'due_date' => $d->due_date->format('Y-m-d'),
                'days_left' => $daysLeft >= 0 ? (int) $daysLeft : 0,
                'status' => $d->status,
                'is_urgent' => $d->is_urgent,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $deadlines,
        ]);
    }

    public function storeDeadline(Request $request): JsonResponse
    {
        $request->validate([
            'user_id' => 'required|string',
            'label' => 'required|string|max:255',
            'due_date' => 'required|date',
            'is_urgent' => 'boolean',
        ]);

        $deadline = Deadline::create([
            'user_id' => $request->user_id,
            'label' => $request->label,
            'due_date' => $request->due_date,
            'is_urgent' => $request->is_urgent ?? false,
            'status' => 'pending',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Tenggat waktu berhasil ditambahkan',
            'data' => $deadline,
        ], 201);
    }

    public function updateDeadline(Request $request, string $id): JsonResponse
    {
        $deadline = Deadline::findOrFail($id);

        $deadline->update($request->only(['label', 'due_date', 'status', 'is_urgent']));

        return response()->json([
            'success' => true,
            'message' => 'Tenggat waktu berhasil diperbarui',
            'data' => $deadline,
        ]);
    }

    public function destroyDeadline(string $id): JsonResponse
    {
        $deadline = Deadline::findOrFail($id);
        $deadline->delete();

        return response()->json([
            'success' => true,
            'message' => 'Tenggat waktu berhasil dihapus',
        ]);
    }

    // ─── Finances (Ringkasan Keuangan) ───

    public function finances(Request $request): JsonResponse
    {
        $query = Finance::where('user_id', $request->user_id)
            ->orderBy('date', 'desc');

        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        $finances = $query->get()->map(function ($f) {
            return [
                'id' => $f->id,
                'type' => $f->type,
                'description' => $f->description,
                'amount' => (float) $f->amount,
                'date' => $f->date->format('Y-m-d'),
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $finances,
        ]);
    }

    public function summary(Request $request): JsonResponse
    {
        $userId = $request->user_id;

        $totalPenerimaan = Finance::where('user_id', $userId)
            ->where('type', 'penerimaan')
            ->sum('amount');

        $totalBiaya = Finance::where('user_id', $userId)
            ->where('type', 'biaya')
            ->sum('amount');

        return response()->json([
            'success' => true,
            'data' => [
                'total_penerimaan' => (float) $totalPenerimaan,
                'total_biaya' => (float) $totalBiaya,
                'sisa_kas' => (float) ($totalPenerimaan - $totalBiaya),
            ],
        ]);
    }

    public function storeFinance(Request $request): JsonResponse
    {
        $request->validate([
            'user_id' => 'required|string',
            'type' => 'required|string|in:penerimaan,biaya',
            'description' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'date' => 'required|date',
        ]);

        $finance = Finance::create([
            'user_id' => $request->user_id,
            'type' => $request->type,
            'description' => $request->description,
            'amount' => $request->amount,
            'date' => $request->date,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Transaksi berhasil ditambahkan',
            'data' => $finance,
        ], 201);
    }

    public function destroyFinance(string $id): JsonResponse
    {
        $finance = Finance::findOrFail($id);
        $finance->delete();

        return response()->json([
            'success' => true,
            'message' => 'Transaksi berhasil dihapus',
        ]);
    }

    // ─── Reports (Laporan Terakhir) ───

    public function reports(Request $request): JsonResponse
    {
        $query = Report::where('user_id', $request->user_id)
            ->orderBy('date', 'desc');

        $reports = $query->get()->map(function ($r) {
            return [
                'id' => $r->id,
                'type' => $r->type,
                'date' => $r->date->format('Y-m-d'),
                'status' => $r->status,
                'file_path' => $r->file_path,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $reports,
        ]);
    }

    public function storeReport(Request $request): JsonResponse
    {
        $request->validate([
            'user_id' => 'required|string',
            'type' => 'required|string|max:255',
            'date' => 'required|date',
            'status' => 'string|in:draft,diajukan,selesai',
        ]);

        $report = Report::create([
            'user_id' => $request->user_id,
            'type' => $request->type,
            'date' => $request->date,
            'status' => $request->status ?? 'draft',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Laporan berhasil ditambahkan',
            'data' => $report,
        ], 201);
    }

    public function updateReport(Request $request, string $id): JsonResponse
    {
        $report = Report::findOrFail($id);

        $report->update($request->only(['type', 'date', 'status', 'file_path']));

        return response()->json([
            'success' => true,
            'message' => 'Laporan berhasil diperbarui',
            'data' => $report,
        ]);
    }

    public function destroyReport(string $id): JsonResponse
    {
        $report = Report::findOrFail($id);
        $report->delete();

        return response()->json([
            'success' => true,
            'message' => 'Laporan berhasil dihapus',
        ]);
    }

    // ─── Summaries (Rangkum Dokumen) ───

    public function summaries(Request $request): JsonResponse
    {
        $summaries = Summary::where('user_id', $request->user_id)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($s) {
                return [
                    'id' => $s->id,
                    'file_name' => $s->file_name,
                    'status' => $s->status,
                    'summary' => $s->summary,
                    'created_at' => $s->created_at->format('Y-m-d H:i'),
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $summaries,
        ]);
    }

    /**
     * Admin: Ambil semua rangkuman dari semua user
     */
    public function allSummaries(): JsonResponse
    {
        $summaries = Summary::orderBy('created_at', 'desc')
            ->get()
            ->map(function ($s) {
                return [
                    'id' => $s->id,
                    'user_id' => $s->user_id,
                    'file_name' => $s->file_name,
                    'status' => $s->status,
                    'summary' => $s->summary,
                    'created_at' => $s->created_at->format('Y-m-d H:i'),
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $summaries,
        ]);
    }

    public function summarize(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'user_id' => 'required|string',
                'file' => 'required|file|mimes:pdf|max:10240',
            ]);
        } catch (\Exception $e) {
            \Log::error('Validasi gagal: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal: ' . $e->getMessage(),
            ], 422);
        }

        try {
            $file = $request->file('file');
            $fileName = $file->getClientOriginalName();

            \Log::info("Mulai rangkum: {$fileName} oleh user {$request->user_id}");

            // Simpan file
            $path = $file->storeAs(
                "summaries/{$request->user_id}",
                $fileName,
                'public'
            );

            // Buat record dengan status processing
            $summary = Summary::create([
                'user_id' => $request->user_id,
                'file_name' => $fileName,
                'file_path' => $path,
                'status' => 'processing',
            ]);

            // Proses rangkuman dengan Gemini AI
            $summaryText = $this->generateSummary($path, $fileName);

            $summary->update([
                'status' => 'done',
                'summary' => $summaryText,
            ]);

            \Log::info("Rangkuman selesai: {$fileName}");

            return response()->json([
                'success' => true,
                'message' => 'Dokumen berhasil dirangkum',
                'data' => [
                    'id' => $summary->id,
                    'file_name' => $summary->file_name,
                    'status' => $summary->status,
                    'summary' => $summary->summary,
                    'created_at' => $summary->created_at->format('Y-m-d H:i'),
                ],
            ], 201);
        } catch (\Exception $e) {
            \Log::error('Gagal rangkum dokumen: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal memproses dokumen: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Rangkum teks langsung (tanpa PDF)
     */
    public function summarizeText(Request $request): JsonResponse
    {
        $request->validate([
            'user_id' => 'required|string',
            'text' => 'required|string|min:50',
        ]);

        try {
            $summary = Summary::create([
                'user_id' => $request->user_id,
                'file_name' => 'Teks Langsung',
                'file_path' => '',
                'status' => 'processing',
            ]);

            $apiKey = config('services.gemini.api_key');

            if (empty($apiKey)) {
                $result = $this->generateFallbackSummary('Teks Langsung');
            } else {
                $text = substr($request->text, 0, 25000);

                $prompt = "Anda adalah asisten pajak untuk UMKM Indonesia. "
                    . "Rangkum teks berikut menjadi poin-poin penting yang mudah dipahami oleh pelaku usaha kecil. "
                    . "Gunakan bahasa sehari-hari yang sederhana. "
                    . "Format output: nomor poin, diikuti penjelasan singkat dan jelas. "
                    . "Maksimal 8 poin. "
                    . "Di akhir, tambahkan satu baris catatan penting jika ada kewajiban atau batas waktu.\n\n"
                    . "Teks:\n{$text}";

                $response = Http::timeout(60)->withoutVerifying()->post(
                    "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key={$apiKey}",
                    [
                        'contents' => [['parts' => [['text' => $prompt]]]],
                        'generationConfig' => ['temperature' => 0.3, 'maxOutputTokens' => 4096],
                    ]
                );

                if ($response->successful()) {
                    $data = $response->json();
                    $aiResult = $data['candidates'][0]['content']['parts'][0]['text'] ?? null;
                    $result = $aiResult
                        ? "📋 Rangkuman Teks\n\nBerikut adalah poin-poin penting:\n\n" . trim($aiResult)
                            . "\n\n⚠️ Catatan: Rangkuman ini dibuat otomatis oleh AI."
                        : $this->generateFallbackSummary('Teks Langsung');
                } else {
                    \Log::error('Gemini API error: ' . $response->body());
                    $result = $this->generateFallbackSummary('Teks Langsung');
                }
            }

            $summary->update(['status' => 'done', 'summary' => $result]);

            return response()->json([
                'success' => true,
                'message' => 'Teks berhasil dirangkum',
                'data' => [
                    'id' => $summary->id,
                    'file_name' => $summary->file_name,
                    'status' => $summary->status,
                    'summary' => $summary->summary,
                    'created_at' => $summary->created_at->format('Y-m-d H:i'),
                ],
            ], 201);
        } catch (\Exception $e) {
            \Log::error('Gagal rangkum teks: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal memproses teks: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function summaryStatus(string $id): JsonResponse
    {
        $summary = Summary::findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $summary->id,
                'file_name' => $summary->file_name,
                'status' => $summary->status,
                'summary' => $summary->summary,
                'created_at' => $summary->created_at->format('Y-m-d H:i'),
            ],
        ]);
    }

    public function destroySummary(string $id): JsonResponse
    {
        $summary = Summary::findOrFail($id);

        if (Storage::disk('public')->exists($summary->file_path)) {
            Storage::disk('public')->delete($summary->file_path);
        }

        $summary->delete();

        return response()->json([
            'success' => true,
            'message' => 'Rangkuman berhasil dihapus',
        ]);
    }

    /**
     * Simpan rangkuman sebagai PDF ke dokumen user
     */
    public function saveSummaryAsPdf(Request $request): JsonResponse
    {
        $request->validate([
            'summary_id' => 'required|integer',
            'user_id' => 'required|string',
            'user_name' => 'required|string',
            'user_email' => 'required|email',
        ]);

        $summary = Summary::findOrFail($request->summary_id);

        if ($summary->status !== 'done' || empty($summary->summary)) {
            return response()->json([
                'success' => false,
                'message' => 'Rangkuman belum selesai atau kosong',
            ], 400);
        }

        // Generate PDF dari teks rangkuman
        $fileName = 'Rangkuman_' . str_replace(' ', '_', $summary->file_name) . '_' . date('Y-m-d') . '.pdf';

        // Konversi teks ke HTML untuk PDF
        $htmlContent = nl2br(e($summary->summary));
        $html = '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="utf-8">
            <style>
                body { font-family: sans-serif; font-size: 12px; line-height: 1.6; padding: 20px; }
                h1 { font-size: 16px; color: #333; border-bottom: 2px solid #6d28d9; padding-bottom: 8px; }
                .meta { font-size: 11px; color: #666; margin-bottom: 16px; }
                .content { font-size: 12px; color: #333; }
                .footer { font-size: 10px; color: #999; margin-top: 24px; border-top: 1px solid #ddd; padding-top: 8px; }
            </style>
        </head>
        <body>
            <h1>📋 Rangkuman Dokumen Pajak</h1>
            <div class="meta">
                <p><strong>Sumber:</strong> ' . e($summary->file_name) . '</p>
                <p><strong>Tanggal:</strong> ' . date('d F Y H:i') . '</p>
                <p><strong>Dihasilkan oleh:</strong> AI Pajak Pintar UMKM</p>
            </div>
            <div class="content">' . $htmlContent . '</div>
            <div class="footer">
                <p>Dokumen ini dibuat secara otomatis oleh sistem Pajak Pintar UMKM.</p>
            </div>
        </body>
        </html>';

        // Generate PDF
        $pdf = Pdf::loadHTML($html);
        $pdfContent = $pdf->output();

        // Simpan ke storage
        $safeName = preg_replace('/[^a-zA-Z0-9._-]/', '_', $fileName);
        $userId = $request->input('user_id');
        $path = "documents/{$userId}/{$safeName}";
        Storage::disk('public')->put($path, $pdfContent);

        // Simpan record ke tabel documents
        $document = Document::create([
            'user_id' => $userId,
            'user_name' => $request->input('user_name'),
            'user_email' => $request->input('user_email'),
            'file_name' => $safeName,
            'file_type' => 'PDF',
            'file_size' => $this->formatFileSize(strlen($pdfContent)),
            'file_path' => $path,
            'status' => 'Tersimpan',
            'category' => 'rangkuman_ai',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Rangkuman berhasil disimpan sebagai PDF',
            'data' => $document,
        ], 201);
    }

    /**
     * Generate rangkuman dari dokumen menggunakan Google Gemini AI.
     */
    private function generateSummary(string $filePath, string $fileName): string
    {
        $apiKey = config('services.gemini.api_key');

        // Jika tidak ada API key, fallback ke simulasi
        if (empty($apiKey)) {
            return $this->generateFallbackSummary($fileName);
        }

        try {
            // Extract text dari PDF
            $fullPath = Storage::disk('public')->path($filePath);
            $parser = new PdfParser();
            $pdf = $parser->parseFile($fullPath);
            $text = $pdf->getText();

            // Batasi text (Gemini punya limit ~30k token untuk input)
            if (strlen($text) > 25000) {
                $text = substr($text, 0, 25000) . "\n\n[Dokumen dipotong karena terlalu panjang]";
            }

            // Kirim ke Gemini API
            $prompt = "Anda adalah asisten pajak untuk UMKM Indonesia. "
                . "Rangkum dokumen peraturan pajak berikut menjadi poin-poin penting yang mudah dipahami oleh pelaku usaha kecil. "
                . "Gunakan bahasa sehari-hari yang sederhana. "
                . "Format output: nomor poin, diikuti penjelasan singkat dan jelas. "
                . "Maksimal 8 poin. "
                . "Di akhir, tambahkan satu baris catatan penting jika ada kewajiban atau batas waktu yang harus diperhatikan.\n\n"
                . "Dokumen: {$fileName}\n\n"
                . "Isi dokumen:\n{$text}";

            $response = Http::timeout(60)->withoutVerifying()->post(
                "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key={$apiKey}",
                [
                    'contents' => [
                        [
                            'parts' => [
                                ['text' => $prompt],
                            ],
                        ],
                    ],
                    'generationConfig' => [
                        'temperature' => 0.3,
                        'maxOutputTokens' => 4096,
                    ],
                ]
            );

            if ($response->successful()) {
                $data = $response->json();
                $result = $data['candidates'][0]['content']['parts'][0]['text'] ?? null;

                if ($result) {
                    return "📋 Rangkuman Dokumen: {$fileName}\n\n"
                        . "Berikut adalah poin-poin penting dari dokumen yang Anda unggah:\n\n"
                        . trim($result)
                        . "\n\n⚠️ Catatan: Rangkuman ini dibuat otomatis oleh AI. "
                        . "Untuk pemahaman lebih lanjut, konsultasikan dengan konsultan pajak atau kunjungi situs resmi DJP.";
                }
            }

            // Jika API gagal, fallback
            \Log::error('Gemini API error: ' . $response->body());
            return $this->generateFallbackSummary($fileName);

        } catch (\Exception $e) {
            \Log::error('Gagal memproses rangkuman: ' . $e->getMessage());
            return $this->generateFallbackSummary($fileName);
        }
    }

    /**
     * Fallback jika Gemini API tidak tersedia.
     */
    private function generateFallbackSummary(string $fileName): string
    {
        $points = [
            "Dokumen ini mengatur tentang ketentuan perpajakan yang berlaku untuk UMKM di Indonesia.",
            "PPh Final UMKM dikenakan tarif 0,5% dari omzet bruto yang diterima atau diperoleh wajib pajak.",
            "Batas pembayaran PPh Final adalah tanggal 15 bulan berikutnya setelah masa pajak berakhir.",
            "Pelaporan SPT Tahunan harus dilakukan paling lambat 31 Maret tahun berikutnya.",
            "Wajib pajak UMKM yang memenuhi kriteria tertentu dapat memperoleh fasilitas pengurangan pajak.",
        ];

        $result = "📋 Rangkuman Dokumen: {$fileName}\n\n";
        $result .= "Berikut adalah poin-poin penting dari dokumen yang Anda unggah:\n\n";

        foreach ($points as $i => $point) {
            $result .= ($i + 1) . ". {$point}\n";
        }

        $result .= "\n⚠️ Catatan: Rangkuman ini dibuat secara otomatis (mode demo). "
            . "Untuk rangkuman AI yang lebih akurat, silakan konfigurasi GEMINI_API_KEY di file .env.";

        return $result;
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
