<?php

require_once 'C:\xampp\htdocs\flowmonitor\app\model\db.php';
// require_once 'app\model\db.php';//The Download Function is not working when the relative path is used Fix this.
// require_once __DIR__ . '/app/model/db.php';
// require_once 'model/db.php';


function downloadCSV() {
    $database = new Database();
    $stmt = $database->execute("SELECT * FROM history ORDER BY update_date DESC");
    $num = $stmt->rowCount();

    if ($num) {
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Set the content type for a file download
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="export.csv"');
        header('Pragma: no-cache');
        header('Expires: 0');

        // Open a new output stream for the CSV file
        $output = fopen('php://output', 'w');
        $header = array("id","device_id","flow_rate","total_pos_flow","signal_strength","update_time","update_date");
        if ($output && $rows) {
            // Write the CSV header (column names)
            fputcsv($output, array_keys($rows[0]));

            // Write each row to the CSV file
            foreach ($rows as $row) {
                fputcsv($output, $row);
            }
            fclose($output);
        } else {
            http_response_code(500); // Internal Server Error
            echo json_encode(
                array("message" => "Error generating CSV file.", "records" => null)
            );
        }
    } else {
        http_response_code(400);
        echo json_encode(
            array("message" => "No records found for download.", "records" => null)
        );
    }
}

if (isset($_GET['action']) && $_GET['action'] === 'downloadCSV') {
    downloadCSV();
} else {
    // Handle other actions or show an error if the action is not recognized
    echo json_encode(array("message" => "Unknown action. The requested action cannot be performed.", "records" => null));
}
?>

