
<?php
// -----------------------my changes / uncomment this and then make test your functionalities
if (!isset($session_user_type)) {
    echo "You cannot directly access this page.";
    die();
}

require_once 'C:\xampp\htdocs\flowmonitor\app\model\db.php';
// -------------------------


$database = new Database();

switch ($redirect) {
    case "user_fetch":
        $edit_id = getValue("row_id");
        $stmt = $database->execute("SELECT id as row_id, display_name, user_email, user_type, unit_flow, unit_totalizer, remarks FROM users WHERE id=" . $edit_id);

        http_response_code(200);
        if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $results_arr["status"] = 'success';

            $results_arr["display_name"] = $row['display_name'];
            $results_arr["user_email"] = $row['user_email'];
            $results_arr["user_type"] = $row['user_type'];
            $results_arr["unit_flow"] = $row['unit_flow'];
            $results_arr["unit_totalizer"] = $row['unit_totalizer'];
            $results_arr["remarks"] = $row["remarks"];

            // show products data in json format
            echo json_encode($results_arr);
        } else {
            $results_arr["status"] = 'error';
            $results_arr["message"] = 'Unable to fetch record.';

            echo json_encode($results_arr);
        }
        break;
    case "user_add":
    case "user_edit":
        $display_name = getValue("display_name");
        $user_email = getValue("user_email");
        $user_type = getValue("user_type");
        $unit_flow = getValue("unit_flow");
        $unit_totalizer = getValue("unit_totalizer");
        $remarks = getValue("remarks", false, "");
        $extra_condition = "";
        if ($redirect === 'user_edit') {
            $user_password = getValue("user_password", false, "");
            if ($user_password != "")
                $user_password = hashPassword($user_password);
            $edit_id = getValue("row_id");
            $extra_condition = " AND id <> $edit_id";
        } else {
            $user_password = hashPassword(getValue("user_password"));
        }
        $stmt = $database->execute("SELECT id FROM users WHERE user_email='$user_email' $extra_condition");

        if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {

            http_response_code(200);

            // tell the user email found
            echo json_encode(
                array("status" => "error", "message" => "Email address already exists.", "records" => "")
            );
            break;
        } else {
            if ($redirect === 'user_edit') {
                if ($user_password == "")
                    $stmt = $database->execute("UPDATE users SET display_name='$display_name', user_email='$user_email', user_type=$user_type, unit_flow='$unit_flow', unit_totalizer='$unit_totalizer', remarks='$remarks' WHERE id=" . $edit_id);
                else
                    $stmt = $database->execute("UPDATE users SET display_name='$display_name', user_email='$user_email', login_password='$user_password', user_type=$user_type, unit_flow='$unit_flow', unit_totalizer='$unit_totalizer', remarks='$remarks' WHERE id=" . $edit_id);
            } else {
                $stmt = $database->execute("INSERT INTO users (display_name, user_email, login_password, user_type, unit_flow, unit_totalizer, remarks) VALUES ('$display_name', '$user_email', '$user_password', $user_type, '$unit_flow', '$unit_totalizer', '$remarks')");
            }
            $num = $stmt->rowCount();
            if ($num) {
                http_response_code(200);

                echo json_encode(
                    array("status" => "success", "message" => ".", "records" => "")
                );
            } else {
                http_response_code(400);
                echo json_encode(
                    array("message" => "Record could not be modified.", "records" => "")
                );
            }
        }
        break;
    case "user_delete":
        $del_id =  getValue("del_id");
        $stmt = $database->execute("DELETE FROM users WHERE id IN ($del_id)");
        $num = $stmt->rowCount();

        if ($num) {
        } else {
            http_response_code(400);

            // tell the user no products found
            echo json_encode(
                array("message" => "Records could not be deleted.", "records" => null)
            );
            break;
        }
        //break;
    case "user_list":
        $page_limit = 10;
        $page_num =  getValue("pgno", false, 1);
        $search_text = getValue("search", false, '');
        $option = getValue("option", false, '');
        if ($search_text != '') {
            $search_text = " WHERE (display_name LIKE '%$search_text%' OR user_email LIKE '%$search_text%') ";  // *** remove WHERE clause if main query includes it
        } else
            $search_text = " ";

        $stmt = $database->execute("SELECT COUNT(*) AS total_records FROM users $search_text");
        $results_arr = array();
        if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $total_records = $row['total_records'];
            $total_pages = intval($total_records / $page_limit) + (($total_records % $page_limit) ? 1 : 0);
            if ($page_num < 1)
                $page_num = 1;
            elseif ($page_num > $total_pages)
                $page_num = $total_pages;

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

        $stmt = $database->execute("SELECT id as row_id, display_name, user_email, user_type, unit_flow, unit_totalizer, remarks, created_date FROM users $search_text LIMIT $page_start, $page_limit");
        $num = $stmt->rowCount();

        if ($num > 0) {

            $results_arr["records"] = array();
            $results_arr["text_align"] = array('', 'left', 'center', 'left', 'center', 'center', 'left', 'center');
            //$curtime = time();

            // retrieve our table contents
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                //$last_update = getTimeText($row['date_time'], $curtime);

                $user_type = ($row['user_type'] == 1) ? "Admin" : "User";
                $alert_item = array(
                    "row_id" => $row['row_id'], // $row  is an array that contains some data, and  row_id  is one of the keys in that array. The code is retrieving the value associated with the key  row_id  from the  $row  array. 
                    "display_name" => html_entity_decode($row['display_name']),
                    "devices" => "<a href='$app_root/?page=devices&user_id=" . $row['row_id'] . "'><i class='microchip icon'></i></a>",
                    "user_email" => $row['user_email'],
                    "user_type" => $user_type,
                    "unit_flow" => $row['unit_flow'],
                    "unit_totalizer" => $row['unit_totalizer'],
                    "remarks" => $row['remarks'],
                    "created_date" => date("d/m/Y h:i:sa", strtotime($row['created_date'])),
                );

                array_push($results_arr["records"], $alert_item);
            }

            $results_arr["page_limit"] = $num;
            // set response code - 200 OK
            http_response_code(200);

            // show products data in json format
            echo json_encode($results_arr);
        } else {

            // set response code - 404 Not found
            http_response_code(404);

            // tell the user no products found
            echo json_encode(
                array("message" => "No users found.", "records" => null)
            );
        }

        break;

    case "devices_fetch":
        $edit_id = getValue("row_id");
        $stmt = $database->execute("SELECT id as row_id, device_friendly_name, device_number, device_type, dev_x, dev_y, user_id FROM devices WHERE id=" . $edit_id);

        http_response_code(200);
        if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $results_arr["status"] = 'success';

            $results_arr["device_name"] = $row['device_friendly_name'];
            $results_arr["device_number"] = $row['device_number'];
            $results_arr["device_type"] = $row['device_type'];
            $results_arr["device_x"] = $row["dev_x"];
            $results_arr["device_y"] = $row["dev_y"];
            $results_arr["user_id"] = $row["user_id"];

            // show products data in json format
            echo json_encode($results_arr);
        } else {
            $results_arr["status"] = 'error';
            $results_arr["message"] = 'Unable to fetch record.';

            echo json_encode($results_arr);
        }
        break;
    case "devices_edit":
    case "devices_add":
        $device_name = getValue("device_name");
        $device_number = getValue("device_number");
        $device_type = getValue("device_type");
        $device_x = getValue("device_x");
        $device_y = getValue("device_y");
        $u_id = getValue("user_id");

        $extra_condition = "";
        if ($redirect === 'devices_edit') {
            $edit_id = getValue("row_id");
            $extra_condition = " AND id <> $edit_id";
        } else {
        }
        $stmt = $database->execute("SELECT id FROM devices WHERE device_number='$device_number' $extra_condition");

        if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {

            http_response_code(200);

            // tell the user email found
            echo json_encode(
                array("status" => "error", "message" => "Device Number already exists.", "records" => "")
            );
            break;
        } else {
            if ($redirect === 'devices_edit') {
                $query = "UPDATE devices SET device_friendly_name='$device_name', device_number='$device_number', device_type=$device_type, dev_x=$device_x, dev_y=$device_y, user_id=$u_id WHERE id=" . $edit_id;
                $stmt = $database->execute($query);
            } else {
                $stmt = $database->execute("INSERT INTO devices (device_friendly_name, device_number, device_type, dev_x, dev_y, user_id) VALUES ('$device_name', '$device_number', $device_type, $device_x, $device_y, $u_id)");
            }
            $num = $stmt->rowCount();
            if ($num) {
                http_response_code(200);

                echo json_encode(
                    array("status" => "success", "message" => ".", "records" => "")
                );
            } else {
                http_response_code(400);
                echo json_encode(
                    array("message" => "Record could not be modified.", "records" => "")
                );
            }
        }
        break;

    case "devices_delete":
        $del_id =  getValue("del_id");
        $database->execute("DELETE FROM history WHERE device_id IN ($del_id)");
        $stmt = $database->execute("DELETE FROM devices WHERE id IN ($del_id)");
        $num = $stmt->rowCount();

        if ($num) {
        } else {
            http_response_code(400);

            // tell the user no products found
            echo json_encode(
                array("message" => "Records could not be deleted.", "records" => null)
            );
            break;
        }
        //break;

    case "list_ufm":
        $page_num =  getValue("pgno", false, 1);
        $u_id = getValue("user_id", false, 0);
        $page_limit = 10;

        $filter_text = "";
        if ($u_id) {
            $filter_text = " AND d.user_id=$u_id ";
            $stmt = $database->execute("SELECT COUNT(*) AS total_records FROM devices WHERE device_type<>'PIEZOMETER' AND user_id=" . $u_id);
        } else
            $stmt = $database->execute("SELECT COUNT(*) AS total_records FROM devices WHERE device_type<>'PIEZOMETER'");

        $results_arr = array();
        if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $total_records = $row['total_records'];
            $total_pages = intval($total_records / $page_limit) + (($total_records % $page_limit) ? 1 : 0);
            if ($page_num < 1)
                $page_num = 1;
            elseif ($page_num > $total_pages)
                $page_num = $total_pages;

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

        $query = "SELECT d.id as did, device_number, device_friendly_name, flow_rate, total_pos_flow, signal_strength, device_type, update_time
            FROM
            devices AS d LEFT JOIN history ON device_id=d.id AND update_date=(SELECT max(update_date) FROM history WHERE device_id=d.id) WHERE device_type<>'PIEZOMETER' $filter_text ORDER BY device_friendly_name  LIMIT $page_start, $page_limit
            ";
        $stmt = $database->execute($query);
        $num = $stmt->rowCount();

        if ($num > 0) {
            $results_arr["records"] = array();
            $results_arr["text_align"] = array('', 'center', 'center', 'left', 'right', 'right', 'right', 'right', 'right', 'right', 'center');
            $curtime = time();

            // retrieve our table contents
            // device_friendly_name, flow_rate, total_pos_flow, signal_strength, last_update
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $time_val = strtotime($row['update_time']);
                $dmy_details = getdmy($row['did']);
                $unit_flow = "";
                $unit_totalizer = "";
                getunit($row['did'], $unit_flow, $unit_totalizer);
                $device_status = "ONLINE";
                if ($curtime - $time_val > 86400)
                    $device_status = "OFFLINE";
                $device_item = array(
                    "row_id" => $row['did'],
                    "device_status" => $device_status,
                    "device_number" => $row['device_number'],
                    // "device_name" => ,
                    "device_name" => "<a href='$app_root/?page=devicelog&device_id=" . $row['did'] . "'><i class='history icon'></i>" . html_entity_decode($row['device_friendly_name']) . "</a>",
                    "flow_rate" => $row['flow_rate'] . " " . $unit_flow,
                    "total_positive_flow" => $row['total_pos_flow'] . " " . $unit_totalizer,
                    "daily" => $dmy_details['daily'] . " " . $unit_totalizer,
                    "monthly" => $dmy_details['monthly'] . " " . $unit_totalizer,
                    "yearly" => $dmy_details['yearly'] . " " . $unit_totalizer,
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

    case "history_fetch":
        $edit_id = getValue("row_id"); // Get the value of `row_id` from the request
        $stmt = $database->execute("SELECT flow_rate, total_pos_flow, signal_strength, update_date FROM history WHERE id=".$edit_id);
        // ----------------------
        //$stmt = $database->execute("SELECT flow_rate, total_pos_flow, signal_strength, update_date FROM history");
        // ----------------------
        http_response_code(200); // Set the HTTP response code to 200 (OK)

        if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $results_arr["status"] = 'success';
            $results_arr["flow_rate"] = $row['flow_rate']; // Assign the 'flow_rate' value from the fetched row
            $results_arr["total_pos_flow"] = $row['total_pos_flow']; // Assign the 'total_pos_flow' value from the fetched row
            $results_arr["signal_strength"] = $row['signal_strength']; // Assign the 'signal_strength' value from the fetched row
            $results_arr["update_date"] = $row["update_date"]; // Assign the 'update_date' value from the fetched row

            // Show products data in JSON format
            echo json_encode($results_arr);
        } else {
            $results_arr["status"] = 'error';
            $results_arr["message"] = 'Unable to fetch record.';
            echo json_encode($results_arr);
        }
        break;
    case "history_edit":
    case "history_add":
        $flow_rate = getValue("flow_rate");
        $total_pos_flow = getValue("total_pos_flow");
        $signal_strength = getValue("signal_strength");
        $update_date = getValue("update_date");
        $d_id = getValue("device_id");

        $extra_condition = "";
        if ($redirect === 'history_edit') {
            $edit_id = getValue("row_id");
            $extra_condition = " AND id <> $edit_id";
        } else {
        }
        $stmt = $database->execute("SELECT id FROM history WHERE device_id=$d_id AND update_date='$update_date' $extra_condition");

        if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {

            http_response_code(200);

            echo json_encode(
                array("status" => "error", "message" => "Entry for Update Date of selected device already exists.", "records" => "")
            );
            break;
        } else {
            if ($redirect === 'history_edit') {
                $query = "UPDATE history SET flow_rate=$flow_rate, total_pos_flow=$total_pos_flow, signal_strength=$signal_strength, update_date='$update_date' WHERE id=" . $edit_id;
                $stmt = $database->execute($query);
            } else {
                $stmt = $database->execute("INSERT INTO history (device_id, flow_rate, total_pos_flow, signal_strength, update_date) VALUES ($d_id, $flow_rate, $total_pos_flow, $signal_strength, '$update_date')");
            }
            $num = $stmt->rowCount();
            if ($num) {
                http_response_code(200);

                echo json_encode(
                    array("status" => "success", "message" => ".", "records" => "")
                );
            } else {
                http_response_code(400);
                echo json_encode(
                    array("message" => "Record could not be modified.", "records" => "")
                );
            }
        }
        break;

    case "history_delete":
        // Get the ID(s) to be deleted
        $del_id = getValue("del_id");

        // Execute the delete query
        $stmt = $database->execute("DELETE FROM history WHERE id IN ($del_id)");

        // Get the number of affected rows
        $num = $stmt->rowCount();

        if ($num) {
            // If records were deleted successfully, do something here
        } else {
            // If no records were deleted, return an error message
            http_response_code(400);
            echo json_encode(
                array("message" => "Records could not be deleted.", "records" => null)
            );
            break;
        }

        // -------------------------
        //switch($redirect) {
    case "history_download":
        $stmt = $database->execute("SELECT * FROM history");
        $num = $stmt->rowCount();

        if ($num) {
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            // Set the content type for a file download
            $fileName = "DeviceLog_export_" . date('Ymd') . ".csv";
            header('Content-Type: text/csv');
            header("Content-Disposition: attachment; filename=$fileName");
            header("Content-Type: application/csv;");
            // header('Pragma: no-cache');
            // header('Expires: 0');
            // Open a new output stream for the CSV file
            $output = fopen('php://output', 'w');
            $header = array("flow_rate", "total_pos_flow", "signal_strength", "update_date");
            
            if ($output && $rows) {
                // Write the CSV header (column names)
                fputcsv($output, array_keys($rows[0]));
                $headerData = array(
                    $order["flow_rate"],
                    $order["total_pos_flow"],
                    $order["signal_strength"],
                    $order["update_date"]
                );
                // Write each row to the CSV file
                foreach ($rows as $row) {
                    fputcsv($output, $headerData);
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
        break;
        // --------------------------------

    case "device_history":
        $page_limit = 10;
        $page_num =  getValue("pgno", false, 1);
        $d_id = getValue("device_id");
        $option = getValue("option", false, '');
        $unit_flow = "";
        $unit_totalizer = "";
        getunit($d_id, $unit_flow, $unit_totalizer);

        $search_text = " WHERE device_id=$d_id ";  // *** remove WHERE clause if main query includes it

        $stmt = $database->execute("SELECT COUNT(*) AS total_records FROM history $search_text");
        $results_arr = array();
        if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $total_records = $row['total_records'];
            $total_pages = intval($total_records / $page_limit) + (($total_records % $page_limit) ? 1 : 0);
            if ($page_num < 1)
                $page_num = 1;
            elseif ($page_num > $total_pages)
                $page_num = $total_pages;

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
        //the below query is working fine have checked in the php my admin replacing the placeholders
        $stmt = $database->execute("SELECT id as row_id, flow_rate, total_pos_flow, signal_strength, update_date FROM history $search_text ORDER BY update_date DESC, update_time DESC LIMIT $page_start, $page_limit");
        $num = $stmt->rowCount();

        if ($num > 0) {

            $results_arr["records"] = array();
            $results_arr["text_align"] = array('', 'right', 'right', 'center', 'center');
            //$curtime = time();

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

                array_push($results_arr["records"], $alert_item);
            }

            $results_arr["page_limit"] = $num;
            // set response code - 200 OK
            http_response_code(200);

            // show products data in json format
            echo json_encode($results_arr);
        } else {

            // set response code - 404 Not found
            http_response_code(404);

            // tell the user no products found
            echo json_encode(
                array("message" => "No history found.", "records" => null)
            );
        }

        break;

    default:
        // set response code - 404 Not found
        http_response_code(404);

        // tell the user no products found
        echo json_encode(
            array("message" => "Invalid Link Specified.", "records" => null)
        );
}
/**
 * This file contains the code for handling various operations related to the admin section.
 * It includes functions for fetching, adding, editing, and deleting user records and device records.
 * The code interacts with a database and returns JSON responses.
 */
?>


