<?php

return [

    'google' => [
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect' => env('GOOGLE_REDIRECT_URI', env('APP_URL').'/auth/google/callback'),
    ],

    'recaptcha' => [
        // Prefer explicit env keys. In local development, fall back to
        // Google's official test keys only when no env keys are set.
        'site_key' => env('RECAPTCHA_SITE_KEY')
            ?: ((env('APP_ENV') === 'local') ? '6LeIxAcTAAAAAJcZVRqyHh71UMIEGNQ_MXjiZKhI' : null),
        'secret_key' => env('RECAPTCHA_SECRET_KEY')
            ?: ((env('APP_ENV') === 'local') ? '6LeIxAcTAAAAAGG-vFI1TnRWxMZNFuojJ4WifJWe' : null),
    ],

];
