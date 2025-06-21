<?php
declare(strict_types=1);

class Security {
    // Input sanitization
    public static function sanitize($input): string {
        if (is_array($input)) {
            return array_map([self::class, 'sanitize'], $input);
        }
        return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
    }

    // Output escaping
    public static function escape($output): string {
        return htmlspecialchars($output, ENT_QUOTES, 'UTF-8', false);
    }

    // CSRF token management
    public static function generateCsrf(): string {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    public static function validateCsrf(string $token): bool {
        return isset($_SESSION['csrf_token']) && 
               hash_equals($_SESSION['csrf_token'], $token);
    }

    // Email validation
    public static function validateEmail(string $email): bool {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    // Password validation
    public static function validatePassword(string $password): bool {
        return strlen($password) >= 8 &&
               preg_match('/[A-Z]/', $password) &&
               preg_match('/[a-z]/', $password) &&
               preg_match('/[0-9]/', $password);
    }

    // File upload validation
    public static function validateFileUpload(array $file, array $allowedTypes = null): bool {
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return false;
        }
        
        // Default allowed types
        $allowedTypes = $allowedTypes ?: [
            'image/jpeg', 'image/png', 'image/gif', 
            'application/pdf', 'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
        ];
        
        // MIME type validation
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        
        return in_array($mime, $allowedTypes, true);
    }

    // XSS prevention in HTML output
    public static function cleanHtml(string $html): string {
        $config = HTMLPurifier_Config::createDefault();
        $config->set('Core.Encoding', 'UTF-8');
        $config->set('HTML.Doctype', 'HTML 4.01 Transitional');
        $config->set('HTML.Allowed', 'p,b,i,em,strong,a[href|title],ul,ol,li,br,img[src|alt]');
        
        $purifier = new HTMLPurifier($config);
        return $purifier->purify($html);
    }
    
    // Secure session regeneration
    public static function regenerateSession(): void {
        session_regenerate_id(true);
        $_SESSION['created'] = time();
    }
}