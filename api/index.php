
 <?php 
  session_start();

  require_once '../app/model/db.php';

  $redirect = getValue("function", true);
  if (isset($_SESSION['userid']))
  {
      $session_user_id = $_SESSION['userid'];
      $session_user_type = $_SESSION['usertype'];
  }
  else {
      $session_user_id = 0;
      $session_user_type = 0;
  }

  if($session_user_type == 1) { // Admin
      require "./admin.php";
  }
  elseif($session_user_type == 2) { // User
      require "./user.php";
  }
  else { // Not Logged
      echo "Restricted Area!! You cannot access this page => ";
      die();
  }
  
//   * This is a PHP code snippet from the index.php file.
//   * It checks the user session and redirects the user based on their user type.
//   * If the user is an admin, it includes the admin.php file.
//   * If the user is a regular user, it includes the user.php file.
//   * If the user is not logged in, it displays a restricted area message and terminates the script.

?>  
  