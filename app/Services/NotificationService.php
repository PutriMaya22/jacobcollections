<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    public static function createNotification(
        User $user,
        string $type,
        string $title,
        string $message,
        ?string $icon = null,
        ?string $color = null,
        ?array $data = null
    ): Notification {
        try {
            return Notification::create([
                'user_id' => $user->id,
                'type' => $type,
                'title' => $title,
                'message' => $message,
                'icon' => $icon ?? self::getDefaultIcon($type),
                'color' => $color ?? self::getDefaultColor($type),
                'data' => $data,
                'is_read' => false
            ]);
        } catch (\Exception $e) {
            Log::error('Error creating notification: ' . $e->getMessage());
            throw $e;
        }
    }

    public static function createBarangMasukNotification(User $user, string $barangName, int $jumlah): Notification
    {
        return self::createNotification(
            user: $user,
            type: 'barang_masuk',
            title: 'Barang Masuk Baru',
            message: "Barang '{$barangName}' telah masuk dengan jumlah {$jumlah} unit",
            icon: 'fas fa-box-open',
            color: 'green',
            data: [
                'barang_name' => $barangName,
                'jumlah' => $jumlah,
                'action' => 'barang_masuk'
            ]
        );
    }

    public static function createBarangKeluarNotification(User $user, string $barangName, int $jumlah): Notification
    {
        return self::createNotification(
            user: $user,
            type: 'barang_keluar',
            title: 'Barang Keluar Baru',
            message: "Barang '{$barangName}' telah keluar dengan jumlah {$jumlah} unit",
            icon: 'fas fa-box',
            color: 'orange',
            data: [
                'barang_name' => $barangName,
                'jumlah' => $jumlah,
                'action' => 'barang_keluar'
            ]
        );
    }

    public static function createStokHabisNotification(User $user, string $barangName): Notification
    {
        return self::createNotification(
            user: $user,
            type: 'warning',
            title: 'Stok Barang Habis',
            message: "Stok barang '{$barangName}' telah habis. Silakan restock.",
            icon: 'fas fa-exclamation-triangle',
            color: 'red',
            data: [
                'barang_name' => $barangName,
                'action' => 'stok_habis'
            ]
        );
    }

    public static function createStokMenipisNotification(User $user, string $barangName, int $sisaStok): Notification
    {
        return self::createNotification(
            user: $user,
            type: 'warning',
            title: 'Stok Barang Menipis',
            message: "Stok barang '{$barangName}' menipis. Sisa stok: {$sisaStok} unit",
            icon: 'fas fa-exclamation-triangle',
            color: 'yellow',
            data: [
                'barang_name' => $barangName,
                'sisa_stok' => $sisaStok,
                'action' => 'stok_menipis'
            ]
        );
    }

    public static function createUserActivityNotification(User $user, string $activity): Notification
    {
        return self::createNotification(
            user: $user,
            type: 'user_activity',
            title: 'Aktivitas Pengguna',
            message: $activity,
            icon: 'fas fa-user',
            color: 'blue',
            data: [
                'activity' => $activity,
                'action' => 'user_activity'
            ]
        );
    }

    public static function createSystemNotification(User $user, string $title, string $message, string $type = 'system'): Notification
    {
        return self::createNotification(
            user: $user,
            type: $type,
            title: $title,
            message: $message,
            icon: 'fas fa-cog',
            color: 'blue',
            data: [
                'action' => 'system_notification'
            ]
        );
    }

    private static function getDefaultIcon(string $type): string
    {
        return match ($type) {
            'user_activity' => 'fas fa-user',
            'data_update' => 'fas fa-database',
            'system' => 'fas fa-cog',
            'role_change' => 'fas fa-user-tag',
            'barang_masuk' => 'fas fa-box-open',
            'barang_keluar' => 'fas fa-box',
            'warning' => 'fas fa-exclamation-triangle',
            'error' => 'fas fa-times-circle',
            'success' => 'fas fa-check-circle',
            'info' => 'fas fa-info-circle',
            default => 'fas fa-bell'
        };
    }

    private static function getDefaultColor(string $type): string
    {
        return match ($type) {
            'user_activity' => 'blue',
            'data_update' => 'green',
            'system' => 'blue',
            'role_change' => 'purple',
            'barang_masuk' => 'green',
            'barang_keluar' => 'orange',
            'warning' => 'yellow',
            'error' => 'red',
            'success' => 'green',
            'info' => 'blue',
            default => 'blue'
        };
    }

    public static function getUnreadCount(User $user): int
    {
        return Notification::where('user_id', $user->id)
            ->where('is_read', false)
            ->count();
    }

    public static function markAllAsRead(User $user): void
    {
        Notification::where('user_id', $user->id)
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now()
            ]);
    }
}
