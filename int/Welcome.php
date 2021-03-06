<?php
  include_once("fest.php");
  A_Check('Committee','Users');
?>

<html>
<head>
<title>WMFF Staff | Welcome</title>
<?php include("files/header.php"); ?>
<?php include_once("festcon.php"); ?>
</head>
<body>

<?php
  include("files/navigation.php");
  include("UserLib.php");

  if (isset($_GET['U'])) {
    $uid = $_GET['U'];
    $user = Get_User($uid);

    if (!$user['Email']) {
      Error_Page('No Email Set up for ' . $user['Name']);
    };
    $newpwd = substr(str_shuffle('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789') , 0 , 10 );
    $hash = crypt($newpwd,"WM");
    $User['password'] = $hash;
    Put_User($User);

    $letter = firstword($user['Name']) . "<p>Welcome to the Wimborne Minster Folk Festival staff pages.<p>" .
	"It is initially accessed by using the <a href=http://wimbornefolk.co.uk/int/Login.php>Login</a> at the bottom of any page below " .
	"the copyright statement on any page of the <a href=http://wimbornefolk.co.uk>website</a>.<p>" .
	"Your username is : " . $user['Login'] ."<br>" .
	"Initial password : $newpwd<p>" .
	"When you are logged in, an extra tab will apear on the navigation bar 'Staff Tools' this gives access to the database and ".
	"document storage.<p>" .
	"Everyone can use the document storage.  To save any files for use by the festival.  <p>" .
	"Everyone can use the simple reporting of problems and requesting features.<p>" .
	"Everyone can set a new password, and will shortly be able to set a few preferences.<p>" .
	"Access to other areas is restricted, most things can be read by everbody, " .
	"but the creation and editing is restricted to relevant people.<p>" .
	"Dance is ready for full use, as are many features of Music, Trade, News and Sponsors.<p>" .
	"If something is not obvious please tell me and I will improve it.<p>" .
	"Richard";
 
    SendEmail($user['Email'],"Welcome " . firstword($user['Name']) . " to WMFF Staff pages",$letter);

    echo "Email sent:<p>$letter";
  } else {
    echo "No user..."; 
  }
?>

</div>

<?php include("files/footer.php"); ?>
</body>
</html>
