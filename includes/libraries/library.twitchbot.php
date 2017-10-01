<?php
class VariableClass {
  #region SETTINGS
	public $chan = "starcitizengiveaways";//#StarCitizenGiveaways
	public $server = "irc.twitch.tv";
	public $port = 6667;
	public $nick = "starcitizengiveaways";
	public $pass = "oauth:live_119954557_yLdL8U"; //http://tmi.twitchapps.com <-- Go there for your oauth key.
	#end
	public $socket;
	#region VARIABLES
	//Database variables
	public $db;
	public $storedVariables;
	/*Default storedVariables
	{"adminUsers" :	["quaintshanty"], "ignoredUsers" : ["moobot", "nightbot", "shantypantsbot"], "welcomeToggle" : false, "welcomeMessage" : "Hello, ", "goodbyeToggle" : false, "goodbyeMessage" : "Bye, ", "setCommandLimitTime" : 60, "commandMaxLimit" : 4,	"setPointsTime" : 900, "pointsModifier" : 2}
	*/
	//User lists
	public $users = array();
	public $adminUsers;
	public $modUsers;
	public $ignoredUsers;
	//Command variables
	public $welcomeToggle;
	public $welcomeMessage;
	public $goodbyeToggle;
	public $goodbyeMessage;
	public $setCommandLimitTime;
	public $commandLimitTimer;
	public $commandLimit = 0;
	public $commandMaxLimit;
	//Points system variables
	public $setPointsTime;
	public $pointsTimer;
	public $pointsModifier;
	
	//Raffle
	public $raffleEntries = array();
	#end
}

// Prevent PHP from stopping the script after 30 sec
set_time_limit(0);
// Set the timezone
date_default_timezone_set('America/New_York'); //ENTER YOUR TIMEZONE HERE. FIND AVAILABLE TIMEZONES HERE: http://www.php.net/manual/en/timezones.php
#region VC Vars
$VC = new VariableClass();
$VC->socket = fsockopen($VC->server, $VC->port);
fputs($VC->socket,"PASS $VC->pass\n");
fputs($VC->socket,"NICK $VC->nick\n");
fputs($VC->socket,"JOIN " . $VC->chan . "\n");
$VC->db = false;
$VC->storedVariables = json_decode('select variables from stored variables', true);
$VC->adminUsers = $VC->storedVariables['adminUsers'];
$VC->ignoredUsers = $VC->storedVariables['ignoredUsers'];
$VC->welcomeToggle = $VC->storedVariables['welcomeToggle'];
$VC->welcomeMessage = $VC->storedVariables['welcomeMessage'];
$VC->goodbyeToggle = $VC->storedVariables['goodbyeToggle'];
$VC->goodbyeMessage = $VC->storedVariables['goodbyeMessage'];
$VC->setCommandLimitTime = $VC->storedVariables['setCommandLimitTime'];
$VC->commandLimitTimer = $VC->setCommandLimitTime;
$VC->commandMaxLimit = $VC->storedVariables['commandMaxLimit'];
$VC->setPointsTime = $VC->storedVariables['setPointsTime'];
$VC->pointsTimer = $VC->setPointsTime;
$VC->pointsModifier = $VC->storedVariables['pointsModifier'];
#end

function StripTrim($strip, $trim){
	$strippedString = (string)stripcslashes(trim($strip, $trim));
	$strippedString = preg_replace('~[.[:cntrl:][:space:]]~', '', $strippedString);
	return $strippedString;
}

function BasicChat($socket, $chan, $text){
	fputs($socket, "PRIVMSG ". $chan . " :" . $text . " \n");
}

