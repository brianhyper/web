<?php
// src/helpers/CSRF.php
namespace App\helpers;

class CSRF {
    public static function generateToken() {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    public static function validateToken($token) {
        if (empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
            throw new \Exception("Invalid CSRF token");
        }
        // Regenerate token after validation
        unset($_SESSION['csrf_token']);
        return true;
    }

    public static function tokenField() {
        return '<input type="hidden" name="csrf_token" value="' . self::generateToken() . '">';
    }
    
    public static function metaTag() {
        return '<meta name="csrf-token" content="' . self::generateToken() . '">';
    }
}