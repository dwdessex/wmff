<?php
  include_once("fest.php");
//  A_Check('Steward');

  global $DAY;
  include_once("ProgramLib.php");

  $Cond = 0;
  if (isset($_GET{'Cond'})) $Cond = $_GET{'Cond'};
  if (isset($_POST{'Cond'})) $Cond = $_POST{'Cond'};

  $day = "Both";
  if (isset($_GET{'Day'})) $day = $_GET{'Day'};

  $head = 1;
  if (isset($_GET{'Head'})) $head = $_GET{'Head'};

  $Public='';
  if (isset($_GET{'Pub'})) $Public=1;

//  var_dump($day);
  Prog_Headers($Public,$head);
  if ($day != 'Sun') {
    Grab_Data();
    Scan_Data($Cond);
    if ($Public) echo "<p><h2>Saturday Dance</h2><p>\n";
    Prog_Grid(0,0,$Cond,$Public);
  }
  if ($day != 'Sat') {
    if ($Public) {
      Grab_Data("Sun");
      Scan_Data($Cond);
      echo "<p><h2>Sunday Dance</h2></p>\n";
      Prog_Grid(0,0,$Cond,$Public);
      dotail();
    } else {
      Controls(0,$Cond);
      ErrorPane(0);
// No standard footer - will use whole screen
      echo "</body></html>\n";
    }
  } else {
    if ($Public && $head) {
      dotail();
    } else {
      echo "</body></html>\n";
    }
  }

?>

