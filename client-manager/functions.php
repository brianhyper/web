<?php
// client-manager/functions.php
require 'db.php';

/**
 * Password Hashing
 * @param string $password Plain text password
 * @return string Hashed password
 */
function hash_password($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

/**
 * Verify Password
 * @param string $password Plain text password
 * @param string $hash Hashed password
 * @return bool True if valid
 */
function verify_password($password, $hash) {
    return password_verify($password, $hash);
}

/**
 * Log Activity
 * @param string $action Action description
 * @param string $details Additional details
 */
function log_activity($action, $details = '') {
    $user_id = $_SESSION['user_id'] ?? null;
    $ip = $_SERVER['REMOTE_ADDR'] ?? '';
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
    
    $sql = "INSERT INTO activity_logs (user_id, action, details, ip_address, user_agent) 
            VALUES (?, ?, ?, ?, ?)";
    query($sql, [$user_id, $action, $details, $ip, $user_agent]);
}

/**
 * Generate PDF Receipt
 * @param array $data Receipt data
 * @return string Path to generated PDF
 */
function generate_receipt_pdf($data) {
    require_once 'vendor/autoload.php';
    
    // Create PDF content
    $html = '<!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <title>Receipt #'.$data['id'].'</title>
        <style>
            body { font-family: Arial, sans-serif; }
            .header { text-align: center; margin-bottom: 20px; }
            .status { 
                padding: 5px 10px; 
                border-radius: 4px; 
                font-weight: bold; 
                display: inline-block;
            }
            .status-paid { background-color: #d4edda; color: #155724; }
            .status-unpaid { background-color: #f8d7da; color: #721c24; }
            .status-deposit { background-color: #fff3cd; color: #856404; }
            .table { width: 100%; border-collapse: collapse; margin: 20px 0; }
            .table th, .table td { border: 1px solid #ddd; padding: 8px; }
            .table th { background-color: #f2f2f2; text-align: left; }
            .text-right { text-align: right; }
            .footer { margin-top: 30px; text-align: center; font-size: 0.8em; color: #6c757d; }
        </style>
    </head>
    <body>
        <div class="header">
            <h1>INVOICE RECEIPT</h1>
            <div class="status status-'.$data['status'].'">'.strtoupper($data['status']).'</div>
        </div>
        
        <div class="row">
            <div style="width: 50%; float: left;">
                <strong>Issued To:</strong><br>
                '.htmlspecialchars($data['client_name']).'<br>
                '.htmlspecialchars($data['client_email']).'<br>
                '.htmlspecialchars($data['client_phone']).'
            </div>
            <div style="width: 50%; float: left; text-align: right;">
                <strong>Receipt #:</strong> '.$data['id'].'<br>
                <strong>Issue Date:</strong> '.date('M d, Y', strtotime($data['issue_date'])).'<br>
                <strong>Due Date:</strong> '.date('M d, Y', strtotime($data['due_date'])).'
            </div>
            <div style="clear: both;"></div>
        </div>
        
        <table class="table">
            <thead>
                <tr>
                    <th>Description</th>
                    <th>Amount</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>'.nl2br(htmlspecialchars($data['description'])).'</td>
                    <td class="text-right">$'.number_format($data['amount'], 2).'</td>
                </tr>
                <tr>
                    <td class="text-right"><strong>TOTAL</strong></td>
                    <td class="text-right"><strong>$'.number_format($data['amount'], 2).'</strong></td>
                </tr>
            </tbody>
        </table>
        
        <div class="notes">
            <strong>Notes:</strong><br>
            '.nl2br(htmlspecialchars($data['notes'])).'
        </div>
        
        <div class="footer">
            Generated on '.date('M d, Y H:i').' by Client Manager
        </div>
    </body>
    </html>';
    
    // Generate PDF
    $dompdf = new Dompdf\Dompdf();
    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();
    
    // Save to storage
    $filename = 'receipt_'.$data['id'].'_'.time().'.pdf';
    $filepath = __DIR__.'/storage/receipts/'.$filename;
    
    file_put_contents($filepath, $dompdf->output());
    
    return $filename;
}

/**
 * Export Data to CSV
 * @param array $data Data to export
 * @param string $filename Output filename
 */
function export_csv($data, $filename = 'export.csv') {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="'.$filename.'"');
    
    $output = fopen('php://output', 'w');
    
    // Add header row
    if (!empty($data)) {
        fputcsv($output, array_keys($data[0]));
    }
    
    // Add data rows
    foreach ($data as $row) {
        fputcsv($output, $row);
    }
    
    fclose($output);
    exit;
}

/**
 * Handle File Uploads
 * @param string $field File input field name
 * @param string $targetDir Destination directory
 * @param array $allowedTypes Allowed MIME types
 * @return array [success, message, filename]
 */
function handle_upload($field, $targetDir, $allowedTypes = []) {
    if (!isset($_FILES[$field])) {
        return [false, 'No file uploaded'];
    }
    
    $file = $_FILES[$field];
    
    // Check for errors
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return [false, 'Upload error: '.$file['error']];
    }
    
    // Verify MIME type
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    
    if (!empty($allowedTypes) && !in_array($mime, $allowedTypes)) {
        return [false, 'Invalid file type: '.$mime];
    }
    
    // Generate secure filename
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = bin2hex(random_bytes(16)).'.'.$extension;
    $targetPath = $targetDir.'/'.$filename;
    
    // Move file to target directory
    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
        return [true, 'File uploaded successfully', $filename];
    }
    
    return [false, 'Failed to move uploaded file'];
}

/**
 * Get MIME type for file extension
 * @param string $extension File extension
 * @return string MIME type
 */
function get_mime_type($extension) {
    $mime_types = [
        'txt' => 'text/plain',
        'pdf' => 'application/pdf',
        'doc' => 'application/msword',
        'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'xls' => 'application/vnd.ms-excel',
        'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'png' => 'image/png',
        'gif' => 'image/gif',
        'zip' => 'application/zip'
    ];
    
    $extension = strtolower($extension);
    return $mime_types[$extension] ?? 'application/octet-stream';
}

/**
 * Send JSON Response
 * @param bool $success Operation status
 * @param string $message Response message
 * @param array $data Additional data
 * @param int $statusCode HTTP status code
 */
function json_response($success = true, $message = '', $data = [], $statusCode = 200) {
    header('Content-Type: application/json');
    http_response_code($statusCode);
    
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'data' => $data
    ]);
    exit;
}

/**
 * Get Pagination Parameters
 * @param int $defaultPerPage Default items per page
 * @return array [page, per_page, offset]
 */
function get_pagination_params($defaultPerPage = 20) {
    $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
    $perPage = isset($_GET['per_page']) ? max(1, (int)$_GET['per_page']) : $defaultPerPage;
    $offset = ($page - 1) * $perPage;
    
    return [$page, $perPage, $offset];
}

/**
 * Get Filter Parameters
 * @param array $allowedFilters Allowed filter fields
 * @return array Filter array
 */
function get_filter_params($allowedFilters = []) {
    $filters = [];
    
    foreach ($allowedFilters as $field) {
        if (isset($_GET[$field]) && !empty($_GET[$field])) {
            $filters[$field] = sanitize($_GET[$field]);
        }
    }
    
    return $filters;
}

/**
 * Send Notification
 * @param int $userId Target user ID
 * @param string $message Notification message
 * @param string $type Notification type (info, warning, success, error)
 */
function send_notification($userId, $message, $type = 'info') {
    $sql = "INSERT INTO notifications (user_id, message, type, is_read) 
            VALUES (?, ?, ?, 0)";
    query($sql, [$userId, $message, $type]);
}