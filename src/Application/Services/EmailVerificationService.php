<?php
namespace App\Application\Services;

use App\Infrastructure\Repositories\UserRepository;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class EmailVerificationService
{
    private UserRepository $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * Send a verification code to the user's email
     */
    public function sendVerificationCode(string $email): void
    {
        $user = $this->userRepository->findByEmail($email);
        if (!$user) {
            throw new \Exception("User not found for email: $email");
        }

        // Always generate a new 4-digit code for security
        $code = $this->generateVerificationCode();
        
        $this->userRepository->update($user->id, [
            'verification_code' => $code,
            // Set expiry to 5 minutes to align with auto-cleanup
            'verification_code_expires_at' => date('Y-m-d H:i:s', strtotime('+5 minutes'))
        ]);

        $this->sendVerificationEmail($email, (string)$code);
        error_log("Generated code for {$email}: {$code}");
    }

    /**
     * Verify the email with the provided code
     */
    public function verifyEmail(string $email, string $code): bool
    {
        $user = $this->userRepository->findByEmail($email);
        
        if (!$user) {
            throw new \Exception("User not found");
        }

        if ($user->is_verified) {
            throw new \Exception("Email already verified");
        }

        // Clean and validate input code
        $inputCode = trim($code);
        if (empty($inputCode) || !ctype_digit($inputCode) || strlen($inputCode) !== 4) {
            return false;
        }

        // Get stored code and ensure consistent comparison
        $storedCode = (string)$user->verification_code;
        
        // Debug logging - remove in production
        error_log("=== VERIFICATION DEBUG ===");
        error_log("Input code: '{$inputCode}' (length: " . strlen($inputCode) . ")");
        error_log("Stored code: '{$storedCode}' (length: " . strlen($storedCode) . ")");
        error_log("Codes match: " . ($storedCode === $inputCode ? 'YES' : 'NO'));

        // Check if code is empty or doesn't match
        if (empty($storedCode) || $storedCode !== $inputCode) {
            error_log("Verification failed: code mismatch");
            return false;
        }

        // Check expiration
        if ($this->isVerificationCodeExpired($user)) {
            throw new \Exception("Verification code has expired");
        }

        // Mark email as verified and clear verification data
        $this->userRepository->update($user->id, [
            'is_verified' => true,
            'email_verified_at' => date('Y-m-d H:i:s'),
            'verification_code' => null,
            'verification_code_expires_at' => null
        ]);

        error_log("Email verification successful for: " . $email);
        return true;
    }

    /**
     * Resend verification code
     */
    public function resendVerificationCode(string $email): void
    {
        $user = $this->userRepository->findByEmail($email);
        
        if (!$user) {
            throw new \Exception("User not found");
        }

        if ($user->is_verified) {
            throw new \Exception("Email already verified");
        }

        // Generate new code and update database
        $code = $this->generateVerificationCode();
        
        $this->userRepository->update($user->id, [
            'verification_code' => $code,
            'verification_code_expires_at' => date('Y-m-d H:i:s', strtotime('+5 minutes'))
        ]);

        $this->sendVerificationEmail($email, (string)$code);
    }

    /**
     * Generate a 4-digit verification code
     */
    private function generateVerificationCode(): int
    {
        return random_int(1000, 9999);
    }

    /**
     * Check if verification code has expired
     */
    private function isVerificationCodeExpired($user): bool
    {
        if (!$user->verification_code_expires_at) {
            return false;
        }

        return strtotime($user->verification_code_expires_at) < time();
    }

    /**
      * Send verification email using PHPMailer
      */
    private function sendVerificationEmail(string $toEmail, string $code): void
    {
        try {
            // Check if SMTP is configured for development
            $smtpHost = $this->getEnvVar('SMTP_HOST');
            $smtpUser = $this->getEnvVar('SMTP_USERNAME');
            $smtpPass = $this->getEnvVar('SMTP_PASSWORD');

            // If SMTP is not configured, skip email sending in development
            if (empty($smtpHost) || empty($smtpUser) || empty($smtpPass)) {
                if (getenv('APP_ENV') === 'development') {
                    error_log("DEVELOPMENT MODE: Skipping email sending for {$toEmail}. Code: {$code}");
                    return; // Skip email sending in development
                } else {
                    throw new \Exception("SMTP configuration is required in production environment");
                }
            }

            $mail = new PHPMailer(true);

            // SMTP configuration
            $mail->isSMTP();
            $mail->Host = $smtpHost;
            $mail->SMTPAuth = true;
            $mail->Username = $smtpUser;
            $mail->Password = $smtpPass;
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = (int)$this->getEnvVar('SMTP_PORT', '587');

            // SSL options for Gmail
            $mail->SMTPOptions = [
                'ssl' => [
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true
                ]
            ];

            // Email setup
            $fromEmail = $this->getEnvVar('SMTP_FROM_EMAIL', 'noreply@gearfalcon.com');
            $mail->setFrom($fromEmail, 'GearFalcon Team');
            $mail->addAddress($toEmail);

            // Content
            $mail->isHTML(true);
            $mail->Subject = 'Verify your GearFalcon account';
            $mail->Body = $this->getEmailTemplate($code);
            $mail->AltBody = "Your GearFalcon verification code is: {$code}. This code expires in 15 minutes.";

            $mail->send();
            error_log("Verification email sent successfully to: " . $toEmail);

        } catch (Exception $e) {
            error_log("Email sending failed: " . $e->getMessage());
            throw new \Exception("Failed to send verification email. Please try again.");
        }
    }

    /**
     * Get environment variable with fallback
     */
    private function getEnvVar(string $key, string $default = ''): string
    {
        $value = $_ENV[$key] ?? getenv($key) ?? $default;
        
        if (empty($value) && empty($default)) {
            error_log("Environment variable {$key} is not set");
        }
        
        return $value;
    }

    /**
     * Get HTML email template
     */
    private function getEmailTemplate(string $code): string
    {
        return "
        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px;'>
            <div style='text-align: center; margin-bottom: 30px;'>
                <h1 style='color: #333;'>GearFalcon</h1>
            </div>
            <div style='background: #f8f9fa; padding: 30px; border-radius: 10px;'>
                <h2 style='color: #333; margin-bottom: 20px;'>Verify Your Email Address</h2>
                <p style='color: #666; margin-bottom: 20px;'>
                    Thank you for registering with GearFalcon! Please use the verification code below to verify your email address:
                </p>
                <div style='text-align: center; margin: 30px 0;'>
                    <span style='font-size: 32px; font-weight: bold; background: #007bff; color: white; padding: 15px 30px; border-radius: 5px; letter-spacing: 5px; font-family: monospace;'>
                        {$code}
                    </span>
                </div>
                <p style='color: #666; font-size: 14px;'>
                    This verification code will expire in 15 minutes. If you didn't request this verification, please ignore this email.
                </p>
            </div>
            <div style='text-align: center; margin-top: 20px; color: #999; font-size: 12px;'>
                <p>GearFalcon Team</p>
            </div>
        </div>";
    }
}
?>