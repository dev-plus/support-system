<?php	

if(empty($_POST)){
	header("Location: ./");
}

	include "cache.php";

	$c = new Cache();

//saveCommandResponse
if(isset($_POST['commandId'])){
	$commands = json_decode(file_get_contents("commands"), true);
	$commands[$_POST['commandId']]['sucessful'] = $_POST['successfull'];
	$commands[$_POST['commandId']]['response'] = utf8_encode($_POST['response']);
	file_put_contents("commands", json_encode($commands));
}

//save systemInformations
if(isset($_POST['systemInformation'])){
	$clientIdentifier = $_POST['clientIdentifier'];
	$Labels = json_decode(file_get_contents("cache/clientLabels.json"), true);
	if(isset($Labels[$clientIdentifier])){
		$label = $Labels[$clientIdentifier];
	}else{
		$label = "noch nicht gesetzt";
	}
	$c->store($_POST['clientIdentifier'], array("clientLabel" => $label, "lastUpdate" => round(microtime(true)*1000) ,"systemInformation" => $_POST['systemInformation']));

	//prepare Commands
	$commands = json_decode(file_get_contents("commands"), true);

	$toSendCommands = array();
	
	foreach($commands as $id => $command){
		if($command['clientId'] == $_POST['clientIdentifier'] && $command['started'] == 0){
			$toSendCommands[$id] = array(
				"id" => $id,
				"type" => $command['type'],
				"data" => $command['data']
			);
			$commands[$id]['started'] = 1; 
		}
	}

	file_put_contents("commands", json_encode($commands));
	echo json_encode(array("commands" => $toSendCommands));
}

/* command
*	id
*	type
*	data
* clientId
* started
*	sucessful
*	response
*/
?>

