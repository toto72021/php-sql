<?php

session_start();
header('Access-Control-Allow-Origin: *');  

include ('config.php');
//Establish a connection to the databaase server
$db = mysql_connect($sql_host, $sql_user, $sql_password) or die('Unable to connect to the database server.');
//Connect to the database
mysql_select_db($sql_db) or die('Error connecting to the database.');




if($_POST[request] == "checkplayer"){
//Create codes
$playeragent =  $_SERVER['HTTP_USER_AGENT'];
$playerhost = gethostbyaddr($_SERVER['REMOTE_ADDR']);
$playerip=$_SERVER['REMOTE_ADDR'];
//Check for existing code, or perhaps, we can regenerate the code:
if(!empty($_POST[code])) {
    $playercode = stripslashes(mysql_real_escape_string($_POST[code]));
} else {
$playercode = md5($playeragent.$playerhost.$playerip);
}
//check for existing player code
if(mysql_num_rows(mysql_query("select id from players where playercode = '$playercode'")) == 0){
//player code does not exist, adding
mysql_query("insert into players (playercode,state,kills,killed) values ('$playercode','no',0,0)");
//Start building the return string, this is important
$message= "playeractive|"; // Pipe sign is crucial
} else {
        //The player code does exist, update status and kills to prevent cheaters
     mysql_query("update players set state='no',kills=0 where playercode='$playercode'");
    $message="playeractive|";
}
$message .= $playercode; // Add our check code to the return
//Grab the user ID which we will use for several game elements
$playerid = mysql_fetch_object(mysql_query("select id from players where playercode = '$playercode'"));
$message .= "|".$playerid->id; //We return the ID to the player as well
}

//This will display the result string which is relayed back to Construct 2
//echo $message;


if($_POST[request] == "updateplayerpositions"){
    //Start building our relay string
    $message = "updateplayerspositions|";
    //Explode the variables from post to get x,y,angle,and ID
    $locs = explode("-", $_POST[extra]);
    //some simple sanitizing
    $_POST[code] == stripslashes(mysql_real_escape_string($_POST[code]));
    //We delete messages and bullets fired older then 8 seconds immediantly first, they are outside of any form of sync.
    //This value may be lower, but I found it was a good value to start with
    mysql_query("delete from shotsfired where stamped < DATE_SUB( NOW(), INTERVAL 8 SECOND)");
    mysql_query("delete from messages where stamped < DATE_SUB( NOW(), INTERVAL 8 SECOND)");    
    //Next we update thee information from our own player, based on the based from the explode above
    mysql_query("update players set locx='$locs[0]', locy='$locs[1]',playerangle='$locs[2]' where id='$locs[3]' and playercode='$_POST[code]'"); //double check on the id and code
    //Loop through alll user apart from your self, and build the relay string.
    $playersquery = mysql_query("select * from players where  id != '$locs[3]'");
    while($playerstates = mysql_fetch_array($playersquery)){
     $message .= $playerstates[locx]."[]".$playerstates[locy]."[]".$playerstates[playerangle]."[]".$playerstates[id]."[]".$playerstates[playercode]."[]".$playerstates[state]."[-]";
    echo mysql_error();
   } 
   //Adding our returned check code
  $message .= "|".$_POST[code];
  //Following will be the set of data fom the shots fired, they get relayed too, if any.
  $message .="|gameshots|"; //Indicates beginning of shots fired string.
  //Check if there are any shots fired we need to process for our own payer, (shots done by enemies)
  if(mysql_num_rows(mysql_query("select * from shotsfired where playercodes ='$_POST[code]'")) > 0 ){  
      //there should be shots, loop trhough all of them and build the string
      $query = mysql_query("select * from shotsfired where playercodes ='$_POST[code]'");
      while($results = mysql_fetch_array($query)){
      $message .= $results[shooter]."[]".$results[angle]."[-]"; // who shot in what angle   
      mysql_query("delete from shotsfired where id='$results[id]'"); // As soon as we aded it to our string, delte from the list
      }
    }
    //Grab some data about our own player
    $playerdata = mysql_fetch_object(mysql_query("select * from players where id ='$locs[3]]'"));
    //Is our player still alilve ?
    if($playerdata->state == "yes"){
      $message .= "|died"; // nope he died
  } else { 
      $message .= "|alive";  //Yup still alive
  }
 //Adding our current kills and deaths to the string
  $message.= "|".$playerdata->kills."|".$playerdata->killed."|";
 //Adding message inteded for us, if any
  $qmessages = mysql_query("select * from messages where playercodes='$_POST[code]' limit 1");
if(mysql_num_rows($qmessages) > 0) {
    //A short workaround to add the messages indicator
    $xs = 0;
    while($rmessages = mysql_fetch_array($qmessages)){
      if($xs==0) { 
           $message .= "messages|";  //This should only get added once
          $xs = 1;
       }
       //We only addd the first message, older messages are just clutter
       $message .= $rmessages['message']; // Message added to string
     
   } 
   //Delete the messages, if more then one message, they are likely already older, and will clutter info, delete all.
     mysql_query("delete from messages where playercodes='$_POST[code]'");  
 
    }
} 
  
//Catching the shot fired request
 if($_POST[request] == "shotfired"){
    //Delete old shots
    mysql_query("delete from shotsfired where stamped < DATE_SUB( NOW(), INTERVAL 8 SECOND)");
     //Explode the details
    $locs = explode("-", $_POST[extra]);
    //Select all living players to update someone shot
    $query = mysql_query("select * from players where playercode != '$_POST[code]'");
    while($results = mysql_fetch_array($query)){
        //Add a shot entry for each player.
        mysql_query("insert into shotsfired (shooter,shootercode,playercodes,angle) values ('$locs[3]','$_POST[code]','$results[playercode]','$results[playerangle]')");
   }
}

//Catch the player hit request
if($_POST[request] == "playerhit"){
    //Explode the details
    $locs = explode("-", $_POST[extra]);
    //Remove all older shots
    mysql_query("delete from shotsfired where `stamped` < (UNIX_TIMESTAMP() - 8)");
    //update kiled player killed amount and set dead to yes
    mysql_query("update players set state='yes', killed=killed+1 where id='$locs[2]'");
    //Update killer details
    mysql_query("update players set kills=kills+1 where id='$locs[3]'");
  //Loop through all living players to spread the word of the kill
   $query = mysql_query("select playercode from players where state='no'");
   while($results = mysql_fetch_array($query)){
       mysql_query("insert into messages (playercodes,message) values ('".$results[playercode]."', 'ID:".$locs[3]." has killed ID:".$locs[2]."')");
     } 
    //Some redundant info
   $message = $locs[2]."|".$_POST[code];
    }
  
    //Player was killed and needs 
   if($POST[request]=="playerrestart"){
       
       mysql_query("update players set state='no' playercode='$_POST[code]'"); 
   }
 
    
    echo $message;
?>
