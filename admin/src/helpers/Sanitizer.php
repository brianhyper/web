<?php
// src/helpers/Sanitizer.php
namespace App\helpers;

class Sanitizer {
    public static function sanitize($input) {
        if (is_array($input)) {
            return array_map('self::sanitize', $input);
        }
        
        if (is_object($input)) {
            $sanitized = new \stdClass();
            foreach ($input as $key => $value) {
                $sanitized->$key = self::sanitize($value);
            }
            return $sanitized;
        }
        
        // Remove whitespace
        $input = trim($input);
        
        // Convert special characters to HTML entities
        return htmlspecialchars($input, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }
    
    public static function sanitizeFilename($filename) {
        // Remove path information
        $filename = basename($filename);
        
        // Replace special characters
        $filename = preg_replace("/[^a-zA-Z0-9_.-]/", "_", $filename);
        
        // Add unique prefix
        return uniqid() . '_' . $filename;
    }
    
    public static function validateEmail($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL);
    }
    
    public static function validateFile($file) {
        $allowedTypes = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'application/pdf' => 'pdf',
            'text/plain' => 'txt',
            'application/vnd.ms-excel' => 'csv',
            'text/csv' => 'csv',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => 'xlsx'
        ];
        
        // Check for upload errors
        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new \Exception("File upload error: " . $file['error']);
        }
        
        // Check file size
        $maxSize = $_ENV['MAX_UPLOAD_SIZE'] ?? 2097152; // 2MB default
        if ($file['size'] > $maxSize) {
            throw new \Exception("File too large. Maximum size: " . ($maxSize / 1024 / 1024) . "MB");
        }
        
        // Verify MIME type
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $file['tmp_name']);
        
        if (!array_key_exists($mime, $allowedTypes)) {
            throw new \Exception("Invalid file type: $mime");
        }
        
        // Generate secure filename
        $extension = $allowedTypes[$mime];
        $newFilename = self::sanitizeFilename($file['name']) . '.' . $extension;
        
        return [
            'filename' => $newFilename,
            'mime' => $mime,
            'extension' => $extension
        ];
    }
    
    public static function escapeOutput($string) {
        return nl2br(self::sanitize($string));
    }
}