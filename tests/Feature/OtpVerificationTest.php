<?php

namespace Tests\Feature;

use App\Models\User;
use App\Mail\OtpVerificationMail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Session;
use Tests\TestCase;

class OtpVerificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_cannot_access_dashboard_without_otp_verification(): void
    {
        // Buat user tanpa verifikasi email
        $user = User::factory()->create([
            'email_verified_at' => null,
        ]);

        // Login user
        $this->actingAs($user);

        // Coba akses dashboard
        $response = $this->get('/dashboard');

        // Seharusnya redirect ke halaman OTP
        $response->assertRedirect('/otp');
    }

    public function test_user_cannot_access_protected_routes_without_otp_verification(): void
    {
        // Buat user tanpa verifikasi email
        $user = User::factory()->create([
            'email_verified_at' => null,
        ]);

        // Login user
        $this->actingAs($user);

        // Test beberapa route yang dilindungi
        $this->get('/data_barang')->assertRedirect('/otp');
        $this->get('/data_ruangan')->assertRedirect('/otp');
        $this->get('/profile')->assertRedirect('/otp');
        $this->get('/user/data')->assertRedirect('/otp');
    }

    public function test_user_can_access_otp_page_when_not_verified(): void
    {
        // Buat user tanpa verifikasi email
        $user = User::factory()->create([
            'email_verified_at' => null,
        ]);

        // Login user
        $this->actingAs($user);

        // Akses halaman OTP
        $response = $this->get('/otp');
        $response->assertStatus(200);
        $response->assertViewIs('auth.otp');
    }

    public function test_user_cannot_access_otp_page_when_already_verified(): void
    {
        // Buat user dengan verifikasi email
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        // Login user
        $this->actingAs($user);

        // Akses halaman OTP
        $response = $this->get('/otp');

        // Seharusnya redirect ke dashboard
        $response->assertRedirect('/dashboard');
    }

    public function test_otp_verification_with_correct_code(): void
    {
        Mail::fake();

        // Buat user tanpa verifikasi email
        $user = User::factory()->create([
            'email_verified_at' => null,
        ]);

        // Login user
        $this->actingAs($user);

        // Set session OTP
        Session::put('otp', '1234');
        Session::put('otp_email', $user->email);
        Session::put('otp_expired', now()->addMinute()->timestamp);

        // Submit OTP yang benar
        $response = $this->post('/otp', [
            'otp1' => '1',
            'otp2' => '2',
            'otp3' => '3',
            'otp4' => '4',
        ]);

        // Seharusnya redirect ke halaman success
        $response->assertRedirect('/otp-success');

        // Cek apakah email sudah terverifikasi
        $this->assertNotNull($user->fresh()->email_verified_at);
    }

    public function test_otp_verification_with_incorrect_code(): void
    {
        // Buat user tanpa verifikasi email
        $user = User::factory()->create([
            'email_verified_at' => null,
        ]);

        // Login user
        $this->actingAs($user);

        // Set session OTP
        Session::put('otp', '1234');
        Session::put('otp_email', $user->email);
        Session::put('otp_expired', now()->addMinute()->timestamp);

        // Submit OTP yang salah
        $response = $this->post('/otp', [
            'otp1' => '5',
            'otp2' => '6',
            'otp3' => '7',
            'otp4' => '8',
        ]);

        // Seharusnya kembali ke halaman OTP dengan error
        $response->assertRedirect('/otp');
        $response->assertSessionHasErrors(['otp']);

        // Cek apakah email belum terverifikasi
        $this->assertNull($user->fresh()->email_verified_at);
    }

    public function test_otp_verification_with_expired_code(): void
    {
        // Buat user tanpa verifikasi email
        $user = User::factory()->create([
            'email_verified_at' => null,
        ]);

        // Login user
        $this->actingAs($user);

        // Set session OTP yang sudah expired
        Session::put('otp', '1234');
        Session::put('otp_email', $user->email);
        Session::put('otp_expired', now()->subMinute()->timestamp);

        // Submit OTP
        $response = $this->post('/otp', [
            'otp1' => '1',
            'otp2' => '2',
            'otp3' => '3',
            'otp4' => '4',
        ]);

        // Seharusnya kembali ke halaman OTP dengan error expired
        $response->assertRedirect('/otp');
        $response->assertSessionHasErrors(['otp']);

        // Cek apakah email belum terverifikasi
        $this->assertNull($user->fresh()->email_verified_at);
    }

    public function test_user_can_access_dashboard_after_otp_verification(): void
    {
        // Buat user dengan verifikasi email
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        // Login user
        $this->actingAs($user);

        // Akses dashboard
        $response = $this->get('/dashboard');

        // Seharusnya bisa akses dashboard
        $response->assertStatus(200);
        $response->assertViewIs('dashboard');
    }

    public function test_otp_resend_functionality(): void
    {
        Mail::fake();

        // Buat user tanpa verifikasi email
        $user = User::factory()->create([
            'email_verified_at' => null,
        ]);

        // Login user
        $this->actingAs($user);

        // Request resend OTP
        $response = $this->post('/otp/resend');

        // Seharusnya redirect ke halaman OTP dengan status
        $response->assertRedirect('/otp');
        $response->assertSessionHas('status');

        // Cek apakah OTP baru sudah diset di session
        $this->assertNotNull(Session::get('otp'));
        $this->assertNotNull(Session::get('otp_expired'));
    }
}
