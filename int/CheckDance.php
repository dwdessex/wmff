<?php
  include_once("DanceLib.php");
  include_once("ProgLib.php");

/*  Check Dance 
  Go through all events for this year with dance
  Find all sides and where they are dancing and who with
  For each side get list of venues, times count number -> Hold
  For each side check that at least 1 slot between spots -> list all errors
  For each side with overlaps check overlap not at same time - same = error, +/- 1 Note, but not error
  Check not on days not there, before arrive, after leave etc.  
  Sides with no dance = error, not same as spots = note
  Update report window is open, otherwise display all  
  Check Surfaces - if the side has none shown all are permitted otherwise check
  Check Sharing States (not for big events)

  Think about likes/dislikes

*/

include_once("DanceLib.php");

function CheckDance($level) { // 0 = None, 1 =Major, 2= All
  global $db,$YEAR, $DayList, $Surfaces, $Share_Type,$Procession;

// GRAB LOTS OF DATA
  echo "<div id=ChechedDance>";
  $Procession = 0;
  if ($level == 0) {
    echo "Errors not being checked for</div>";
    return;
  }
  $Venues = Get_Venues(1);
  $Sides = &Select_Come_All();
  $sidenames = Sides_All();

  $sideercount = 0;
  $res = $db->query("SELECT e.* FROM Events e, Event_Types t WHERE Year=$YEAR AND e.Type=t.ETypeNo AND t.HasDance=1 ORDER BY Day, Start" );
  if ($res) {
    while ($e = $res->fetch_assoc()) {
      $eid = $e['EventId'];
      $Events[$eid]=$e;
      for($i=1;$i<5;$i++) {
	if ($s = $e["Side$i"]) {
	  if (isset($Sides[$s])) {
	    $dancing[$s][] = $eid;
	  } else {
      	    echo "<a href=AddDance.php?sidenum=$s>" . $sidenames[$s] . "</a>: ";
      	    echo "<span class=red>Is listed doing an event at " . $e['Start'] . " in " . SName($Venues[$e['Venue']]) .
		 " on " . $DayList[$e['Day']] . ", but is <b>NOT</b> there that day</span>";
	  }
	}
      }
      if ($e['BigEvent']) {
	if ($e['Name'] == 'Procession') $Procession = $eid;
        $Other = Get_Other_Things_For($eid);
	$Events[$eid]['Other'] = $Other;
	foreach ($Other as $i=>$ot) {
	  if ($ot['Type'] == 'Side') {
	    $s = $ot['Identifier'];
	    if (isset($Sides[$s])) {
	      $dancing[$s][] = $eid;
	    } else {
      	      echo "<a href=AddDance.php?sidenum=$s>" . $sidenames[$s] . "</a>: ";
      	      echo "<span class=red>Is listed doing an event at " . $e['Start'] . " in " . SName($Venues[$e['Venue']]) .
		   " on " . $DayList[$e['Day']] . ", but is <b>NOT</b> there that day</span>";
	    }
	  }
	}
      }
    }
  } else {
    $sideercount = 1;
    echo "<h2 class=Err>No Events Found</h2>";
  }

// Go through each side checking for lots

  foreach ($Sides as $si=>$side) {
    $Err = '';
    $Merr = '';
    $LastDay = '';
    $LastTime = 0;
    $DayCounts = array(0,0,0);
    $VenuesUsed = array();
    $surfs = 0;
    $last_e = 0;
    $minorspots = 0;
    $badvens = array();
    foreach ($Surfaces as $ss=>$s) if ($ss < 5 && $s && $side["Surface_$s"]) $surfs++;
    $lastVen = -1;
    $InProcession = 0;
    if (isset($dancing[$si])) {
      foreach ($dancing[$si] as $dd=>$e) { // Checking for ~30 minute gaps
	$Ven = $Events[$e]['Venue'];
	$daynum = $Events[$e]['Day']; 
	$day = $DayList[$daynum];
	$start = $Events[$e]['Start'];
	if ($Events[$e]['EventId'] == $last_e) {
	  $Err .= "Doing the same event on $day at $start in " . SName($Venues[$Ven]) . ", ";
	}
	$last_e = $Events[$e]['EventId'];
	if ($last_e == $Procession) $InProcession = 1;
	if ($Events[$e]['SubEvent'] < 0) { $End = $Events[$e]['SlotEnd']; } else { $End = $Events[$e]['End']; };
	if (!isset($side[$day])) { 
	  $Err .= "Event Issue: Dances not allowed for on $day (yet), ";
	} elseif (!$side[$day]) { 
	  $Err .= "Not at Festival on $day, ";
	} elseif ($day != $LastDay) {
	  $VenuesUsed = array();
	  $LastDay = $day;
	  $LastTime = $End;
	  $minorspots = 0;
	} elseif (timereal($start) - timereal($LastTime) < 20) { // Min 20 mins to allow for odd timing of some events
	  $Err .= "Too close on $day $start to $LastTime at " . SName($Venues[$lastVen]) . ", ";
	} else {
	  $LastTime = $End;
	}
	if (isset($VenuesUsed[$Ven])) {
	  if (!$Venues[$Ven]['AllowMult']) $Merr .= "Dancing multiple times at " . SName($Venues[$Ven]) . " on $day, ";
	} else {
	  $VenuesUsed[$Ven] = 1;
	}
	if ($Venues[$Ven]["Minor$day"]) {
	  if ($minorspots++) $Merr .= "Dancing $minorspots times at minor spots on $day,";
	}
	if ($surfs) {
//if (!$Surfaces[$Venues[$Ven]['SurfaceType1']]) { echo "Surface - $Ven ..."; }
	  if (($side["Surface_" . $Surfaces[$Venues[$Ven]['SurfaceType1']]]) || 
	      ($side["Surface_" . $Surfaces[$Venues[$Ven]['SurfaceType2']]])) { // Good
	  } else {
	    if(!isset($badvens[$Ven])) {
              $Err .= "Do not like dancing on the surfaces at " . SName($Venues[$Ven]) . ", ";
	    }
	   $badvens[$Ven]=1;
	  }
        }

	if (!$Events[$e]['BigEvent']) { // Sharing Checks
	  $ns = 0;
	  for ($j=1; $j<5; $j++) if ($Events[$e]["Side$j"]>0) $ns++;
	  if ($ns == 1) {
	    if ($side['Share'] == $Share_Type['Always']) $Err .= "Do not like being alone ( $day " . $Events[$e]['Start'] . 
				" at " . SName($Venues[$Events[$e]['Venue']]) . ", ";
	  } else if ($side['Share'] == $Share_Type['Never']) $Err .= "Do not like sharing ( $day " . $Events[$e]['Start'] . 
				" at " . SName($Venues[$Events[$e]['Venue']]) . ", ";
	}

	if (!$Events[$e]['ExcludeCount']) $DayCounts[$daynum]++;

	for($i = 1; $i<3; $i++) { // Dancer Overlaps - should work
  	  if ($o = $side["OverlapD$i"]) {
	    if (isset($dancing[$o])) {
	      $oside = $Sides[$o];
	      $oname = $oside['Name'];
	      $starttime = timereal($start = $Events[$e]['Start']);
	      foreach ($dancing[$o] as $od=>$oe) {
		if ($Events[$oe]['Day'] == $daynum) {
		  if ($Events[$oe]['SubEvent'] < 0) { $OEnd = $Events[$oe]['SlotEnd']; } else { $OEnd = $Events[$oe]['End']; }
		  $gap = $starttime - timereal($OEnd);
		  if ($gap < -50) {
		  } elseif ($gap <= 0) {
		    $Err .= "Dancer Overlap on $day $start with $oname, ";
		  } elseif ($gap < 5) { // 
		    $Err .= "No dancer Gap on $day $start with $oname, ";
		  } elseif ($gap < 20) { // Checking for 20, not 30 to allow for odd timings of some events
		    $Merr .= "Little dancer Gap on $day $start with $oname, ";
		  }
		}
	      }
	    }
	  }
	}
	$lastVen = $Ven;
      }
	// Musician Overlaps - can do same spot multi sides and 2 consecutive spots, not 3+ - To be written
	if ($side["OverlapM1"] || $side["OverlapM2"] ) {
//if ($si ==256) echo "here...";
	  $Playing = $dancing[$si];
	  $otherplaying = 0;
 	  for ($i = 1; $i < 3; $i++) {
	    if ( $side["OverlapM$i"] ) {
	      if (isset($dancing[$side["OverlapM$i"]])) {
		foreach ($dancing[$side["OverlapM$i"]] as $oei) {
		  $pos = -1;
		  $oe = $Events[$oei];
	          foreach ($Playing as $p=>$sei) {
		    $se = $Events[$sei];
		    if ($pos < 0 && ($oe['Day'] < $se['Day'] || ($oe['Day'] == $se['Day'] && $oe['Start'] < $se['Start']))) $pos = $p;
		  }
		  array_splice($Playing,$pos,0,$oei);
	          $otherplaying = 1;
	        }
	      }
	    }
	  } // Playing now has events in order
// if ($si == 256) var_dump($Playing,"<br>");
	  if ($otherplaying) {
	    $LastVen = 0;
	    $Consec = 0;
	    $LastDay = 0;
	    $LastTime = 0;
	    foreach ($Playing as $pd=>$e) {
//if ($e == 0) var_dump($si,$Playing,"<br>");
	      $Ev = $Events[$e];
	      $start = timereal($Ev['Start']);
	      if ($Ev['SubEvent'] < 0) { $End = timereal($Ev['SlotEnd']); } else { $End = timereal($Ev['End']); }
	      $Ven = $Ev['Venue'];
//if ($si ==256) var_dump("xx",$start,$LastTime,$End);
	      if ($LastDay == $Ev['Day'] && $start < ($LastTime + 20)) {
	        $day = $DayList[$LastDay];
		if ($Ven == $LastVen) {
		  $Consec += ($End - $LastTime);
		  if ($Consec > 65) $Merr .= "Performing for $Consec minutes on $day at " . $Ev['Start'] . ", ";
	        } else {
		  $Err .= "Playing at the same time in two locations: " . SName($Venues[$LastVen]) . " at " . timeformat($LastTime) .
				" on $day and at " . SName($Venues[$Ven]) . " at " . $Ev['Start'] . ", ";
		}
	      } else {
		$Consec = 0;
	      }
	      $LastVen = $Ven;
	      $LastDay = $Ev['Day'];
	      $LastTime = $End;
	    }
	  }
	}  

      // First/Last Check and number of spots
      if ($side['Arrive'] && ($side['Arrive'] > $dancing[$si][0]['Start'])) { $Err .= "Dancing before arriving, "; };
      if ($side['Depart'] && ($side['Depart'] < $LastTime)) { $Err .= "Dancing after depature, "; };

      if ($side['Sat']) 
	if ($DayCounts[1] != $side['SatDance']) $Merr .= "Have " . $DayCounts[1] . " spots on Sat and wanted " . $side['SatDance'] . ", ";
      if ($side['Sun']) 
	if ($DayCounts[2] != $side['SunDance']) $Merr .= "Have " . $DayCounts[2] . " spots on Sun and wanted " . $side['SunDance'] . ", ";

      if ($side['Sat'] && $side['Procession'] != $InProcession) {
	if ($InProcession) { $Err .= "In the Procession, but don't want to be.  "; }
        else { $Merr .= "Not yet in the procession."; }
      }
      // NOTE no checking (yet) of likes/dislikes

    } else {
      $Merr .= 'No Dance spots, ';
    }

    // Update error list and dance list cache?
    $needbr=0;
    if ($Err) {
      $sideercount++;
      echo "<a href=AddDance.php?sidenum=$si>" . $side['Name'] . "</a>: ";
      echo "<span class=red>$Err</span>";
      $needbr=1;
    }
    if ($Merr && $level==2) {
      if (!$Err) {
        $sideercount++;
        echo "<a href=AddDance.php?sidenum=$si>" . $side['Name'] . "</a>: ";
      }
      echo "<span class=brown>$Merr</span>\n";
      $needbr=1;
    }
    if ($needbr) echo "<br>";
  }  

  if ($sideercount == 0) echo "No Errors!\n";
  echo "</div>\n"; 
}
?>
