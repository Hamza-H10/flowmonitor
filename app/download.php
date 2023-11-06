<?php
try {
    $conn1 = new PDO("mysql:host=localhost;dbname=flowmeter_db", "root", "");
    $conn1->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// --------------------------------------------
$d_id = getValue('device_id', false, 0);
$database = new Database();
$stmt = $database->execute("SELECT device_number, device_friendly_name FROM devices WHERE id=" . $d_id);

// retrieve our table contents
if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
  $device_name = $row["device_friendly_name"];
  $device_number = $row["device_number"];
}
// --------------------------------------------

function downloadCSV() {
    global $conn1; // Access the PDO connection
    $stmt = $conn1->prepare("SELECT * FROM history ORDER BY update_date DESC");
    $stmt->execute();

    $num = $stmt->rowCount();
    
    if ($num) {
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $fileName = "DeviceLog_export_" . date('Ymd') . ".csv";
        header("Content-Description: File Transfer");
        header("Content-Disposition: attachment; filename=$fileName");
        header("Content-Type: application/csv;");

        // Open a new output stream for the CSV file
        $output = fopen('php://output', 'w');

        // Define the headers exactly as in the provided code
        $header = array("flow_rate(M3/hr)", "total_pos_flow", "signal_strength", "update_date");

        if ($output && $rows) {
            // Write the CSV header (column names)
            fputcsv($output, $header);

            // Write each row to the CSV file
            foreach ($rows as $row) {
                // Generate data array matching the headers
                $data = array(
                    $row["flow_rate"],
                    $row["total_pos_flow"],
                    $row["signal_strength"],
                    $row["update_date"]
                );
                fputcsv($output, $data);
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
