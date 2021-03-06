<?php
  include_once("fest.php");
  A_Check('Committee','Users');
?>

<html>
<head>
<title>WMFF Staff | Add/Change User</title>
<?php include("files/header.php"); ?>
<?php include_once("festcon.php"); ?>
</head>
<body>

<?php
  include("files/navigation.php");
  include("UserLib.php");

  Set_User_Help();

  echo "<div class='content'><h2>Add/Edit Fest Con Users</h2>\n";
  echo "<form method=post action='AddUser.php'>\n";
  if (isset($_POST{'UserId'})) { /* Response to update button */
    $unum = $_POST{'UserId'};
    if ($unum > 0) { 				// existing User
      $User = Get_User($unum);
      if (isset($_POST{'ACTION'})) {
        switch ($_POST{'ACTION'}) {
	case 'Set Password' :
	  $hash = crypt($_POST{'NewPass'},"WM");
	  $User['password'] = $hash;
	  $a = Put_User($User);
	  break;
	}
      } else {
        Update_db_post('FestUsers',$User);
      }
    } else { /* New User */
      $proc = 1;
      if (!isset($_POST['Name'])) {
	echo "<h2 class=ERR>NO NAME GIVEN</h2>\n";
	$proc = 0;
      }
      if ($proc && !isset($_POST['Login'])) {
	echo "<h2 class=ERR>NO login GIVEN</h2>\n";
	$proc = 0;
      }
      $unum = Insert_db_post('FestUsers',$User,$proc);
    }
  } elseif ($_GET{'usernum'}) {
    $unum = $_GET{'usernum'};
    $User = Get_User($unum);
  } 

//  echo "<!-- " . var_dump($User) . " -->\n";
  echo "<table width=90% border>\n";
    echo "<tr><td>User Id:<td>";
      if (isset($unum) && $unum > 0) {
	echo $unum . fm_hidden('UserId',$unum);
      } else {
	echo fm_hidden('UserId',-1);
        $User['AccessLevel'] = $Access_Type['Committee'];
      }
    echo "<tr>" . fm_text('Name', $User,'Name',1,'','autocomplete=off');
    echo "<tr>" . fm_text('Email',$User,'Email',1,'','autocomplete=off');
    echo "<tr>" . fm_text('WMFF Email',$User,'WMFFemail',1,'','autocomplete=off');
    echo "<tr>" . fm_text('Login',$User,'Login');
    echo "<tr>" . fm_text('Roll',$User,'Roll');
    echo "<tr><td>Access Level<td>" . fm_select($Access_Levels,$User,'AccessLevel');
    echo "<tr>" . fm_text('Image', $User,'Image');
    echo "<tr><td>Show on Contacts Page:<td>" . fm_checkbox('',$User,'Contacts');
    $r = 0;
    foreach($Sections as $sec) {
      if ((($r++)&1) == 0) echo "<tr>";
      echo "<td>Change " . $sec . ":" . fm_select($Area_Levels,$User,$sec);
    }
    echo "<tr><td>Last Login:<td>" . date('d/m/y H:i:s',$User['LastAccess']);
    if (Access('SysAdmin')) {
      echo "<tr>" . fm_text('Change Sent',$User,'ChangeSent',1,'','readonly');
      echo "<tr>" . fm_text('Access Key',$User,'AccessKey',1,'','readonly');
    }
    echo "</table>\n";

  if ($unum > 0) {
    echo "<Center><input type=Submit name='Update' value='Update'>\n";
    echo "</center>\n";
    echo "</form><form method=post action=AddUser.php>" . fm_hidden('UserId',$unum);
    echo " <input type=text name=NewPass size=10>";
    echo "<input type=submit name=ACTION value='Set Password'></form>\n";
    echo "<h2><a href=Welcome.php?U=$unum>Send Welcome Email with New Password Link</a> , \n";
  } else { 
    echo "<Center><input type=Submit name=Create value='Create'></center>\n";
    echo "</form>\n<h2>";
  }
  echo "<a href=ListUsers.php>List Users</a> , \n";
  if ($unum >0) echo "<a href=AddUser.php>Add Another User</a>\n";
  echo "</h2>";
?>

</div>

<?php include("files/footer.php"); ?>
</body>
</html>
