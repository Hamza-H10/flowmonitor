<?php
//This file has its own database credentials created here.
//when the website will be live this may not work because the conn1ection string is not properly to that
//include_once("app/model/db.php"); // Include the Database class file

try {
	$conn1 = new PDO("mysql:host=localhost;dbname=flowmeter_db", "root", "");
	$conn1->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
	die("Connection failed: " . $e->getMessage());
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
		$filterOrders = $stmt->fetchAll(PDO::FETCH_ASSOC);

		if (count($filterOrders)) {
			$fileName = "DeviceLog_export_" . date('Ymd') . ".csv";
			header("Content-Description: File Transfer");
			header("Content-Disposition: attachment; filename=$fileName");
			header("Content-Type: application/csv;");
			$file = fopen('php://output', 'w');
			$header = array("flow_rate", "total_pos_flow", "signal_strength", "update_date");
			//fputcsv($file, $header);
			fputcsv($file, $header);
			foreach ($filterOrders as $order) {
				$orderData = array(
					$order["flow_rate"],
					$order["total_pos_flow"],
					$order["signal_strength"],
					$order["update_date"]
				);
				fputcsv($file, $orderData);
			}
			fclose($file);
			exit;
		} else {
			$noResult = '<label class="text-danger">There are no records within this date range to export. Please choose a different date range.</label>';
		}
	}
}
