<?php
$salt1 = "qm&h*bZ";
$salt2 = "pg!A@M";

if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on')
    $url = "https://";
else
    $url = "http://";
// Append the host(domain name, ip) to the URL.   
$url .= $_SERVER['HTTP_HOST'];

$app_root =  $url . "/flowmonitor";

class Database
{
    private $host = "localhost";
    private $db_name = "flowmeter_db";
    private $username = "flowmeter_user";
    private $password = "s5R,ucJ!)@}W";
    // database connection and table name
    private $conn;

    // constructor with database connection
    public function __construct()
    {
        $this->conn = null;

        try {
            $this->conn = new PDO("mysql:host=" . $this->host . ";dbname=" . $this->db_name, $this->username, $this->password);
            $this->conn->exec("set names utf8");
            $this->conn->exec("SET @@session.time_zone = '+05:30';"); //this ensures that any date and time operations performed in the database will consider this time zone.
        } catch (PDOException $exception) {
            echo "Connection error: " . $exception->getMessage();
        }
    }
    public function execute($query)
    {

        // prepare query statement
        $stmt = $this->conn->prepare($query);

        // execute query
        $stmt->execute();

        return $stmt;
    }
    public function lastinsertid()
    {
        return $this->conn->lastInsertId();
    }

    // public function execute2($query, $params = array())
    // {
    //     $stmt = $this->conn->prepare($query);

    //     // If parameters are provided, bind them to the statement
    //     if (!empty($params)) {
    //         foreach ($params as $key => &$val) {
    //             $stmt->bindParam($key, $val);
    //         }
    //     }
    //     $stmt->execute();

    //     return $stmt;
    // }
}
function hashPassword($password)
{
    global $salt1, $salt2;
    return hash('SHA1', "$salt1$password$salt2");
}

function getValue($value_name, $required = true, $default = null)
{
    if (isset($_REQUEST[$value_name])) {
        return filter_var($_REQUEST[$value_name], FILTER_SANITIZE_STRING);
    } else {
        if ($required) {
            http_response_code(400);

            // tell the user no products found
            echo json_encode(
                array("message" => "Required parameter '$value_name'.")
            );
            die();
        } else {
            return $default;
        }
    }
}

function getdmy($device_id)
{
    global $database;
    $min_day = $max_day = $min_month = $max_month = $min_year = $max_year = -1;

    $stmt = $database->execute("SELECT total_pos_flow FROM history WHERE device_id = $device_id AND update_date = CURRENT_DATE() - interval 2 day");
    if ($row = $stmt->fetch(PDO::FETCH_ASSOC))
        $min_day = $row["total_pos_flow"];
    $stmt = $database->execute("SELECT total_pos_flow FROM history WHERE device_id = $device_id AND update_date = CURRENT_DATE() - interval 1 day");
    if ($row = $stmt->fetch(PDO::FETCH_ASSOC))
        $max_day = $row["total_pos_flow"];

    $stmt = $database->execute("SELECT total_pos_flow FROM history WHERE device_id = $device_id AND update_date = (SELECT min(update_date) FROM history WHERE device_id = $device_id AND month(update_date) = MONTH(CURRENT_DATE())-1 AND Year(update_date) = year(CURRENT_DATE()) )");
    if ($row = $stmt->fetch(PDO::FETCH_ASSOC))
        $min_month = $row["total_pos_flow"];
    $stmt = $database->execute("SELECT total_pos_flow FROM history WHERE device_id = $device_id AND update_date = (SELECT max(update_date) FROM history WHERE device_id = $device_id AND month(update_date) = MONTH(CURRENT_DATE())-1 AND Year(update_date) = year(CURRENT_DATE()) )");
    if ($row = $stmt->fetch(PDO::FETCH_ASSOC))
        $max_month = $row["total_pos_flow"];

    $stmt = $database->execute("SELECT total_pos_flow FROM history WHERE device_id = $device_id AND update_date = (SELECT min(update_date) FROM history WHERE device_id = $device_id AND Year(update_date) = Year(CURRENT_DATE())-1)");
    if ($row = $stmt->fetch(PDO::FETCH_ASSOC))
        $min_year = $row["total_pos_flow"];
    $stmt = $database->execute("SELECT total_pos_flow FROM history WHERE device_id = $device_id AND update_date = (SELECT max(update_date) FROM history WHERE device_id = $device_id AND Year(update_date) = Year(CURRENT_DATE())-1)");
    if ($row = $stmt->fetch(PDO::FETCH_ASSOC))
        $max_year = $row["total_pos_flow"];

    $device_arr = array();
    $device_arr["daily"] = 0;
    $device_arr["monthly"] = 0;
    $device_arr["yearly"] = 0;
    if ($min_day >= 0 and $max_day >= 0)
        $device_arr["daily"] = $max_day - $min_day;
    if ($min_month >= 0 and $max_month >= 0)
        $device_arr["monthly"] = $max_month - $min_month;
    if ($min_year >= 0 and $max_year >= 0)
        $device_arr["yearly"] = $max_year - $min_year;

    return ($device_arr);
}

function getunit($device_id, &$unit_flow, &$unit_totalizer)
{
    global $database;

    $stmt = $database->execute("SELECT unit_flow, unit_totalizer FROM users, devices WHERE devices.user_id=users.id and devices.id=$device_id");
    if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $unit_flow = $row["unit_flow"];
        $unit_totalizer = $row["unit_totalizer"];
    }
}
