<?php
  include_once("fest.php");
  A_Check('Staff','Venues');
?>

<html>
<head>
<title>WMFF Staff | Add/Change Event</title>
<?php include("files/header.php"); ?>
<?php include_once("festcon.php"); ?>
<script src="/js/Participants.js"></script>
</head>
<body>
<?php include("files/navigation.php"); ?>
<?php
  include("ProgLib.php");
  include("DanceLib.php");
  include("MusicLib.php");
  global $MASTER,$YEAR;

  Set_Event_Help();

  $EventTimeFields = array('Start','End','SlotEnd');
  $EventTimeMinFields = array('Setup','Duration');

  $SideList=Select_Come();
  $ActList=Select_Act_Come(0);
  $OtherList=Select_Other_Come(0);
  $Venues = Get_Venues(0);
  $Skip = 0;

  echo "<div class='content'><h2>Add/Edit Events</h2>\n";
  echo "<form method=post action='EventAdd.php'>\n";
  if (isset($_POST{'EventId'})) { // Response to update button
    $eid = $_POST{'EventId'};
    if ($eid > 0) $Event = Get_Event($eid);
    if (isset($_POST{'ACTION'})) {
      switch ($_POST{'ACTION'}) {
      case 'Divide':
        //echo fm_smalltext('Divide into ','SlotSize',30,2) . fm_smalltext(' minute slots with ','SlotSetup',0,2) . " minute setup";
	$slotsize  = $_POST['SlotSize'];
	$slotsetup = $_POST['SlotSetup'];
        $se = $Event['SubEvent'];
	$SubEvent = $Event;
	for ($i=1;$i<5;$i++) { $SubEvent["Side$i"] = $SubEvent["Act$i"] = $SubEvent["Other$i"] = 0; };
//	$SubEvent['Name'] = "";
	if ($se == 0) {
	  $Timeleft = timereal($Event['End'])-timereal($Event['Start'])-$slotsize;
	  if ($Timeleft > 0) {
	    $Event['SubEvent'] = -1;
	    $Event['SlotEnd']=timeadd($Event['Start'],$slotsize-$slotsetup);
	    Put_Event($Event);
	    $SubEvent['SubEvent']=$eid;
	    while ($Timeleft > 0) {
	      $SubEvent['Start'] = timeadd($SubEvent['Start'],$slotsize);
	      $SubEvent['End'] = min($Event['End'],timeadd($SubEvent['Start'],$slotsize-$slotsetup));
	      $SubEvent['Duration'] = $SubEvent['End'] - $SubEvent['Start'];
	      $Timeleft -= $slotsize;
	      Insert_db('Events',$SubEvent);
            }
	  } else { 
	    $Err = "Can't divide";
	  }
	} elseif ($se < 0) { // Aready parent event
	  $Timeleft = timereal($Event['SlotEnd'])-timereal($Event['Start'])-$slotsize;
	  if ($Timeleft > 0) {
	    $oldEnd = $Event['SlotEnd'];
	    $Event['SlotEnd']=timeadd($Event['Start'],$slotsize-$slotsetup);
	    Put_Event($Event);
	    $SubEvent['SubEvent']=$eid;
	    while ($Timeleft > 0) {
	      $SubEvent['Start'] = timeadd($SubEvent['Start'],$slotsize);
	      $SubEvent['End'] = min($oldEnd,timeadd($SubEvent['Start'],$slotsize-$slotsetup));
	      $SubEvent['Duration'] = $SubEvent['End'] - $SubEvent['Start'];
	      $Timeleft -= $slotsize;
	      Insert_db('Events',$SubEvent);
            }
	  } else { 
	    $Err = "Can't divide";
	  }
	} else { // Child event
	  $Timeleft = timereal($Event['End'])-timereal($Event['Start'])-$slotsize;
	  if ($Timeleft > 0) {
	    $oldEnd = $Event['End'];
	    $Event['End']=timeadd($Event['Start'],$slotsize-$slotsetup);
	    Put_Event($Event);
	    while ($Timeleft > 0) {
	      $SubEvent['Start'] = timeadd($SubEvent['Start'],$slotsize);
	      $SubEvent['End'] = min($oldEnd,timeadd($SubEvent['Start'],$slotsize-$slotsetup));
	      $SubEvent['Duration'] = $SubEvent['End'] - $SubEvent['Start'];
	      $Timeleft -= $slotsize;
	      Insert_db('Events',$SubEvent);
            }
	  } else { 
	    $Err = "Can't divide";
	  }
	}
        break;

      case 'Add': // Add N Subevents starting and ending at current ends - if a subevent, parent is ses parent
        $AddIn = $_POST{'Slots'};
	$Se = $Event['SubEvent'];
	$SubEvent = $Event;
	$SubEvent['End'] = $SubEvent['Start'];
	$SubEvent['Duration'] = 0;
	for ($i=1;$i<5;$i++) { $SubEvent["Side$i"] = $SubEvent["Act$i"] = $SubEvent["Other$i"] = 0; };
	if ($Se > 0) { // Is already a Sub event so copy parent
	} else if ($Se ==0 ) { // SEs of this
	  $Event['SubEvent'] = -1;
	  $Event['SlotEnd'] = $Event['End'];
	  Put_Event($Event);
	  $SubEvent['SubEvent'] = $eid;
	} else { // Already Has SEs
	  $SubEvent['SubEvent'] = $eid;
	}  
	for($i=1;$i<=$AddIn;$i++) Insert_db('Events',$SubEvent);
	break;

      case 'Delete':
	$Event['Year'] -= 1000;
	Put_Event($Event);
	$Skip = 1;
        break;
      }
    } elseif ($eid > 0) { 	// existing Event
      $CurEvent=$Event;
      Parse_TimeInputs($EventTimeFields,$EventTimeMinFields);
      Update_db_post('Events',$Event);
      Check_4Changes($CurEvent,$Event);
      $OtherValid = 1;
      if ($Event['BigEvent']) {
	$err = 0;
        if (!isset($Other)) $Other = Get_Other_Things_For($eid);
	if (!$err && $Other) foreach ($Other as $i=>$ov) {  // Start with venues only
	  if ($ov['Type'] == 'Venue') {
	    $id = $ov['BigEid'];
	    if ($_POST{"VEN$id"} != $ov['Identifier']) {
	      $ven = $_POST{"VEN$id"};
	      if ($ven != 0 ) {
	      	if ($Event['Venue'] == $ven) $err = 1;
	        foreach ($Other as $ii=>$oov) if ($ov['Type'] == 'Venue' && $oov['Identifier'] == $ven) $err=1;
		$BigE = Get_BigEvent($id);
	        $BigE['Identifier'] = $ven;
	     	Put_BigEvent($BigE);
	      } else {
		db_delete('BigEvent',$id);
	      }
	      $OtherValid = 0;
	    }
	  }
	}
	if ($err==0 && $_POST{'NEWVEN'} > 0) { // Add venue
	  if ($Other) foreach ($Other as $i=>$ov) if ($ov['Type'] == 'Venue' && $ov['Identifier'] == $_POST{'NEWVEN'}) $err++;
	  if ($err == 0 && $Event['Venue'] == $_POST{'NEWVEN'}) $err++; 
	  if ($err == 0) {
	    $BigE = array('Event'=>$eid, 'Type'=>'Venue', 'Identifier'=>$_POST{'NEWVEN'});
	    New_BigEvent($BigE);
	    $OtherValid = 0;
	  }
	}
	if ($err) echo "<h2 class=ERR>The Event already has Venue " . $Venues[$_POST{'NEWVEN'}] . "</h2>\n";
	if (!$OtherValid) unset($Other);
      }  
    } else { // New
      $proc = 1;
      if (!isset($_POST['Name']) || strlen($_POST{'Name'}) < 2) { 
        echo "<h2 class=ERR>NO NAME GIVEN</h2>\n";
        $Event = $_POST;
	$proc = 0;
      }
      Parse_TimeInputs($EventTimeFields,$EventTimeMinFields);
      $_POST{'Year'} = $YEAR;
      $eid = Insert_db_post('Events',$Event,$proc); //
      $empty = array();
      Check_4Changes($empty,$Event);
    }
  } elseif (isset($_GET{'e'})) {
    $eid = $_GET{'e'};
    $Event = Get_Event($eid);
  } else {
    $eid = -1;
    $Event = array();
    if (isset($_GET{'Act'})) $Event['Act1'] = $_GET{'Act'};
  }

