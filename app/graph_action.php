
<?php
//action.php
// $connect = new PDO("mysql:host=localhost;dbname=", "root", "");
// graph_action.php
require_once(__DIR__ . '/model/db.php');


    $d_id = getValue('device_id',false,0);
    $database = new Database();
    $stmt = $database->execute("SELECT device_number, device_friendly_name FROM devices WHERE id=".$d_id);
        
    // retrieve our table contents
    if ($row = $stmt->fetch(PDO::FETCH_ASSOC)){
        $device_name = $row["device_friendly_name"];
        $device_number = $row["device_number"];
    }


if(isset($_POST["action"]))
{
	if($_POST["action"] == 'fetch')
	{
		// $order_column = array('order_number', 'order_total', 'order_date');
        $order_column = array('flow_rate', 'total_pos_flow', 'signal_strength', 'update_date');

		$main_query = "
		SELECT order_number, SUM(order_total) AS order_total, order_date 
		FROM test_order_table 
		";
        
		$search_query = 'WHERE order_date <= "'.date('Y-m-d').'" AND ';

		if(isset($_POST["start_date"], $_POST["end_date"]) && $_POST["start_date"] != '' && $_POST["end_date"] != '')
		{
			$search_query .= 'order_date >= "'.$_POST["start_date"].'" AND order_date <= "'.$_POST["end_date"].'" AND ';
		}

		if(isset($_POST["search"]["value"]))
		{
			$search_query .= '(order_number LIKE "%'.$_POST["search"]["value"].'%" OR order_total LIKE "%'.$_POST["search"]["value"].'%" OR order_date LIKE "%'.$_POST["search"]["value"].'%")';
		}



		$group_by_query = " GROUP BY order_date ";

		$order_by_query = "";

		if(isset($_POST["order"]))
		{
			$order_by_query = 'ORDER BY '.$order_column[$_POST['order']['0']['column']].' '.$_POST['order']['0']['dir'].' ';
		}
		else
		{
			$order_by_query = 'ORDER BY order_date DESC ';
		}

		$limit_query = '';

		if($_POST["length"] != -1)
		{
			$limit_query = 'LIMIT ' . $_POST['start'] . ', ' . $_POST['length'];
		}

		$statement = $connect->prepare($main_query . $search_query . $group_by_query . $order_by_query);

		$statement->execute();

		$filtered_rows = $statement->rowCount();

		$statement = $connect->prepare($main_query . $group_by_query);

		$statement->execute();

		$total_rows = $statement->rowCount();

		$result = $connect->query($main_query . $search_query . $group_by_query . $order_by_query . $limit_query, PDO::FETCH_ASSOC);

		$data = array();

		foreach($result as $row)
		{
			$sub_array = array();

			$sub_array[] = $row['order_number'];

			$sub_array[] = $row['order_total'];

			$sub_array[] = $row['order_date'];

			$data[] = $sub_array;
		}

		$output = array(
			"draw"			=>	intval($_POST["draw"]),
			"recordsTotal"	=>	$total_rows,
			"recordsFiltered" => $filtered_rows,
			"data"			=>	$data
		);

		echo json_encode($output);
	}
}
?>