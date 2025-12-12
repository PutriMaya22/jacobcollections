<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\BarangMasuk;
use App\Models\BarangKeluar;
use App\Models\Barang;
use Carbon\Carbon;

class NotificationController extends Controller
{
    
    public function index(): JsonResponse
    {
        $notifications = $this->generateNotifications();
        
        return response()->json([
            'notifications' => $notifications,
            'count' => count($notifications)
        ]);
    }

    public function count(): JsonResponse
    {
        $notifications = $this->generateNotifications();
        
        return response()->json([
            'count' => count($notifications)
        ]);
    }

    private function generateNotifications(): array
    {
        $notifications = [];

        // 1. Barang Masuk Baru (24 jam terakhir)
        $barangMasukBaru = BarangMasuk::with('barang')
            ->where('created_at', '>=', Carbon::now()->subDay())
            ->orderBy('created_at', 'desc')
            ->get();

        foreach ($barangMasukBaru as $masuk) {
            $notifications[] = [
                'id' => 'masuk_' . $masuk->id,
                'type' => 'barang_masuk',
                'icon' => 'fas fa-arrow-down',
                'color' => 'green',
                'title' => 'Barang Masuk Baru',
                'message' => "Barang '{$masuk->barang->nama}' telah masuk ({$masuk->jumlah} unit)",
                'time' => $this->getTimeAgo($masuk->created_at),
                'timestamp' => $masuk->created_at->timestamp,
                'url' => '#' // Sesuaikan dengan route yang ada
            ];
        }

        // 2. Stok Hampir Habis (total < 5 unit)
        $stokRendah = Barang::where('total', '<', 5)
            ->where('total', '>', 0)
            ->orderBy('total', 'asc')
            ->get();

        foreach ($stokRendah as $barang) {
            $notifications[] = [
                'id' => 'stok_' . $barang->id,
                'type' => 'stok_rendah',
                'icon' => 'fas fa-exclamation-triangle',
                'color' => 'yellow',
                'title' => 'Stok Hampir Habis',
                'message' => "Barang '{$barang->nama}' tersisa {$barang->total} unit",
                'time' => 'Perlu perhatian',
                'timestamp' => time(),
                'url' => '#' // Sesuaikan dengan route yang ada
            ];
        }

        // 3. Barang Keluar (24 jam terakhir)
        $barangKeluarBaru = BarangKeluar::with('barang')
            ->where('created_at', '>=', Carbon::now()->subDay())
            ->orderBy('created_at', 'desc')
            ->get();

        foreach ($barangKeluarBaru as $keluar) {
            $notifications[] = [
                'id' => 'keluar_' . $keluar->id,
                'type' => 'barang_keluar',
                'icon' => 'fas fa-arrow-up',
                'color' => 'blue',
                'title' => 'Barang Keluar',
                'message' => "Barang '{$keluar->barang->nama}' keluar ({$keluar->jumlah} unit) ke {$keluar->tujuan}",
                'time' => $this->getTimeAgo($keluar->created_at),
                'timestamp' => $keluar->created_at->timestamp,
                'url' => '#' // Sesuaikan dengan route yang ada
            ];
        }

        // 4. Stok Habis
        $stokHabis = Barang::where('total', '=', 0)->get();
        
        foreach ($stokHabis as $barang) {
            $notifications[] = [
                'id' => 'habis_' . $barang->id,
                'type' => 'stok_habis',
                'icon' => 'fas fa-times-circle',
                'color' => 'red',
                'title' => 'Stok Habis',
                'message' => "Barang '{$barang->nama}' sudah habis",
                'time' => 'Segera restock',
                'timestamp' => time(),
                'url' => '#' // Sesuaikan dengan route yang ada
            ];
        }

        // 5. Barang Kondisi Rusak (perlu perhatian)
        $barangRusak = Barang::where('kondisi', '!=', 'baik')
            ->orderBy('updated_at', 'desc')
            ->limit(10) // Batasi untuk menghindari terlalu banyak notifikasi
            ->get();

        foreach ($barangRusak as $barang) {
            $kondisiText = $barang->kondisi == 'rusak_ringan' ? 'rusak ringan' : 'rusak berat';
            $notifications[] = [
                'id' => 'rusak_' . $barang->id,
                'type' => 'barang_rusak',
                'icon' => 'fas fa-tools',
                'color' => 'red',
                'title' => 'Barang Perlu Perbaikan',
                'message' => "Barang '{$barang->nama}' dalam kondisi {$kondisiText}",
                'time' => 'Perlu perbaikan',
                'timestamp' => $barang->updated_at->timestamp ?? time(),
                'url' => '#' // Sesuaikan dengan route yang ada
            ];
        }

        // Sort by timestamp descending
        usort($notifications, function($a, $b) {
            return $b['timestamp'] - $a['timestamp'];
        });

        return $notifications;
    }

    /**
     * Get human readable time ago
     */
    private function getTimeAgo($datetime): string
    {
        $now = Carbon::now();
        $time = Carbon::parse($datetime);
        
        $diffInMinutes = $now->diffInMinutes($time);
        $diffInHours = $now->diffInHours($time);
        $diffInDays = $now->diffInDays($time);

        if ($diffInMinutes < 60) {
            return $diffInMinutes . ' menit yang lalu';
        } elseif ($diffInHours < 24) {
            return $diffInHours . ' jam yang lalu';
        } elseif ($diffInDays == 1) {
            return 'Kemarin';
        } else {
            return $diffInDays . ' hari yang lalu';
        }
    }

    /**
     * Mark notification as read (optional for future use)
     */
    public function markAsRead(Request $request): JsonResponse
    {
        $notificationId = $request->input('notification_id');
        
        // Here you can implement marking notification as read
        // For now, we'll just return success
        
        return response()->json([
            'success' => true,
            'message' => 'Notification marked as read'
        ]);
    }
}