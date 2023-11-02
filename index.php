<?php
// Start a new or resume the existing session.
session_start();

// Include the database model file.
require_once './app/model/db.php';

// Get the 'page' and 'action' parameters from the request.
$redirect = getValue("page", false, "home"); //the default value of the page parameter is set to "home" if it is not provided or is false.
$page_action = getValue("action", false, null);

// Check if a user session is already active.
if (isset($_SESSION['userid'])) {
    $logged_in = TRUE;
    $session_user_id = $_SESSION['userid'];
    $session_user_type = $_SESSION['usertype'];
} else {
    $logged_in = FALSE;
}

$error_message = $message = null;

// Handle the 'logout' action.
if ($redirect == "logout") {
    if (isset($_SESSION['userid'])) {
        $_SESSION = array(); // Clear session data.

        if (session_id() != "" || isset($_COOKIE[session_name()])) {
            // Delete the session cookie.
            setcookie(session_name(), '', time() - 2592000, '/');
        } //this is to destroy the current session 

        session_destroy(); // Destroy the session.
        header("refresh:1;url=?page=home"); // Redirect to the home page after 1 second.
        echo "<div class='main'><br>" .
            "The system is logging you out.....</div>";
        die(); // Exit the script.
    } else {
        echo "<div class='main'><br>" .
            "You cannot log out because you are not logged in</div>";
    }
}

// Handle the 'login' action.
if ($page_action == "login") {
    // Create a new Database instance.
    $database = new Database();
    $username = getValue('username');
    $password = getValue('password');
    $token = hashPassword($password);

    // Query the database to find a user with the provided credentials.
    $stmt = $database->execute("select id, display_name, user_type from users where user_email='$username' and login_password='$token'");

    if ($stmt->rowCount() >= 1) {
        if ($Results = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $message = $Results['display_name'] . " Login Successful!";
            $_SESSION['userid'] = $session_user_id = $Results['id'];
            $_SESSION['usertype'] = $session_user_type = $Results['user_type'];
            $logged_in = TRUE;
        }
    } else {
        $error_message = "Invalid email or password!!";
    }
}

// Include the header file.
require "./app/header.php";

if ($logged_in == true) {
    if ($session_user_type == 1) {
        $page_open = "./app/admin_" . $redirect . ".php";
        if (file_exists($page_open)) {
            require "./app/menu.php";
            require $page_open;
        } else {
            die("Invalid link specified");
        }
    } elseif ($session_user_type == 2) {
        echo "<div id='positive_message'></div>"; //providing message to the users interface
        echo "<div id='error_message'></div>";

        $page_open = "./app/page_" . $redirect . ".php";
        if (file_exists($page_open)) {
            require "./app/menu.php";
            require $page_open;
        } else {
            die("Invalid link specified");
        }
    }
} else {
    echo ($message ? "<div class='ui positive message'>" . $message . "</div>" : "");
    echo ($error_message ? "<div class='ui error message'>" . $error_message . "</div>" : "");
    require "./app/login.php";
}
?>
<!-- /**
 * Summary:
 * This is a PHP code that handles user authentication and session management.
 * It includes a database model file and performs various actions based on the provided parameters.
 * The code checks if a user session is active, handles logout, and login actions.
 * It also includes header, menu, and page files based on user type and redirects to the home page if necessary.
 */ -->