<?php
declare(strict_types=1);

class Token {
    public static function generate(string $type, int $length = 32): string {
        return bin2hex(random_bytes($length));
    }
    
    public static function validate(string $token, string $type, int $userId = null): bool {
        $db = Database::getInstance();
        
        switch ($type) {
            case 'verification':
                $sql = "SELECT COUNT(*) FROM users 
                        WHERE verification_token = :token 
                        AND token_expiry > NOW()";
                break;
                
            case 'password_reset':
                $sql = "SELECT COUNT(*) FROM password_resets 
                        WHERE token = :token 
                        AND expires_at > NOW()";
                break;
                
            case 'api':
                $sql = "SELECT COUNT(*) FROM api_tokens 
                        WHERE token = :token 
                        AND expires_at > NOW()";
                if ($userId) $sql .= " AND user_id = :user_id";
                break;
                
            default:
                throw new Exception("Invalid token type: $type");
        }
        
        $params = ['token' => $token];
        if ($userId && $type === 'api') $params['user_id'] = $userId;
        
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchColumn() > 0;
    }
    
    public static function invalidate(string $token, string $type): void {
        $db = Database::getInstance();
        
        switch ($type) {
            case 'verification':
                $sql = "UPDATE users SET verification_token = NULL 
                         WHERE verification_token = :token";
                break;
                
            case 'password_reset':
                $sql = "DELETE FROM password_resets WHERE token = :token";
                break;
                
            case 'api':
                $sql = "DELETE FROM api_tokens WHERE token = :token";
                break;
        }
        
        $stmt = $db->prepare($sql);
        $stmt->execute(['token' => $token]);
    }
}