function UserCommands($users, $sender, $socket, $chan, $message, $rawcmd, $args){
	//Verify that there are arguments.
	if(!is_null($args)){
		switch($rawcmd[1]) {
			case "!sayit" :
				fputs($socket, "PRIVMSG " . $chan . " :" . $args . "\n");
				break;
			case "!md5" :
				fputs($socket, "PRIVMSG " . $chan . " :MD5 " . md5($args) . "\n");
				break;
			
			case "!quote" :
				if(count($args)>1){
					fputs($socket, "PRIVMSG " . $chan . " :Quote system coming soon.\n");
				}
				else {
					
				}				
				break;
		}
	}
	else {
		switch($rawcmd[1]) {
			case "!points" :
				for($i=0; $i < count($users); $i++){
					if($sender == $users[$i]['name']){
						fputs($socket, "PRIVMSG " . $chan . " :" . $sender . ", you have " . $users[$i]['points'] . " points.\n");
						break;
					}
				}
				break;
				
			case "?points" : 
				fputs($socket, "PRIVMSG " . $chan . " :With points, you can use them to play games (coming soon) and enter in giveaways (coming soon), or become chosen for special events with the broadcaster. The more points, the better the rewards!\n");
				break;
			case "!timeHere" :
				for($i=0; $i < count($users); $i++){
					if($sender == $users[$i]['name']){
						fputs($socket, "PRIVMSG " . $chan . " :" . $sender .  ", you have been here for " . secondsToTime(round((microtime(true) - $users[$i]['time_joined']))) . ".\n");
						break;
					}
				}
		}
	}
}
function secondsToTime($seconds) {
    $dtF = new DateTime("@0");
    $dtT = new DateTime("@$seconds");
    return $dtF->diff($dtT)->format('%a days, %h hours, %i minutes and %s seconds');
}
function SaveVariable($VC, $db){
	$storedVariablesJSONed = json_encode($VC->storedVariables);
	$db->query("UPDATE stored_variables SET variables = '{$storedVariablesJSONed}'");
}
function AdminCommands($message, $rawcmd, $args, $VC){
	//Verify that there are arguments.
	if(!is_null($args))
    {
		switch($rawcmd[1]) 
        {
			case "!mod" :
				if(!in_array(StripTrim($args, ":"), $VC->adminUsers)) 
                {
					$VC->adminUsers[] = StripTrim($args, ":");
					$VC->storedVariables['adminUsers'][] = StripTrim($args, ":");;
					SaveVariable($VC, $VC->db);
				}
		}
	}
	else 
    {
		switch($rawcmd[1]) {
			case "!users" :
				break;
		}
	}
}
function AddPointsToAll($users, $pointsModifier, $db) {
	for($i = 0; $i < count($users); $i++){
		$users[$i]['points'] += $pointsModifier;
		$db->query("UPDATE users SET points = '{$users[$i]['points']}' WHERE name = '{$users[$i]['name']}'");
	}
	
	return $users;
}

