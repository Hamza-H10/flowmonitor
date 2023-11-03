<?php


try {
	$conn1 = new PDO("mysql:host=localhost;dbname=flowmeter_db", "root", "");
	$conn1->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
	die("Connection failed: " . $e->getMessage());
}

// retrieve our table contents
if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
	$device_name = $row["device_friendly_name"];
	$device_number = $row["device_number"];
}

$startDateMessage = '';
$endDateMessage = '';
$noResult = '';

if (isset($_POST["export"])) {
	if (empty($_POST["fromDate"])) {
		$startDateMessage = '<label class="text-danger">Select start date.</label>';
	} else if (empty($_POST["toDate"])) {
		$endDate = '<label class="text-danger">Select end date.</label>';
	} else {
		$fromDate = $_POST["fromDate"];
		$toDate = $_POST["toDate"];

		$query = "SELECT * FROM history WHERE update_date >= :fromDate AND update_date <= :toDate ORDER BY update_date DESC";
		// $stmt = $conn1->prepare($query);
		$stmt = $conn1->prepare($query);
		$stmt->bindParam(":fromDate", $fromDate);
		$stmt->bindParam(":toDate", $toDate);
		$stmt->execute();
		$filterRows = $stmt->fetchAll(PDO::FETCH_ASSOC);

		if (count($filterRows)) {
			$fileName = "DeviceLog_export_" . date('Ymd') . ".csv";
			header("Content-Description: File Transfer");
			header("Content-Disposition: attachment; filename=$fileName");
			header("Content-Type: application/csv;");
			$fileCSV = fopen('php://output', 'w');
			$header = array("flow_rate", "total_pos_flow", "signal_strength", "update_date");
			fputcsv($fileCSV, $header);
			foreach ($filterRows as $rows) {
				$deviceLogData = array(
					$rows["flow_rate"],
					$rows["total_pos_flow"],
					$rows["signal_strength"],
					$rows["update_date"]
				);
				fputcsv($fileCSV, $deviceLogData);
			}
			fclose($fileCSV);
			exit;
		} else {
			$noResult = '<label class="text-danger">There are no records within this date range to export. Please choose a different date range.</label>';
		}
	}
}
