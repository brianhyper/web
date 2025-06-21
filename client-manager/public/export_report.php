<?php
require '../app.php';
authenticate(['admin', 'staff']);

// Get parameters
$type = $_GET['type'] ?? 'financial';
$start = $_GET['start'] ?? date('Y-m-d', strtotime('-30 days'));
$end = $_GET['end'] ?? date('Y-m-d');
$format = $_GET['format'] ?? 'csv'; // 'csv' or 'pdf'

// Fetch data (example: financial report)
$stmt = $pdo->prepare("
    SELECT 
        DATE(created_at) AS date,
        SUM(CASE WHEN type = 'income' THEN amount ELSE 0 END) AS income,
        SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END) AS expense
    FROM transactions
    WHERE DATE(created_at) BETWEEN ? AND ?
    GROUP BY DATE(created_at)
    ORDER BY date ASC
");
$stmt->execute([$start, $end]);
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

$filename = "{$type}_report_{$start}_to_{$end}." . $format;

if ($format === 'csv') {
    // CSV Export
    header('Content-Type: text/csv');
    header("Content-Disposition: attachment; filename=\"$filename\"");

    $output = fopen('php://output', 'w');
    // Header row
    fputcsv($output, ['#', 'Date', 'Income', 'Expense']);
    foreach ($data as $i => $row) {
        fputcsv($output, [
            $i + 1,
            $row['date'],
            number_format($row['income'], 2),
            number_format($row['expense'], 2)
        ]);
    }
    fclose($output);
    exit;
} elseif ($format === 'pdf') {
    // PDF Export (requires TCPDF via Composer)
    require_once('../vendor/autoload.php');
    $pdf = new \TCPDF();
    $pdf->SetCreator('Client Manager');
    $pdf->SetAuthor('Client Manager');
    $pdf->SetTitle('Report');
    $pdf->AddPage();

    // Simple CSS styling
    $html = '
    <style>
        h2 { color: #1a73e8; }
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #ddd; padding: 8px; }
        th { background: #1a73e8; color: #fff; }
        tr:nth-child(even) { background: #f2f2f2; }
    </style>
    <h2>Financial Report</h2>
    <p>Period: ' . htmlspecialchars($start) . ' to ' . htmlspecialchars($end) . '</p>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Date</th>
                <th>Income</th>
                <th>Expense</th>
            </tr>
        </thead>
        <tbody>
    ';
    foreach ($data as $i => $row) {
        $html .= '<tr>
            <td>' . ($i + 1) . '</td>
            <td>' . htmlspecialchars($row['date']) . '</td>
            <td>$' . number_format($row['income'], 2) . '</td>
            <td>$' . number_format($row['expense'], 2) . '</td>
        </tr>';
    }
    $html .= '</tbody></table>';

    $pdf->writeHTML($html, true, false, true, false, '');
    $pdf->Output($filename, 'D');
    exit;
} else {
    echo "Invalid format.";
    exit;
}