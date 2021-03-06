<?php
  include_once("int/fest.php");
  dohead("Music Line-up");
  include_once("int/MusicLib.php");
?>
<h2 class="maintitle">Music Line-up</h2>

<p>We are currently building our line-up for the 2018 festival, so check back soon to see if we have anything ready for you. If you've heard someone good this summer, <a href="/contact" rel="bookmark"><strong>Contact Us</strong></a> to suggest them for next year!</p>

<h2 class="subtitle">Full Line-up A to Z</h2>
<div id="flex">
<?php
  global $db,$Book_State,$YEAR;
  $SideQ = $db->query("SELECT s.*, y.* FROM Sides AS s, ActYear AS y " .
           "WHERE s.SideId=y.SideId AND y.year=$YEAR AND y.YearState>=" . $Book_State['Booking'] . " AND s.IsAnAct=1 ORDER BY s.Importance DESC, s.Name");
  
  while($side = $SideQ->fetch_assoc()) {
    echo "<div class=mini>";
    echo "<a href=/int/ShowMusic.php?sidenum=" . $side['SideId'] . ">";
    if ($side['Photo']) echo "<img class=miniimg src='" . $side['Photo'] ."'>";
    echo "<h2 class=minittl>" . $side['Name'] . "</h2></a>";
    echo "<div class=minitxt>" . $side['Description'] . "</div>";
    echo "</div>";
  }
?>

</div>

<h2 class="subtitle">Stay Updated</h2>
<p>Keep up to date with our latest music announcements by joining us on <a href="http://facebook.com/WimborneFolk" rel="tag" target="-blank"><strong>Facebook</strong></a>, <a href="http://twitter.com/WimborneFolk" rel="tag" target="_blank"><strong>Twitter</strong></a> and <a href="http://instagram.com/WimborneFolk" rel="tag" target="_blank"><strong>Instagram</strong></a>!</p>

<?php
  dotail();
?>
