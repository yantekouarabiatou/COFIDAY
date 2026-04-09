<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Illuminate\Support\Facades\Mail;
use App\Models\User;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    // public function store(LoginRequest $request): RedirectResponse
    // {
    //     $request->authenticate();

    //     $request->session()->regenerate();

    //     return redirect()->intended(route('dashboard', absolute: false));
    // }

        public function store(LoginRequest $request)
        {
            $request->authenticate(); // vérifie email + mot de passe

            $user = Auth::user();

            // Générer un OTP
            $otp = rand(100000, 999999);
            $user->otp_code = $otp;
            $user->otp_expires_at = now()->addMinutes(2);
            $user->save();

            // après authenticate()
            session(['otp_user_id' => $user->id, 'otp_pending' => true, 'remember' => $request->filled('remember')]);
            session()->save();

            // Envoie l'OTP et redirige vers le formulaire OTP sans logout
            Mail::to($user->email)->send(new \App\Mail\SendOtpMail($otp));
            return redirect()->route('otp.form');

        }

        // Vérification OTP
        public function verifyOtp(Request $request)
        {
            $request->validate([
                'otp_code' => 'required|array|size:6',
                'otp_code.*' => 'required|digits:1',
            ]);

            $otp = implode('', $request->otp_code);

            $user = User::find(session('otp_user_id'));

            if (!$user) {
                return redirect()->route('login')->withErrors('Utilisateur introuvable.');
            }

            if ($user->otp_code !== $otp || $user->otp_expires_at < now()) {
                return back()->withErrors('Code OTP invalide ou expiré.');
            }

            // Reset OTP
            $user->update([
                'otp_code' => null,
                'otp_expires_at' => null,
            ]);

            // Auth final
            Auth::login($user, session('remember'));

            // Nettoyage session OTP
            session()->forget(['otp_user_id', 'otp_pending', 'remember']);

            $request->session()->regenerate();

            return redirect()->intended(route('dashboard'));
        }


        public function showOtpForm()
        {
            $user = User::find(session('otp_user_id'));

            if (!$user) {
                return redirect()->route('login');
            }

            // Calcul du temps restant en secondes (entier)
            $remainingSeconds = max(0, intval(now()->diffInSeconds($user->otp_expires_at, false)));

            // Masquer l'email : ex "ad*******@gmail.com"
            $maskedEmail = preg_replace('/(?<=.).(?=[^@]*?.@)/', '*', $user->email);

            return view('auth.otp', [
                'remainingSeconds' => $remainingSeconds,
                'maskedEmail' => $maskedEmail
            ]);
        }



        public function resendOtp(Request $request)
        {
            $user = User::find(session('otp_user_id'));

            if (!$user) {
                return response()->json(['error' => 'Utilisateur introuvable'], 404);
            }

            // Générer un nouveau OTP
            $otp = rand(100000, 999999);

            $user->otp_code = $otp;
            $user->otp_expires_at = now()->addMinutes(2);
            $user->save();

            // Envoyer l'email
            Mail::to($user->email)->send(new \App\Mail\SendOtpMail($otp));

            return response()->json([
                'message' => 'OTP renvoyé',
                'remainingSeconds' => 120 // 2 minutes
            ]);
        }






    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/login');
    }
}