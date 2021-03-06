<?php
  include_once("fest.php");
  A_Check('Committee','Venues');

  dostaffhead("Manage Event Types");

  include_once("ProgLib.php");
  include_once("TradeLib.php");

  echo "<div class='content'><h2>Manage Event Types</h2>\n";
  echo "Please don't have too many types.<p>\n";
  echo "The only event types that should be not public are Sound Checks (probably)<p>\n";
  
  $Types = Get_Event_Types(1);
  if (UpdateMany('EventTypes','Put_Event_Type',$Types,1)) $Types = Get_Event_Types(1);

  $coln = 0;

  echo "<h2>Event Types</h2><p>";
  echo "Set the Not critical flag for sound checks - means that this event type does not have to be complete for contract signing.<p>";
  echo "Set the Use Imp flag to bring headline particpants to top of an event, they still get bigger fonts.<p>";
  echo "<form method=post action=EventTypes.php>";
  echo "<table id=indextable border>\n";
  echo "<thead><tr>";
  echo "<th><a href=javascript:SortTable(" . $coln++ . ",'N')>Event Type</a>\n";
  echo "<th><a href=javascript:SortTable(" . $coln++ . ",'T')>Name</a>\n";
  echo "<th><a href=javascript:SortTable(" . $coln++ . ",'T')>Public</a>\n";
  echo "<th><a href=javascript:SortTable(" . $coln++ . ",'T')>Has Dance</a>\n";
  echo "<th><a href=javascript:SortTable(" . $coln++ . ",'T')>Has Music</a>\n";
  echo "<th><a href=javascript:SortTable(" . $coln++ . ",'T')>Has Other</a>\n";
  echo "<th><a href=javascript:SortTable(" . $coln++ . ",'T')>Not Critical</a>\n";
  echo "<th><a href=javascript:SortTable(" . $coln++ . ",'T')>Use Imp</a>\n";
  echo "</thead><tbody>";
  foreach($Types as $t) {
    $i = $t['ETypeNo'];
    echo "<tr><td>$i" . fm_text1("",$t,'Name',1,'','',"Name$i");
    echo "<td>" . fm_checkbox('',$t,'Public','',"Public$i");
    echo "<td>" . fm_checkbox('',$t,'HasDance','',"HasDance$i");
    echo "<td>" . fm_checkbox('',$t,'HasMusic','',"HasMusic$i");
    echo "<td>" . fm_checkbox('',$t,'HasOther','',"HasOther$i");
    echo "<td>" . fm_checkbox('',$t,'NotCrit','',"NotCrit$i");
    echo "<td>" . fm_checkbox('',$t,'UseImp','',"UseImp$i");
    echo "\n";
  }
  echo "<tr><td><td><input type=text name=Name0 >";
  echo "<td><input type=checkbox name=Public0>";
  echo "<td><input type=checkbox name=HasDance0>";
  echo "<td><input type=checkbox name=HasMusic0>";
  echo "<td><input type=checkbox name=HasOther0>";
  echo "<td><input type=checkbox name=NotCrit0>";
  echo "<td><input type=checkbox name=UseImp0>";
  echo "</table>\n";
  echo "<input type=submit name=Update value=Update>\n";
  echo "</form></div>";

  dotail();

?>
