<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;

class AccountController extends Controller
{
    public function store(Request $request)
    {
        $recaptchaSecret = (string) config('services.recaptcha.secret_key');
        $recaptchaEnabled = $recaptchaSecret !== '';

        $validationRules = [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
        ];

        if ($recaptchaEnabled) {
            $validationRules['g-recaptcha-response'] = 'required|string';
        }

        // Validate the request data
        $request->validate($validationRules, [
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

        $fullName = trim($request->name);
        $nameParts = preg_split('/\s+/', $fullName, -1, PREG_SPLIT_NO_EMPTY);
        $firstName = $nameParts[0] ?? $fullName;
        $lastName = count($nameParts) > 1 ? implode(' ', array_slice($nameParts, 1)) : '';

        // Build a unique username from the submitted name.
        $baseUsername = strtolower(preg_replace('/[^a-z0-9]+/i', '', $fullName));
        if ($baseUsername === '') {
            $baseUsername = 'user';
        }

        $username = $baseUsername;
        $counter = 1;
        while (User::where('username', $username)->exists()) {
            $username = $baseUsername.$counter;
            $counter++;
        }

        // Create the user
        User::create([
            'username' => $username,
            'name' => $fullName,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'owner',
            'profile' => [
                'first_name' => $firstName,
                'last_name' => $lastName,
                'full_name' => $fullName,
            ],
        ]);

        // Redirect to a success page or login
        return redirect('/login')->with('success', 'Account created successfully!');
    }
}