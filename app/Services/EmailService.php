<?php

namespace App\Services;

use App\Helpers\EmailHelper;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Mail\Mailable;

class EmailService
{
    /**
     * Send welcome email with auto-generated password
     */
    public static function sendWelcomeEmail(string $email, string $businessName, string $userRole = 'owner', string $password = null)
    {
        $password = $password ?: self::generateSecurePassword();
        
        try {
            $mailable = new class($email, $businessName, $userRole, $password) extends Mailable {
            private $email;
            private $businessName;
            private $userRole;
            private $password;

            public function __construct($email, $businessName, $userRole, $password)
            {
                $this->email = $email;
                $this->businessName = $businessName;
                $this->userRole = $userRole;
                $this->password = $password;
            }

            public function envelope()
            {
                return new \Illuminate\Mail\Envelope(
                    subject: "Welcome to {$this->businessName} - Meat Shop POS"
                );
            }

            public function content()
            {
                return new \Illuminate\Mail\Content(
                    view: 'emails.welcome',
                    with: [
                        'email' => $this->email,
                        'businessName' => $this->businessName,
                        'userRole' => $this->userRole,
                        'password' => $this->password,
                        'loginUrl' => config('app.url') . '/login'
                    ]
                );
            }
            public function attachments()
            {
                return [];
            }
        };

            // Use business email as sender
            $fromEmail = EmailHelper::getBusinessEmail('info', $businessName);
            $fromName = "{$businessName} - Meat Shop POS";

            Mail::to($email)
                ->from($fromEmail, $fromName)
                ->send($mailable);

            return [
                'success' => true,
                'password' => $password,
                'message' => 'Welcome email sent successfully'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'password' => $password,
                'error' => $e->getMessage(),
                'message' => 'Failed to send welcome email'
            ];
        }
    }

    /**
     * Send password reset email
     */
    public static function sendPasswordReset(string $email, string $businessName, string $resetToken)
    {
        try {
            $mailable = new class($email, $businessName, $resetToken) extends Mailable {
                private $email;
                private $businessName;
                private $resetToken;

                public function __construct($email, $businessName, $resetToken)
                {
                    $this->email = $email;
                    $this->businessName = $businessName;
                    $this->resetToken = $resetToken;
                }

                public function envelope()
                {
                    return new \Illuminate\Mail\Envelope(
                        subject: "Password Reset Request - {$this->businessName}"
                    );
                }

                public function content()
                {
                    return new \Illuminate\Mail\Content(
                        view: 'emails.password-reset',
                        with: [
                            'email' => $this->email,
                            'businessName' => $this->businessName,
                            'resetToken' => $this->resetToken,
                            'resetUrl' => config('app.url') . "/password-reset/{$this->resetToken}"
                        ]
                    );
                }

                public function attachments()
                {
                    return [];
                }
            };

            $fromEmail = EmailHelper::getBusinessEmail('support', $businessName);
            $fromName = "{$businessName} - Support";

            Mail::to($email)
                ->from($fromEmail, $fromName)
                ->send($mailable);

            return [
                'success' => true,
                'message' => 'Password reset email sent successfully'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'message' => 'Failed to send password reset email'
            ];
        }
    }

    /**
     * Send tenant creation confirmation email
     */
    public static function sendTenantCreationConfirmation(string $email, string $businessName, array $tenantDetails)
    {
        try {
            $mailable = new class($email, $businessName, $tenantDetails) extends Mailable {
                private $email;
                private $businessName;
                private $tenantDetails;

                public function __construct($email, $businessName, $tenantDetails)
                {
                    $this->email = $email;
                    $this->businessName = $businessName;
                    $this->tenantDetails = $tenantDetails;
                }

                public function envelope()
                {
                    return new \Illuminate\Mail\Envelope(
                        subject: "Your Meat Shop POS Account is Ready - {$this->businessName}"
                    );
                }

                public function content()
                {
                    return new \Illuminate\Mail\Content(
                        view: 'emails.tenant-creation',
                        with: [
                            'email' => $this->email,
                            'businessName' => $this->businessName,
                            'tenantDetails' => $this->tenantDetails,
                            'loginUrl' => $this->tenantDetails['login_url'] ?? config('app.url') . '/login'
                        ]
                    );
                }

                public function attachments()
                {
                    return [];
                }
            };

            $fromEmail = EmailHelper::getBusinessEmail('billing', $businessName);
            $fromName = "{$businessName} - Meat Shop POS";

            Mail::to($email)
                ->from($fromEmail, $fromName)
                ->send($mailable);

            return [
                'success' => true,
                'message' => 'Tenant creation confirmation email sent successfully'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'message' => 'Failed to send tenant creation confirmation email'
            ];
        }
    }

    /**
     * Generate secure random password
     */
    public static function generateSecurePassword(int $length = 12): string
    {
        $uppercase = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $lowercase = 'abcdefghijklmnopqrstuvwxyz';
        $numbers = '0123456789';
        $specialChars = '!@#$%^&*()_+-=[]{}|;:,.<>?';

        $allChars = $uppercase . $lowercase . $numbers . $specialChars;
        
        // Ensure at least one character from each set
        $password = '';
        $password .= $uppercase[random_int(0, strlen($uppercase) - 1)];
        $password .= $lowercase[random_int(0, strlen($lowercase) - 1)];
        $password .= $numbers[random_int(0, strlen($numbers) - 1)];
        $password .= $specialChars[random_int(0, strlen($specialChars) - 1)];

        // Fill remaining length with random characters from all sets
        for ($i = 4; $i < $length; $i++) {
            $password .= $allChars[random_int(0, strlen($allChars) - 1)];
        }

        // Shuffle the password to randomize character positions
        return str_shuffle($password);
    }

    /**
     * Test email configuration
     */
    public static function testEmailConfiguration(string $testEmail = null): array
    {
        $testEmail = $testEmail ?: EmailHelper::getBusinessEmail('info', 'Test Business');
        
        try {
            $mailable = new class($testEmail) extends Mailable {
                private $testEmail;

                public function __construct($testEmail)
                {
                    $this->testEmail = $testEmail;
                }

                public function envelope()
                {
                    return new \Illuminate\Mail\Envelope(
                        subject: 'Email Configuration Test - Meat Shop POS'
                    );
                }

                public function content()
                {
                    return new \Illuminate\Mail\Content(
                        view: 'emails.test',
                        with: [
                            'testEmail' => $this->testEmail,
                            'timestamp' => now()->format('Y-m-d H:i:s')
                        ]
                    );
                }

                public function attachments()
                {
                    return [];
                }
            };

            Mail::to($testEmail)
                ->send($mailable);

            return [
                'success' => true,
                'message' => 'Test email sent successfully to ' . $testEmail
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'message' => 'Failed to send test email'
            ];
        }
    }
}
