<?php
declare(strict_types=1);

class ExportHelper {
    public static function toCSV(array $data, string $filename): string {
        // Create output stream
        $output = fopen('php://temp', 'w');
        
        // Add BOM for UTF-8
        fwrite($output, "\xEF\xBB\xBF");
        
        // Add headers
        if (!empty($data)) {
            fputcsv($output, array_keys($data[0]));
        }
        
        // Add data
        foreach ($data as $row) {
            fputcsv($output, $row);
        }
        
        // Save to file
        $filePath = EXPORT_PATH . '/' . $filename;
        file_put_contents($filePath, stream_get_contents($output, -1, 0));
        fclose($output);
        
        return $filePath;
    }

    public static function toPDF(array $data, string $filename, string $template = 'default'): string {
        // Generate HTML
        $html = self::renderTemplate($template, $data);
        
        // Configure Dompdf
        $options = new Dompdf\Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);
        $options->set('defaultFont', 'DejaVu Sans');
        
        $dompdf = new Dompdf\Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        
        // Save file
        $filePath = EXPORT_PATH . '/' . $filename;
        file_put_contents($filePath, $dompdf->output());
        
        return $filePath;
    }
    
    private static function renderTemplate(string $template, array $data): string {
        ob_start();
        include TEMPLATES_PATH . "/export/$template.php";
        return ob_get_clean();
    }
}