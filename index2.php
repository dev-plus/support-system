<?php

if($_POST['action'] == "getClients"){
  include "cache.php";
$c = new Cache();

  echo json_encode($c->retrieveAll());
  die();
}

if($_POST['action'] == "updateClientLabel"){
	$Labels = json_decode(file_get_contents("cache/clientLabels.json"), true);
	$Labels[$_POST['uuid']] = $_POST['newLabel'];
	file_put_contents("cache/clientLabels.json", json_encode($Labels));
}

?>

<html>
  
  <head>
    <link rel="stylesheet" type="text/css" href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
    <script src="//code.jquery.com/jquery-3.2.1.min.js"></script>
    <script src="//maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"> </script>
  </head>
  
  <body>
    
    <!-- Modal -->
    <div id="dieseModal" class="modal fade bd-example-modal-lg" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-lg">
        <div id="dieseModalContent" class="modal-content">
          ...
        </div>
       </div>
    </div>
    
    <div id="List">
      <table id="inventoryTable" style="width: 100%; float: center;">
        <tr>
					<td><center><img src="http://www.vlat.co/img/loading.gif" /></center></td>
        </tr>
				<tr>
					<td><center>Laden..</center></td>
				</tr>
      </table>
    </div>
  </body>
    <script>
var Cache = {};
      
      setInterval(function(){
        $.ajax({
					type: 'POST',
					url: 'index2.php',
					data: "action=getClients",
					dataType: 'json',
					success: function(result) {
            Cache = result;
                          var printVar = `<tr>
                                <th>Label</th>
                                <th>User</th>
                                <th>CPU</th>
                                <th>Status</th>
                              </tr>`;
            
            for (var key in result) {
              if (!result.hasOwnProperty(key)) continue;
              var o = result[key];
              
              if(typeof o.systemInformation.users == "undefined"){
                 var user = "-";
               }else{
                 var user = o.systemInformation.users["0"].user;
               }
              
              printVar += `<tr onclick='clientDetails("`+key+`")'>
                             <td>`+o.clientLabel+`</td>
                             <td>`+user+`</td>
                             <td>
                                <div class="progress" style="width: 80%;">
                                  <div class="progress-bar progress-bar-primary" role="progressbar" style="width:`+o.systemInformation.currentLoad.currentload_system+`%">System</div>
                                  <div class="progress-bar progress-bar-info" role="progressbar" style="width:`+o.systemInformation.currentLoad.currentload_user+`%">User</div>
                                  <div class="progress-bar progress-bar-success" role="progressbar" style="width:`+o.systemInformation.currentLoad.currentload_idle+`%">Idle</div>
                                </div> 
                              </td>
                              <td>`+getLastResponse(o.lastUpdate)+`</td>
                          </tr>`;
            }
            $("#inventoryTable").html(printVar);
					}
				});
      }, 3000);
      
      function clientDetails(uuid){
        var Client = Cache[uuid];
        var content = `<table width="100%">
                        <!-- Allgemein -->
                        <tr><th colspan="2"><h2>Allgemein</h2></th></tr>
                        <tr>
                          <th>Label</th> <td><input id="`+uuid+`_clientName" value="`+Client.clientLabel+`" onchange='changeClientName("`+uuid+`")' /></td>
                        </tr>
                        <tr>
                          <th>uuid</th> <td>`+uuid+`</td>
                        </tr>
                        <tr>
                          <th>NodeJS version</th> <td>`+Client.systemInformation.node+`</td>
                        </tr>
                        <tr>
                          <th>letzte Aktivität</th> <td>`+Client.lastUpdate+`</td>
                        </tr>
                        <!-- CPU -->
                        <tr><th colspan="2"><h2>CPU</h2></th></tr>
                        <tr>
                          <th>Durchsch. Taktfrequenz</th> <td>`+Client.systemInformation.cpuCurrentspeed.avg+` GHz</td>
                        </tr>
                        <tr>
                          <th>anz Cores</th> <td>`+Client.systemInformation.currentLoad.cpus.length+`</td>
                        </tr>
                        <!-- RAM -->
                        <tr><th colspan="2"><h2>RAM</h2></th></tr>
                        <tr>
                          <th>RAM belegt</th> <td><div width="80%" class="progress"><div class="progress-bar" style="width:`+Number(Client.systemInformation.mem.used)/Number(Client.systemInformation.mem.total)*100+`%">`+Math.round(Number(Client.systemInformation.mem.used)/1000000)/1000+` GB / `+Math.round(Number(Client.systemInformation.mem.total)/1000000)/1000+` GB</div></div></td>
                        </tr>
                        <!-- Disk -->
                        <tr><th colspan="2"><h2>Disks</h2></th></tr>
                        `+getDiskRows(Client.systemInformation.fsSize)+`
                        <!-- Akku -->
                        <tr><th colspan="2"><h2>Akku</h2></th></tr>
                        <tr>
                          <th>Aufladen</th> <td>`+Client.systemInformation.battery.ischarging+`</td>
                        </tr>
                        <tr>
                          <th>Ladezustand</th> <td><div width="80%" class="progress"><div class="progress-bar" style="width:`+Client.systemInformation.battery.percent+`%">`+Client.systemInformation.battery.percent+`%</div></div></td>
                        </tr>
                        <!-- Netzwerk -->
                        <tr><th colspan="2"><h2>Nettzwerk</h2></th></tr>
                        <tr>
                          <th>Durchschn. Latenz (Google)</th><td>`+Client.systemInformation.inetLatency+`</td>
                        </tr>
                       </table>`;
        openModal(content);
      }
      
      function getLastResponse(lastResponse){
        var curMillis = (new Date()).getTime();
        if(Number(lastResponse) + 30000 > curMillis){
                                            //smaller than 5s
                                            var status = "<b>online</b>";
                                        }else if(Number(lastResponse) + 60000 > curMillis){
                                            //smaller than 1min
                                            var seconds = (Math.round((Number(curMillis)-Number(lastResponse))/1000));
                                            var status = seconds+" Sekunden offline";
                                        }else if(Number(lastResponse) + 3600000 > curMillis){
                                            //smaller than 1h
                                            var minutes = (Math.round((Number(curMillis)-Number(lastResponse))/60000));
                                            var status = minutes+" Minuten offline";
                                        }else if(Number(lastResponse) + 86400000 > curMillis){
                                            //smaller than 1d
                                            var hours = (Math.round((Number(curMillis)-Number(lastResponse))/3600000));
                                            var status = hours+" Stunden offline";
                                        }else if(Number(lastResponse) + 604800000 > curMillis){
                                            //smaller than 1w
                                            var days = (Math.round((Number(curMillis)-Number(lastResponse))/86400000));
                                            var status = days+" Tage offline";
                                        }else if(Number(lastResponse) + 2592000000 > curMillis){
                                            //smaller than 1m
                                            var weeks = (Math.round((Number(curMillis)-Number(lastResponse))/604800000));
                                            var status = weeks+" Wochen offline";
                                        }
        return status;
      }
      
      function getDiskRows(fsSize){
        var toReturn = "";
        for (var key in fsSize) {
              if (!fsSize.hasOwnProperty(key)) continue;
              var o = fsSize[key];
        
            toReturn += `<tr><th>`+o.mount + ` (`+o.type+`)</th><td><div width="80%" class="progress"><div class="progress-bar" style="width:`+Number(o.used)/Number(o.size)*100+`%">`+Math.round(Number(o.used)/1000000)/1000+` GB / `+Math.round(Number(o.size)/1000000)/1000+` GB</div></div></td></tr>`;  
          
        }
        return toReturn;
      }
      
			function changeClientName(uuid){
				 $.ajax({
					type: 'POST',
					url: 'index2.php',
					data: "action=updateClientLabel&uuid=" + uuid + "&newLabel=" + $('#'+uuid+'_clientName').val() });
			}
			
      function openModal(content){
        $('#dieseModalContent').html(content);
        $('#dieseModal').modal('show');
      }
    </script>
</html>
