<?php
  include_once("fest.php");
  A_Check('Upload');
?>

<html>
<head>
<title>WMFF Staff | Help</title>
<?php include("files/header.php"); ?>
<?php include_once("festcon.php"); ?>
</head>
<body>
<?php include("files/navigation.php"); ?>
<div class="content">
<p>
In general anyone at committee level can read anything, but only edit/change those parts of the system they have been given control over.<p>

If you are not logged in, there is a small discrete Login link just below the copytight statement at the bottom of the page.<p>

If you are logged in, it will show it on the main navigation bar (and a logout link) and a button "Staff Tools" will apear.  
From this page all features of the databases and document stprage are available.<p>

<h2>How To Use the Document Storage</h2><p>

This is a normal directory structure.  With files and directories.<p>

Navigate through the files and directories by clicking on them.<p>

Click on a file it will open it in your browser.  If the file type is not handled by you browser you can save and open it.<p>

You can create a new directory by using the "Create New Directory" below any directory listing.  
Provide the name and click on the "Create" button.<p>

You can upload files, clicking on the "Choose Files" below any directory listing.  You can either upload one file or many files.
In general to upload many files select the first one, then select others while holding your control or shift keys down.  (I hope to support
drag and drop for files soon - Richard).<p>

Files and directories you have uploaded can be deleted, renamed and moved by you.<p>

Note files "Deleted" are removed from view and archieved.  It is possible (but not easy) to recover a deleted file.<p>

At present all documents / directories can be read by everyone, some design is in place to restrict this in the future if wanted.<p>

<h2>Dance, Music and other Participants</h2><p>

In general these will all be treated in similar ways.  If (when) an area comes up with extra requirements 
they will be implemented.  Where practicle new features for one area will work for all.<p>

You can list all the paticipants (in several ways).  Add/update them and perform programming and checks.<p>

Once the participant is in the system you can Email them at they can edit and update many properties, upload photos,
provide blurb for the website, upload Insurance and other documents without you having to be involved yourself.<p>

If you want them to be able to do updates then <b>ALWAYS</b> use the email links from the participant lists/edit pages.  
These links launch your email client with an appropriate subject and most importantly a link in the body of the 
message which enables them to maintain their data.<p>

When they do updates, you should be able to get (if you want) an email from the system highlighting the changes made.  This can currently be enabled by Richard
or James.  (Currently broken - Richard)<p>

<h2>Event Programming</h2><p>

First Venues need be defined.<p>

Then add the Event.  An event could be "Dancing" in the Cornmarket from 1000 to 1700.  An event such as this can be subdivided into
subevents for every half hour.  Although the partipants can be set up from the event creation page, for most purposes there is a much
easier to use tool with drag/drop under the relevant section (eg Dance).<p>

There is special provision for large events that have large numbers of participants and many venues (eg the Procession).  
Select "Big Event" and create/update the event.  This enables you to select many more venues and gives access to an extra drag/drop tool
to add any participant to the event.<p>

You can control public access to events in two ways:
<ul><li>Each event has a flag enabling it to be made public, private, or to follow a general setting.
<li>There are general settings for Music and Dance (controlled by Richard or James) for most events
</ul>

<h2>Users</h2><p>
Initially, this is for the committee, but some parts of this system may be made available later to staff / stewards / others as appropriate.<p>

Polly is an example of staff, who needs access to event, venue and participant information and the document storage.<p>

<h2>Need a new table?</h2><p>
Ask Richard.  Most will be very quick to implement.<p>


<h2>Problems?</h2><p>
If something goes wrong, or its not obvious or you wish it could do something extra. <p>

Then either contact Richard (07718 511 432, Email: <a href=mailto:richard@wavwebs.com>Richard@wavwebs.com</a>)
or <a href=AddBug.php>Raise a bug or Feature request</a>.<p>


</div>

<?php include("files/footer.php");  ?>
</body></html>

