
<?php

// Allow cross-origin requests
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
// ------------------------------------------------------
//action.php
//all being done for getting device id
// $connect = new PDO("mysql:host=localhost;dbname=flowmeter_db", "root", "");
// $redirect = getValue("page", false, "home"); //the default value of the page parameter is set to "home" if it is not provided or is false.
// $page_action = getValue("action", false, null);
// $page_open = "./app/page_" . $redirect . ".php";
// if (file_exists($page_open)) {
// 	require "./app/menu.php";
// 	require $page_open;
// } else {
// 	die("Invalid link specified");
// }
// --------------------------------------------------------
// ----------------------BELOW IS FOR LOGGING DB ERRORS-----------------
// error_reporting(E_ALL);
// ini_set('display_errors', 1);
// $connect->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// require_once(__DIR__ . '/model/db.php'); //uncomment 
require_once('model/db.php');
// ----------------------------------------
// $connect = new PDO("mysql:host=localhost;dbname=flowmeter_db", "flowmeter_user", "s5R,ucJ!)@}W");

// $database = new  Database();

// if (!isset($session_user_type)) {
// 	echo "You cannot directly access this page.";
// 	die();
// }

$connect = new Database();

// Check if the 'action' parameter is set in the POST request
if (isset($_POST["action"])) {
	if ($_POST["action"] == 'fetch') {
		$d_id = $_POST['d_id'];

		$order_column = array('flow_rate', 'total_pos_flow', 'signal_strength', 'update_date');

		$search_text = " WHERE device_id=$d_id ";

		$main_query = "SELECT id as row_id, flow_rate, total_pos_flow, signal_strength, update_date FROM history $search_text";

		$search_query = 'AND update_date <= "' . date('Y-m-d') . '" AND ';

		if (isset($_POST["start_date"], $_POST["end_date"]) && $_POST["start_date"] != '' && $_POST["end_date"] != '') {
			$search_query .= 'update_date >= "' . $_POST["start_date"] . '" AND update_date <= "' . $_POST["end_date"] . '" AND ';
		}

		if (isset($_POST["search"]["value"])) {
			$search_query .= '(flow_rate LIKE "%' . $_POST["search"]["value"] . '%" OR total_pos_flow LIKE "%' . $_POST["search"]["value"] . '%" OR signal_strength LIKE "%' . $_POST["search"]["value"] . '%" OR update_date LIKE "%' . $_POST["search"]["value"] . '%" )';
		}

		$group_by_query = " GROUP BY update_date ";

		$order_by_query = "";

		if (isset($_POST["order"])) {
			$order_by_query = 'ORDER BY ' . $order_column[$_POST['order']['0']['column']] . ' ' . $_POST['order']['0']['dir'] . ' ';
		} else {
			$order_by_query = 'ORDER BY update_date DESC ';
		}

		$limit_query = '';

		if ($_POST["length"] != -1) {
			$limit_query = 'LIMIT ' . $_POST['start'] . ', ' . ($_POST['length'] + 1);
		}

		$statement = ($main_query . $search_query . $group_by_query . $order_by_query);
		$complete_query = ($main_query . $search_query . $group_by_query . $order_by_query . $limit_query);

		$statement = $connect->execute($main_query . $search_query . $group_by_query . $order_by_query);

		$complete_query = $connect->execute($main_query . $search_query . $group_by_query . $order_by_query . $limit_query);

		$filtered_rows = $statement->rowCount();

		$statement = $connect->execute($main_query . $group_by_query);

		$total_rows = $statement->rowCount();

		// $result = $connect->execute($main_query . $search_query . $group_by_query . $order_by_query . $limit_query)->fetchAll(PDO::FETCH_ASSOC);

		$result = $connect->execute($main_query . $search_query . $group_by_query . $order_by_query . $limit_query, PDO::FETCH_ASSOC);

		if ($result->rowCount() == 0) {
			$output = array(
				"error" => "NO DATA FOUND FOR THE SELECTED DATE RANGE"
			);
			echo json_encode($output);
		} else {

			$data = array();

			foreach ($result as $row) {
				$sub_array = array();

				$sub_array[] = $row['flow_rate'];

				$sub_array[] = $row['total_pos_flow'];

				$sub_array[] = $row['signal_strength'];

				$sub_array[] = $row['update_date'];

				$data[] = $sub_array;
			}

			// This code is commonly used in AJAX requests to retrieve data from a database and display it on a web page. 
			$output = array(
				"draw"			=>	intval($_POST["draw"]), //this is used to keep track of no. of request made by the client.
				"recordsTotal"	=>	$total_rows, //for pagination purpose
				"recordsFiltered" => $filtered_rows, //for pagination
				"data"			=>	$data // contains the actual records that will be displayed on the webpage
			);

			echo json_encode($output); //this will echo the output
			// echo json_encode(array("output" => $output, "data" => $data, "result" => $result, "statement" => $statement, "complete_query" => $complete_query, "order_by_query" => $order_by_query, "main_query" => $main_query, "search_query" => $search_query, "group_by_query" => $order_by_query, "limit_query" => $limit_query, "POST" => $_POST));
		}
	}
}

?> 


