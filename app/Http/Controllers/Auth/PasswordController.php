<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\Mail;
use App\Mail\OtpVerificationMail;

class PasswordController extends Controller
{
    /**
     * Update the user's password.
     */
    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validateWithBag('updatePassword', [
            'current_password' => ['required', 'current_password'],
            'password' => ['required', Password::defaults(), 'confirmed'],
            'otp_code' => ['required', 'digits:4'],
        ]);

        // Validate OTP from session
        $sessionOtp = session('otp');
        $expiredAt = session('otp_expired');

        if (!$sessionOtp) {
            return back()->withErrors(['otp_code' => 'Kode OTP tidak ditemukan. Silakan kirim OTP terlebih dahulu.'], 'updatePassword');
        }

        if (now()->timestamp > ($expiredAt ?? 0)) {
            return back()->withErrors(['otp_code' => 'Kode OTP sudah expired. Silakan kirim ulang OTP.'], 'updatePassword');
        }

        if ((string) $validated['otp_code'] !== (string) $sessionOtp) {
            return back()->withErrors(['otp_code' => 'Kode OTP tidak sesuai.'], 'updatePassword');
        }

        $request->user()->update([
            'password' => Hash::make($validated['password']),
        ]);

        // Clear OTP after successful use
        session()->forget(['otp', 'otp_email', 'otp_expired']);

        return back()->with('status', 'password-updated');
    }

    /**
     * Send OTP code to the authenticated user's email for password update
     */
    public function sendOtp(Request $request): RedirectResponse
    {
        $user = $request->user();
        $otp = random_int(1000, 9999);

        // store OTP in session for 60 seconds
        session([
            'otp' => $otp,
            'otp_email' => $user->email,
            'otp_expired' => now()->addMinute()->timestamp,
        ]);

        try {
            Mail::to($user->email)->send(new OtpVerificationMail($user, $otp));
            \Log::info('Password change OTP mailed to '.$user->email.': '.$otp);
            $flash = ['status' => 'Kode OTP telah dikirim ke email Anda.', 'open_tab' => 'password'];
            if (config('app.debug')) {
                $flash['dev_otp'] = $otp;
            }
            return redirect()->route('profile.edit')->with($flash);
        } catch (\Exception $e) {
            \Log::error('Failed to send OTP for password update: ' . $e->getMessage());
            return redirect()->route('profile.edit')->with(['error' => 'Gagal mengirim OTP. Silakan coba lagi.', 'open_tab' => 'password']);
        }
    }
}
