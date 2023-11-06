<?php
//in this the database is connected from a database instance of the database class 
// include_once("app/model/db.php");//check the error and compare with the below path error in the browser 
require_once('model/db.php');//compare the above path with this

$database = new Database();
$query = "SELECT * FROM history ORDER BY update_date DESC";
$results = $database->execute2($query);
$allorders = array();
while ($order = $results->fetch(PDO::FETCH_ASSOC)) {
    $allorders[] = $order;
}

$startDateMessage = "";
$endDateMessage = "";
$noResult = "";

if (isset($_POST["export"])) {
    if (empty($_POST["fromDate"])) {
        $startDateMessage = '<label class="text-danger">Select start date.</label>';
    } else if (empty($_POST["toDate"])) {
        $endDateMessage = '<label class="text-danger">Select end date.</label>';
    } else {
        $query = "SELECT * FROM history WHERE update_date >= ? AND update_date <= ? ORDER BY update_date DESC";
        //$params = array($_POST["fromDate"], $_POST["toDate"]);
        $params = array(":fromDate" => $_POST["fromDate"], ":toDate" => $_POST["toDate"]);
        $orderResult = $database->execute2($query); //use the execute method from the database class 
        $filterorders = $orderResult->fetchAll(PDO::FETCH_ASSOC);

        if (count($filterorders)) {
            $fileName = "DeviceLog_export_" . date('Ymd') . ".csv";
            header("Content-Description: File Transfer");
            header("Content-Disposition: attachment; filename=$fileName");
            header("Content-Type: application/csv;");
            $file = fopen('php://output', 'w');
            $header = array("flow rate", "total_pos_flow", "signal strength", "update_date");
            fputcsv($file, $header);
            foreach ($filterorders as $order) {
                $orderData = array(
                    $order["Flow rate"],
                    $order["total_pos_flow"],
                    $order["signal strength"],
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