// Set timout to 1 second
if (!stream_set_timeout($VC->socket, 1)) die("Could not set timeout");
while(1) {
	
	//points timer
	if($VC->pointsTimer > 0) {
		$VC->pointsTimer--;
	}
	else {
		$VC->pointsTimer = $VC->setPointsTime;
		$VC->users = AddPointsToAll($VC->users, $VC->pointsModifier, $VC->db);
	}
	
	//commands limit
	if($VC->commandLimitTimer > 0) {
		$VC->commandLimitTimer--;
	}
	else {
		$VC->commandLimitTimer = $VC->setCommandLimitTime;
		$VC->commandLimit = 0;
	}
	
	while($data = fgets($VC->socket)) {
	    flush();
	    
		//Separate the incoming data by spaces and add them to the the message variable as a list.
		$message = explode(' ', $data);
		
		//If the server sends us a ping, pong those suckers back!
		if($message[0] == "PING"){
        	fputs($VC->socket, "PONG " . $message[1] . "\n");
	    }
		else {
			echo $data;
		}
	
		if($message[1] == "353"){
			//Adds all current users to the user list.
			for($i = 5; $i < count($message); $i++){
				$strippedUser = StripTrim($message[$i], ":"); //Trim is needed for the first user since it starts with :, sadly.
				if(!in_array($strippedUser, $VC->ignoredUsers) && !in_array($strippedUser, $VC->users)){
					$db_name = $VC->db->querySingle("SELECT name FROM users WHERE name = '" . $strippedUser . "'", false);
					
					if($db_name) {
						$db_points = $VC->db->querySingle("SELECT points FROM users WHERE name = '" . $strippedUser . "'", false);
						$VC->users[] = array(
								'name' => $db_name,
								'points' => $db_points,
								'time_joined' => microtime(true)
						); //Add them to the users list.
					} else {
						$VC->users[] = array(
								'name' => $strippedUser,
								'points' => 0,
								'time_joined' => microtime(true)
						); //Add them to the users list without loading from the DB.
						$VC->db->query("INSERT INTO users (name, points) VALUES ('" . $strippedUser . "', 0)"); //Add user to DB
					}
				}
			}
		}
		elseif($message[1] == "JOIN"){
			$temp = explode('!', (string)$message[0]);
			$joinedUser = StripTrim($temp[0], ":");
			if(!in_array($joinedUser, $VC->ignoredUsers) && !in_array($joinedUser, $VC->users)){
				$db_name = $VC->db->querySingle("SELECT name FROM users WHERE name = '" . $joinedUser . "'", false);
				
				if($db_name) {
					$db_points = $VC->db->querySingle("SELECT points FROM users WHERE name = '" . $joinedUser . "'", false);
					$VC->users[] = array(
							'name' => $db_name,
							'points' => $db_points,
								'time_joined' => microtime(true)
					); //Add them to the users list.
				} else {
					$VC->users[] = array(
							'name' => $joinedUser,
							'points' => 0,
							'time_joined' => microtime(true)
					); //Add them to the users list without loading from the DB.
					$VC->db->query("INSERT INTO users (name, points) VALUES ('" . $joinedUser . "', 0)"); //Add user to DB
				}
				if($VC->welcomeToggle){
					BasicChat($VC->socket, $VC->chan, $VC->welcomeMessage . $joinedUser . "!");
				}
			}
		}
		elseif($message[1] == "PART"){
			$temp = explode('!', (string)$message[0]);
			$partedUser = StripTrim($temp[0], ":");
			
			for($i=0; $i < count($VC->users); $i++){
				if($partedUser == $VC->users[$i]['name']){
					unset($VC->users[$i]['name']); //Remove them from the users list.
					$VC->users = array_values($VC->users);
					unset($VC->users[$i]['points']);
					$VC->users = array_values($VC->users);
					unset($VC->users[$i]['time_joined']);
					$VC->users = array_values($VC->users);
					unset($VC->users[$i]);
					$VC->users = array_values($VC->users);
					break;
				}
			}
			if($VC->goodbyeToggle){
				if(!in_array($partedUser, $VC->ignoredUsers)){
					BasicChat($VC->socket, $VC->chan, $VC->goodbyeMessage . $partedUser . "!");
				}
			}
		}
		elseif($message[1] == "MODE"){
			// Add mods
		}
		elseif($message[1] == "PRIVMSG"){
			echo "Entered...";
			if($VC->users!=NULL && count($VC->users)>0) {
				$temp = explode('!', (string)$message[0]);
				$sender = StripTrim($temp[0], ":");
				
				$rawcmd = explode(':', $message[3]); //Get the raw command from the message.
				
				//Get all arguments after the raw command.
				$args = NULL;
				if(count($message) > 4){
					for($i = 4; $i < count($message); $i++){
						$args .= $message[$i] . ' ';
					}
				}
				
				$rawcmd = preg_replace('~[.[:cntrl:][:space:]]~', '', $rawcmd);
				
				if(substr($rawcmd[1], 0, 1) == "!" && $VC->commandLimit < ($VC->commandMaxLimit + 1))
					$VC->commandLimit++;
				echo "Commanding...";
				echo $rawcmd[1];
				if($rawcmd[1] == "!raffle") {
					echo "Raffling...";
					for($i=0; $i < count($users); $i++){
						if($sender == $VC->users[$i]['name']){
							echo $i . "PLACE...";
							if($VC->users[$i]['points'] >= 5){
								echo "Success";
								if(!in_array($VC->users[$i]['name'], $VC->raffle))
								{
									$VC->users[$i]['points'] -= 5;
									fputs($$VC->socket, "PRIVMSG " . $VC->chan . " :You are entered in to the raffle!\n");
								}
								else
								{
									fputs($$VC->socket, "PRIVMSG " . $VC->chan . " :You are already entered!\n");
								}
							}
							else {
								echo "Fail";
								fputs($VC->socket, "PRIVMSG " . $VC->chan . " :You need 5 or more points to enter the raffle.\n");
							}
							break;
						}
					}
				}
				else
				{
					//Make it so users can't spam commands.
					if($VC->commandLimit <= $VC->commandMaxLimit)
						UserCommands($VC->users, $sender, $VC->socket, $VC->chan, $message, $rawcmd, $args);
					
					//replace this will array_search by assigning an admin variable in the future.
					if(in_array($sender, $VC->adminUsers)){
						AdminCommands($message, $rawcmd, $args, $VC);
					}
				}
			}
		}
	}
	
	if (!feof($VC->socket)) {
		continue;
	}
	
	sleep(1);
}
?>