// $Event_Types = array('Dance','Music','Workshop','Craft','Mixed','Other');
// Dance		Y		Y			Y	Y
// Music			Y	Y			Y	Y
// Other				Y		Y	Y	Y

//var_dump($Event);
  if (isset($Err)) echo "<h2 class=ERR>$Err</h2>\n";
  if (!$Skip) {
    $adv = ($Event['SubEvent']>0?"class=Adv":""); 
    echo "<table width=90% border>\n";
      if (isset($eid) && $eid > 0) {
        echo "<tr><td>Event Id:" . $eid . fm_hidden('EventId',$eid);
      } else {
        echo fm_hidden('EventId',-1);
      }
//      echo fm_text('SE',$Event,'SubEvent');
      echo "<td>" . fm_checkbox('Big Event',$Event,'BigEvent');
      echo "<td>" . fm_checkbox('Exclude From Spot Counts',$Event,'ExcludeCount');
      echo "<td>Public:" . fm_select($Public_Event_Types,$Event,'Public');
      echo "<td>" . fm_checkbox('Family Event',$Event,'Family');

      echo "<tr>" . fm_text('Name', $Event,'Name');
        echo "<td>Event Type:" . fm_select($Event_Types,$Event,'Type');
        $se = $Event['SubEvent'];
        if ($se == 0) { echo "<td>No Sub Events"; }
        elseif ($se < 0) { echo "<td><a href=EventList.php?se=$eid>Has Sub Events</a>"; }
        else { echo "<td><a href=EventList.php?se=$se>Is a Sub Event</a>"; };

      if ($se <= 0) {
	echo "<tr>";
	echo "<td>" . fm_simpletext('Price &pound;',$Event,'Price1');
	if ($MASTER['PriceChange1']) echo "<td>" . fm_simpletext('Price after ' . date('j M Y',$MASTER['PriceChange1']) . '(if diff) &pound;',$Event,'Price2');
	if ($MASTER['PriceChange2']) echo "<td>" . fm_simpletext('Price after ' . date('j M Y',$MASTER['PriceChange2']) . '(if diff) &pound;',$Event,'Price3');
	echo "<td>" . fm_simpletext('Door Price (if different) &pound;',$Event,'DoorPrice');
	echo "<td>" . fm_simpletext('Ticket Code',$Event,'TicketCode');
      }

      echo "<tr>" . fm_radio('Day',$DayList,$Event,'Day');
//      echo fm_text('Year',$Event,'Year');
      echo "<td colspan=2>Times: " . fm_smalltext('Start','Start',$Event['Start']);
        echo fm_smalltext('End','End',$Event['End']);
        echo fm_smalltext('Setup Time','Setup (mins)',$Event['Setup']);
        echo fm_smalltext('Duration','Duration',$Event['Duration']) . " minutes";
        if ($se == 1) echo fm_smalltext('Slot End','SlotEnd',$Event['SlotEnd']);
      echo "<td>Participant Visibility:" . fm_select($VisParts,$Event,'InvisiblePart');
      echo "<tr><td>Venue:<td>" . fm_select($Venues,$Event,'Venue',1);
        echo fm_textarea('Notes', $Event,'Notes',4,2);
      $et = 'Mixed';
      if (isset($Event['Type'])) $et = $Event_Types[$Event['Type']];
      echo "<tr $adv>" . fm_textarea('Description',$Event,'Description',5,2,'','maxlength=150');
      echo "<tr $adv>" . fm_textarea('Blurb',$Event,'Blurb',5,2,'','maxlength=2000');
      echo "<tr><td>" . fm_checkbox('Bar',$Event,'Bar') . "<td>" . fm_checkbox('Food',$Event,'Food') . fm_text('Food/Bar text',$Event,'BarFoodText') . "\n";
      if (!$Event['BigEvent']) {
//        if ($et == 'Dance' || $et == 'Workshop' || $et == 'Mixed' || $et == 'Other') {
          echo "<tr><td rowspan=2>Sides:";
          for ($i=1; $i<5; $i++) {
	    if ($i==3) echo "<tr>"; 
	    echo "<td colspan=2>" . fm_select($SideList,$Event,'Side' . $i);
	  }
//        }
//        if ($et == 'Music' || $et == 'Workshop' || $et == 'Mixed' || $et == 'Other') {
          echo "<tr><td rowspan=2>Music:";
          for ($i=1; $i<5; $i++) {
	    if ($i==3) echo "<tr>"; 
            echo "<td colspan=2>" .fm_select($ActList,$Event,'Act' . $i,1);
          }
//        }
//        if ($et == 'Craft' || $et == 'Workshop' || $et == 'Mixed' || $et == 'Other') {
          echo "<tr><td rowspan=2>Other:";
          for ($i=1; $i<5; $i++) {
	    if ($i==3) echo "<tr>"; 
	    echo "<td colspan=2>" .fm_select($OtherList,$Event,'Other' . $i,1);
	  }
//        }
      } else {
	$ovc=0;
        echo "<tr><td>Other Venues:";
        if (!isset($Other)) $Other = Get_Other_Things_For($eid);
 	if ($Other) {
	  foreach ($Other as $i=>$ov) {
	    if ($ov['Type'] == 'Venue') {
	      $id = $ov['Identifier'];
  	      echo "<td>" . fm_select2($Venues,$id,"VEN" . $ov['BigEid'] ,1);
	      if ((($ovc++)&3) == 3) echo "\n<tr><td>";
	    }
	  }
	}
  	echo "<td>" . fm_select2($Venues,0,"NEWVEN",1);
      }
        
    echo "</table>\n";
    if ($Event['BigEvent']) {
      echo "Use the <a href=BigEventProg.php?e=$eid>Big Event Programming Tool</a> to add sides, musicians and others to this event. ";
      echo "Use the <a href=DisplayBE.php?e=$eid>Big Event Display</a> to get a simply display of the event.";
    }
  
    if ($eid > 0) {
      echo "<Center><input type=Submit name='Update' value='Update'>\n";
      if (Access('Committee','Venues')) {
        echo ", <form method=post action='EventAdd.php'>\n";
        echo fm_hidden('EventId',$eid);
        echo fm_smalltext('Divide into ','SlotSize',30,2) . fm_smalltext(' minute slots with ','SlotSetup',0,2) . " minute setup";
        echo "<input type=Submit name=ACTION value=Divide>, \n";
        echo "<input type=Submit name=ACTION value=Delete onClick=\"javascript:return confirm('are you sure you want to delete this?');\">, \n";
	echo "<input type=Submit name=ACTION value=Add>" . fm_smalltext('','Slots',1,2) . " sub events";
        echo "</form>\n";
      }
      echo "</center>\n";
    } else { 
      echo "<Center><input type=Submit name=Create value='Create'></center>\n";
    }
    if ($Event['SubEvent'] > 0) echo "<button onclick=ShowAdv(event) id=ShowMore type=button class=floatright>More features</button>";
    echo "</form>\n";
  }
  echo "<h2><a href=EventList.php>List Events</a>";
  if ($eid) echo ", <a href=EventAdd.php>Add another event</a>";
  if ($eid) echo ", <a href=EventShow.php?e=$eid>Show Event</a>";
  echo "</h2>\n";
?>

</div>

<?php include("files/footer.php"); ?>
</body>
</html>
