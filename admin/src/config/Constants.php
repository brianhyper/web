<?php
namespace App\config;

class Constants {
    // Application version
    const VERSION = '1.0.0';
    
    // User roles
    const ROLE_ADMIN = 'admin';
    const ROLE_STAFF = 'staff';
    
    // Project statuses
    const STATUS_PLANNED = 'planned';
    const STATUS_ACTIVE = 'active';
    const STATUS_COMPLETED = 'completed';
    const STATUS_ON_HOLD = 'on_hold';
    
    // Transaction types
    const TRANSACTION_INCOME = 'income';
    const TRANSACTION_EXPENSE = 'expense';
    
    // Chat message senders
    const SENDER_USER = 'user';
    const SENDER_BOT = 'bot';
    const SENDER_CLIENT = 'client';
    
    // Notification types
    const NOTIFICATION_EMAIL = 'email';
    const NOTIFICATION_SMS = 'sms';
    const NOTIFICATION_APP = 'app';
    
    // Security constants
    const PASSWORD_MIN_LENGTH = 8;
    const CSRF_TOKEN_EXPIRY = 3600; // 1 hour
    const VERIFICATION_TOKEN_EXPIRY = 3600; // 1 hour
    const REMEMBER_ME_EXPIRY = 2592000; // 30 days
    
    // File upload settings
    const MAX_UPLOAD_SIZE = 2097152; // 2MB
    const ALLOWED_MIME_TYPES = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'application/pdf' => 'pdf',
        'text/plain' => 'txt',
        'application/vnd.ms-excel' => 'csv',
        'text/csv' => 'csv',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => 'xlsx'
    ];
    
    // Path constants
    public static function basePath() {
        return realpath(__DIR__ . '/../../');
    }
    
    public static function storagePath($type = '') {
        $path = self::basePath() . '/storage/';
        switch ($type) {
            case 'logs':
                return $path . 'logs/';
            case 'exports':
                return $path . 'exports/';
            case 'receipts':
                return $path . 'receipts/';
            case 'uploads':
                return $path . 'uploads/';
            default:
                return $path;
        }
    }
    
    public static function publicPath() {
        return self::basePath() . '/public/';
    }
    
    // Date formats
    const DATE_FORMAT = 'Y-m-d';
    const DATETIME_FORMAT = 'Y-m-d H:i:s';
    const DISPLAY_DATE = 'F j, Y';
    const DISPLAY_DATETIME = 'F j, Y g:i a';
}