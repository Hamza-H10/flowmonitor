
<?php
if (!isset($session_user_type)) {
    echo "You cannot directly access this page.";
    die();
}

$database = new Database();

switch ($redirect) {
    case "list_ufm":
        $query = "SELECT device_id, device_number, device_friendly_name, flow_rate, total_pos_flow, signal_strength, device_type, update_time
            FROM
            devices AS d LEFT JOIN history ON device_id=d.id AND update_date=(SELECT max(update_date) FROM history WHERE device_id=d.id) WHERE device_type<>'PIEZOMETER' AND user_id = $session_user_id ORDER BY device_friendly_name
            ";
        $stmt = $database->execute($query);
        $num = $stmt->rowCount();

        if ($num > 0) {

            $results_arr = array();
            $results_arr["records"] = array();
            $results_arr["text_align"] = array('', 'center', 'center', 'left', 'right', 'right', 'right', 'right', 'right', 'right', 'center');
            $curtime = time();

            // retrieve our table contents
            // device_friendly_name, flow_rate, total_pos_flow, signal_strength, last_update
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $time_val = strtotime($row['update_time']);
                $dmy_details = getdmy($row['device_id']);
                $unit_flow = "";
                $unit_totalizer = "";
                getunit($row['device_id'], $unit_flow, $unit_totalizer);
                $device_status = "ONLINE";
                if ($curtime - $time_val > 86400)
                    $device_status = "OFFLINE";
                $device_item = array(
                    "row_id" => $row['device_id'],
                    "device_status" => $device_status,
                    "device_number" => $row['device_number'],
                    "device_name" => "<a href='$app_root/?page=devicelog&device_id=" . $row['device_id'] . "'><i class='history icon'></i>" . html_entity_decode($row['device_friendly_name']) . "</a>",
                    // "device_name" => html_entity_decode($row['device_friendly_name']),
                    "flow_rate_(" . $unit_flow . ")" => $row['flow_rate'],
                    "total_positive_flow_(" . $unit_totalizer . ")" => $row['total_pos_flow'],
                    "daily_(" . $unit_totalizer . ")" => $dmy_details['daily'],
                    "monthly_(" . $unit_totalizer . ")" => $dmy_details['monthly'],
                    "yearly_(" . $unit_totalizer . ")" => $dmy_details['yearly'],
                    "device_type" => $row['device_type'],
                    "signal_strength" => $row['signal_strength'],
                    "last_update" => date("d/m/Y h:i:sa", $time_val),
                );

                array_push($results_arr["records"], $device_item);
            }

            $results_arr["page_limit"] = $num;
            // set response code - 200 OK
            http_response_code(200);

            // show products data in json format
            echo json_encode($results_arr);
        } else {

            // set response code - 200 OK
            http_response_code(200);

            // tell the user no products found
            echo json_encode(
                array("message" => "No devices found.", "records" => null)
            );
        }

        break;

        // ----------------------------------------------------------------------------------------            
    case "device_history":
        $page_limit = 10;
        $page_num =  getValue("pgno", false, 1);
        $d_id = getValue("device_id");
        $unit_flow = "";
        $unit_totalizer = "";
        getunit($d_id, $unit_flow, $unit_totalizer);
        $option = getValue("option", false, '');

        $search_text = " WHERE device_id=$d_id ";  // *** remove WHERE clause if main query includes it

        $stmt = $database->execute("SELECT COUNT(*) AS total_records FROM history $search_text");
        $results_arr = array();
        if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $total_records = $row['total_records']; //this means the value stored in the key 'total_records' in the $row array is assigned to $total_records
            $total_pages = intval($total_records / $page_limit) + (($total_records % $page_limit) ? 1 : 0);
            if ($page_num < 1)
                $page_num = 1;
            elseif ($page_num > $total_pages)
                $page_num = $total_pages;

            //by removing the below lines only this the data table is not showing
            $results_arr["cur_page"] = $page_num;
            $page_start = ($page_num - 1) * $page_limit;
            $results_arr["total_pages"] = $total_pages;
            $results_arr["page_start"] = $page_start;
            $results_arr["total_records"] = $total_records;
        } else {
            http_response_code(404);

            // tell the user no products found
            echo json_encode(
                array("message" => "No records found.", "records" => null)
            );
        }

        //removing the below query the records are getting fetched and pagination and all is working but the dataTable is not showing on the client.  
        //so the below query is only fetching the data on the dataTable
        //check the below query via debugger that how its working. is this query working page by page. 
        $stmt = $database->execute("SELECT id as row_id, flow_rate, total_pos_flow, signal_strength, update_date FROM history $search_text ORDER BY update_date DESC, update_time DESC LIMIT $page_start, $page_limit");
        $num = $stmt->rowCount(); //num = 10 (rows). so, the above query is used to get data for 10 rows for respective page_num


        if ($num > 0) {
            $results_arr["records"] = array(); //by removing this line the dataTable is not working
            $results_arr["text_align"] = array('', 'right', 'right', 'center', 'center');
            //$curtime = time();
            // ---------------------------------------------data table contents are fetched here---------------
            // retrieve our table contents
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                //$last_update = getTimeText($row['date_time'], $curtime);

                $alert_item = array(
                    "row_id" => $row['row_id'],
                    "flow_rate_(" . $unit_flow . ")" => $row['flow_rate'],
                    "total_pos_flow_(" . $unit_totalizer . ")" => $row['total_pos_flow'],
                    "signal_strength" => $row['signal_strength'],
                    "update_date" => date("d/m/Y", strtotime($row['update_date'])),
                );

                array_push($results_arr["records"], $alert_item); //this is forming the whole data Table
            }

            $results_arr["page_limit"] = $num; // this is removing the pagination page indicator on the right side bottom
            // set response code - 200 OK
            http_response_code(200);

            // show products data in json format
            echo json_encode($results_arr); // this line is reflecting data on the client
        } else {
            // set response code - 404 Not found
            http_response_code(404);

            // tell the user no products found
            echo json_encode(
                array("message" => "No history found.", "records" => null)
            );
        }
        break;

        //  ------------------------------------------------------------------------------------------           
    case "device_history_print":
        $d_id = getValue("device_id");
        $unit_flow = "";
        $unit_totalizer = "";
        getunit($d_id, $unit_flow, $unit_totalizer);

        // Initialize the search conditions
        $search_conditions = "WHERE device_id = $d_id";

        // Check if a date range is provided
        if (isset($_GET['fromDate']) && isset($_GET['toDate'])) {
            $fromDate = $_GET['fromDate'];
            $toDate = $_GET['toDate'];

            // Validate the date range format using DateTime
            $fromDateTime = DateTime::createFromFormat('Y-m-d', $fromDate);
            $toDateTime = DateTime::createFromFormat('Y-m-d', $toDate);


            // Validate the date range format (you may want to add further validation)
            if ($fromDateTime && $toDateTime) {
                $fromDate = $fromDateTime->format('Y-m-d');
                $toDate = $toDateTime->format('Y-m-d');
                // Add date range conditions to the query
                $search_conditions .= " AND update_date >= '$fromDate' AND update_date <= '$toDate'";
            }
        }

        $stmt = $database->execute("SELECT id as row_id, flow_rate, total_pos_flow, signal_strength, update_date FROM history $search_conditions ORDER BY update_date DESC, update_time DESC");
        $num = $stmt->rowCount();

        if ($num > 0) {
            $results_arr["records"] = array();
            $results_arr["text_align"] = array('', 'right', 'right', 'center', 'center');

            // Retrieve our table contents
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $alert_item = array(
                    "row_id" => $row['row_id'],
                    "flow_rate_(" . $unit_flow . ")" => $row['flow_rate'],
                    "total_pos_flow_(" . $unit_totalizer . ")" => $row['total_pos_flow'],
                    "signal_strength" => $row['signal_strength'],
                    "update_date" => date("d/m/Y", strtotime($row['update_date'])),
                );

                array_push($results_arr["records"], $alert_item);
            }

            $results_arr["page_limit"] = $num;

            http_response_code(200);

            // Show products data in JSON format
            echo json_encode($results_arr);
        } else {

            http_response_code(404);
            // Tell the user no products found
            echo json_encode(
                array("message" => "No history found.", "records" => null)
            );
        }
        break;

        // ---------------------------------------------------------                  
    default:
        // set response code - 404 Not found
        http_response_code(404);

        // tell the user no products found
        echo json_encode(
            array("message" => "Invalid Link Specified.", "records" => null)
        );
}
/**
 * This code retrieves data from the database based on the provided redirect parameter.
 * It fetches device information and history for a specific device.
 * The retrieved data is then formatted and returned as JSON.
 * If no data is found, appropriate error messages are returned.
 */
?>