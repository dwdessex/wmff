<?php
  include_once("fest.php");
  A_Check('Committee','Dance');

  include_once("ProgramLib.php");

  Prog_Headers();
  Grab_Data();
  Scan_Data();
  Prog_Grid();
  Side_List();
  Controls();
  $lvl = (isset($_GET['EInfo'])? $_GET['EInfo'] : 0 );
  ErrorPane($lvl);
  InfoPane();
  Notes_Pane()

// No standard footer - will use whole screen
?>

</body>
</html>
