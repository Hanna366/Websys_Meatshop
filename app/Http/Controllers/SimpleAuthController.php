<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Throwable;

class SimpleAuthController extends Controller
{
    public function showLoginForm()
    {
        // If already authenticated, redirect to dashboard
        if (session('authenticated')) {
            return redirect('/dashboard');
        }
        
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $recaptchaSecret = (string) config('services.recaptcha.secret_key');
        $recaptchaEnabled = $recaptchaSecret !== '';

        $validationRules = [
            'email' => 'required|email',
            'password' => 'required',
        ];

        if ($recaptchaEnabled) {
            $validationRules['g-recaptcha-response'] = 'required|string';
        }

        $credentials = $request->validate($validationRules, [
            'g-recaptcha-response.required' => 'Please complete the reCAPTCHA challenge.',
        ]);

        if ($recaptchaEnabled) {
            $recaptchaResponse = Http::asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
                'secret' => $recaptchaSecret,
                'response' => (string) $request->input('g-recaptcha-response'),
                'remoteip' => $request->ip(),
            ]);

            if (!$recaptchaResponse->successful() || !data_get($recaptchaResponse->json(), 'success', false)) {
                return back()
                    ->withErrors(['captcha' => 'reCAPTCHA verification failed. Please try again.'])
                    ->withInput();
            }
        }

        // Demo accounts with different subscription plans
        $demoAccounts = [
            'basic@meatshop.com' => [
                'password' => 'basic123',
                'plan' => 'Basic',
                'features' => ['Up to 100 products', 'Inventory tracking', 'Single user']
            ],
            'standard@meatshop.com' => [
                'password' => 'standard123', 
                'plan' => 'Standard',
                'features' => ['Unlimited products', 'Full POS system', 'Up to 3 users', 'Customer management']
            ],
            'premium@meatshop.com' => [
                'password' => 'premium123',
                'plan' => 'Premium', 
                'features' => ['All features', 'Advanced analytics', 'API access', 'Unlimited users', 'Priority support']
            ]
        ];

        if (isset($demoAccounts[$credentials['email']]) && $demoAccounts[$credentials['email']]['password'] === $credentials['password']) {
            $account = $demoAccounts[$credentials['email']];
            $name = explode('@', $credentials['email'])[0];
            $name = ucfirst($name);
            
            session([
                'authenticated' => true,
                'user' => [
                    'email' => $credentials['email'],
                    'name' => $name,
                    'plan' => $account['plan'],
                    'features' => $account['features']
                ]
            ]);
            return redirect()->intended('/dashboard');
        }

        return back()->withErrors([
            'email' => 'Invalid credentials. Try basic@meatshop.com / basic123, standard@meatshop.com / standard123, or premium@meatshop.com / premium123',
        ]);
    }

    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }

    public function handleGoogleCallback()
    {
        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (Throwable $e) {
            return redirect('/login')->with('error', 'Google sign-in failed. Please try again.');
        }

        $email = $googleUser->getEmail();
        if (!$email) {
            return redirect('/login')->with('error', 'Google account has no email. Please use another account.');
        }

        $displayName = trim((string) ($googleUser->getName() ?: $googleUser->getNickname() ?: explode('@', $email)[0]));
        $displayName = $displayName !== '' ? $displayName : 'Google User';

        $nameParts = preg_split('/\s+/', $displayName, -1, PREG_SPLIT_NO_EMPTY);
        $firstName = $nameParts[0] ?? $displayName;
        $lastName = count($nameParts) > 1 ? implode(' ', array_slice($nameParts, 1)) : '';

        $user = User::where('email', $email)->first();

        if (!$user) {
            $baseUsername = strtolower(preg_replace('/[^a-z0-9]+/i', '', $displayName));
            if ($baseUsername === '') {
                $baseUsername = 'user';
            }

            $username = $this->buildUniqueUsername($baseUsername);

            $user = User::create([
                'username' => $username,
                'name' => $displayName,
                'email' => $email,
                'password' => Hash::make(Str::random(40)),
                'role' => 'owner',
                'profile' => [
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                    'full_name' => $displayName,
                    'google_id' => $googleUser->getId(),
                    'avatar' => $googleUser->getAvatar(),
                ],
            ]);
        }

        session()->regenerate();
        session([
            'authenticated' => true,
            'user' => [
                'email' => $user->email,
                'name' => $user->name,
                'plan' => 'Basic',
                'features' => ['Up to 100 products', 'Inventory tracking', 'Single user'],
            ],
        ]);

        return redirect()->intended('/dashboard');
    }

    private function buildUniqueUsername(string $baseUsername): string
    {
        $username = $baseUsername;
        $counter = 1;

        while (User::where('username', $username)->exists()) {
            $username = $baseUsername.$counter;
            $counter++;
        }

        return $username;
    }

    public function logout()
    {
        session()->invalidate();
        session()->regenerateToken();
        return redirect('/login');
    }
}
