<div class="ui labeled icon menu">
    <div class="ui container">
      <a href="?page=home" class="item">
        <img class="logo" src="images/logo.png">
      </a>
      <?php if ($session_user_type == 2) { ?>
        <a href="?page=home" class="item"><i class="address book icon"></i>Devices</a>
        <!--<a class="item" href="?page=vwire"><i class="random icon"></i>Piezo</a>-->
        <!--<a class="item" href="?page=loadcell"><i class="chart line icon"></i>Load Cell</a>-->
        <!--<a class="item" href="?page=inclino"><i class="exchange icon"></i>Inclino</a>-->
      <?php } else { ?>
        <a href="?page=home" class="item"><i class="address book icon"></i>Users</a>
        <a class="item" href="?page=devices"><i class="microchip icon"></i>Devices</a>
        <a class="item" href="?page=potential"><i class="address card icon"></i>Register</a>
      <?php } ?>
      <a href="?page=logout" class="item right aligned"><i class="logout icon"></i>Logout</a>
    </div>
  </div>
