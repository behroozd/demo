<style>
.inout-row{
	margin: 15px 15px 15px 15px;
	font-family: arial;
	font-size: 16px;
	width: 100%;
	height: 110px;
	border-bottom: thin solid #888888;
}

.inout-row img{
	height: 30px;
	width:60px;
}
.inout-row .poke{
	height: 30px;
	width : 30px;
	cursor: pointer;
	margin-left:10px;
}

.timestamp {
	float: right;
	height: 90px;
	margin-right: 25px;
	margin-top: 25px;
}

.username {
	font-size: 30px;
}
</style>
<?php
/*
inout.php?uuid=E2C56DB5DFFB48D2B060777777777777&major=777&minor=1&activity=inout&macaddress=f8:a9:d0:8c:08:a8

*/
function display_in_html($retVal, $activity){
	/*echo "<pre>";
	print_r($retVal);
	echo "</pre>";
	*/
	
//	echo count($retVal['users']);
	
	display_header();
	
	foreach ($retVal['users'] as $user)
	{
		echo '<div class="inout-row">';

		$dp = date_parse($user['timestamp']);
		$timestamp = strtotime($user['timestamp']);
		$format = "N";
		
		echo '<div class="timestamp">' ; 
		
		echo date("D M j H:i", $timestamp);
		
		echo "</div>";

		

		echo '<div class="username">' . $user['fname'].' '.$user['lname'] . "</div>";
		

		if ($user['inout']=="1")
		{
			echo '<img src="img/In-Arrow.png" />';
		}
		else
		{
			echo '<img src="img/Out-Arrow.png" />';
		}

		echo "</div>";
	}

	display_footer();

}

function display_header()
{
	echo "<table>";
}

function display_footer(){
	echo "</table>";
}

//---------------------------------------------
function displayPokeHTML($retVal, $activity, $senderName, $senderMacAddress ){
	?>
	<html>
	<head>
		<title><?php echo $senderName; ?></title>
		<script src='https://cdn.firebase.com/js/client/2.2.1/firebase.js'></script>
		<script src='https://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js'></script>
		<script type="text/javascript">
			var myDataRef = new Firebase('https://qccht4wammb.firebaseio-demo.com/');
			myDataRef.remove();
			function showPokeTo(id){
				$("#messagesDiv_"+id).show();
			}
			function sendPokeTo(id){
				var name = $("#messagesDiv_"+id+' #name').val();
				var sMac = $("#messagesDiv_"+id+' #sMac').val();
				var tMac = $("#messagesDiv_"+id+' #tMac').val();
				var text = $("#messagesDiv_"+id+' #text').val();
	
				myDataRef.set({senderName: name, senderMac: sMac, to:tMac, text: text, id: id});
				myDataRef.set('User ' + name + ' says ' + text);
	
				myDataRef.push({senderName: name, senderMac: sMac, to:tMac, text: text, id: id});
				$("#messagesDiv_"+id).hide();
			}
			myDataRef.on('child_added', function( snapshot ){
				var message = snapshot.val();
				var myMac   = '<?php echo $_REQUEST['macaddress']; ?>';
				if(message.to==myMac){
					displayChatMessage( message.senderName, message.text, message.id );
				}
			});
			
			function displayChatMessage( name, text, id ){
				var strDate = new Date();
				var strTime = strDate.getHours()+':'+strDate.getMinutes()+":"+strDate.getSeconds();
				$('<div/>').text('poked you ('+strTime+')').prepend($('<em/>').text(name+' ')).appendTo($('#inMessage'));
				$('#inMessage')[0].scrollTop = $('#inMessage')[0].scrollHeight;
//				$('<div/>').text(text).prepend($('<em/>').text(name+': ')).appendTo($('#inMessage_'+id));
//				$('#inMessage_'+id)[0].scrollTop = $('#inMessage_'+id)[0].scrollHeight;
			};
		</script>
		<style>
			span{ font-weight:normal;font-size:12px; }
		</style>
	</head>
	<body>
	<div id="inMessage"></div>
	<?php
	display_header();
	foreach ($retVal['users'] as $user){
		$dp = date_parse($user['timestamp']);
		$timestamp = strtotime($user['timestamp']);
		$format = "N";
		?>
		<div class="inout-row">
			<div class="timestamp"><?php echo date("D M j H:i", $timestamp); ?></div>
			<div class="username">
				<?php echo $user['fname'].' '.$user['lname']; ?>
				<img src="img/Hand-Touch-2-icon.png" class="poke" title="poke" alt="poke" onClick="sendPokeTo('<?php echo $user['user_id'];?>')" />
				<div style="display:none;" id="messagesDiv_<?php echo $user['user_id'];?>">
					<span>Message: </span><input type="text" id="text" value="you have been poked" placeholder='Message' />
					<input type="hidden" id="tMac" value="<?php echo $user['macaddress'];?>" />
					<input type="hidden" id="name" value="<?php echo $senderName;?>"/>
					<input type="hidden" id="sMac" value="<?php echo $senderMacAddress;?>" />
					<button onClick="sendPokeTo('<?php echo $user['user_id'];?>')">send</button>
					<button onClick="$('#messagesDiv_<?php echo $user['user_id'];?>').hide();">cansel</button>
				</div>
				<!--<div id="inMessage_<?php echo $user['user_id'];?>"></div>-->
			</div>
		</div><br />
		<?php
		/*
		echo '<div class="inout-row">';
			echo '<div class="timestamp">' ; 
				echo date("D M j H:i", $timestamp);
			echo "</div>";
			echo '<div class="username">';
				echo $user['fname'].' '.$user['lname'];
//				echo '<button>Poke</button>';
				echo '<img src="img/Hand-Touch-2-icon.png" class="poke" title="poke" alt="poke" onclick="alert(\''.$user['fname'].' '.$user['lname'].'\')" />';
			echo "</div>";
		echo "</div>";
		*/
	}
	display_footer();
	?>
	</body>
	</html>
	<?php
}
//---------------------------------------------








//  ----------------------------------------------------- Start
require_once 'filemaker/FileMaker.php';
require_once 'php_functions.php';
$iniParameters = parse_ini_file("config.ini",true);
if( $iniParameters==false ){
	$retVal=array(
		"result"  => 0,
		"message" => "config file not found"
	);
}else{
	//------------------------------------------------
	$keys = array_keys($_REQUEST);
	$inData=array();
	foreach($keys as $key){
		$inData[strtolower($key)]=$_REQUEST[$key];
	}
	//------------------------------------------------
	$fm = new FileMaker(
				$iniParameters['database']['FM_FILE'],
				$iniParameters['database']['FM_HOST'],
				$iniParameters['database']['FM_USER'],
				$iniParameters['database']['FM_PASS']);
				
	//------------------------------------------------
	$activity  = ( !isset($inData['activity']) || trim($inData['activity'])=="")? 'inout' : trim(strtolower($inData['activity']));
	$macADD    = $inData['macaddress'];
	$temp      = make_beaconid( $fm, $macADD, $inData['uuid'], $inData['major'], $inData['minor'] );
	if( $temp['beaconid']=="" ){
		echo json_encode($temp['retVal'],JSON_PRETTY_PRINT);
		exit();
	}else{
		$beaconid=$temp['beaconid'];
	}
	//------------------------------------------------
	if( $inData['macaddress']=='' ){
		$retVal=array(
			"result"   => 0,
			"activity" => $activity,
			"message"  => "invalid macaddress "
		);
	}else{
		$request = $fm->newFindCommand( 'user' );
		$request->addFindCriterion('macaddress', $macADD   );
//		$request->addFindCriterion('beaconid'  , $beaconid );
		$result  = $request->execute();
		if (FileMaker::isError($result)) {
			if($result->code==401){
				$retVal=array(
					"result"   => 0,
					"activity" => $activity,
					"message"  => "Authentication failed"
				);
			}else{
				$retVal=array(
					"result"   => 0,
					"activity" => $activity,
					"message"  => addslashes($result->getMessage())
				);
			}
		}else{
			//----------------------------------------
			$records  = $result->getRecords();
			$userName = $records[0]->getField('fname').' '.$records[0]->getField('lname');
			$temp     = checkUserTable( $records[0], $fm, $macADD, $beaconid );
			if($temp['userID']!=0){
				$userID  = $temp['userID'];
				$retVal="";
				switch( strtolower( $inData['presence'] ) ){
					case 'in':{
						$retVal = setInOut( $fm, $userID, 1, $beaconid );
						break;
					}
					case 'out':{
						$retVal = setInOut( $fm, $userID, 0, $beaconid );
						break;
					}
				}
			}else{
				$retVal=$temp['retVal'];
			}
			if($retVal==""){
				switch($activity){
					case 'inout':{
//						$retVal = activity_inout($fm, $activity, $userID );
						$retVal = activity_inout($fm, $activity, $beaconid );
						display_in_html($retVal, $activity);
						break;
					}
					case 'poke':{
						$retVal = activity_poke($fm, $activity, $beaconid );
						displayPokeHTML($retVal, $activity, $userName, $macADD);
						break;
					}
					/*
					case 'list':{
						$locID  = trim($inData['locationid']);
						$Type   = trim($inData['type'      ]);
						if( $locID=='' || $Type=='' ){
							$retVal=array(
								"result"   => 0,
								"activity" => $activity,
								"message"  => "invalid user name or macaddress or Location id or Type"
							);
						}else{
							$retVal = activity_list($fm, $activity, $locID, $Type);
						}
						break;
					}
					case 'listall':{
						$userNA = trim($inData['username'  ]);
						$macADD = trim($inData['macaddress']);
						if($userNA=='' && $macADD=='' ){
							$retVal=array(
								"activity" => $activity,
								"result"   => 0,
								"message"  => "invalid user name or macaddress "
							);
						}else{
							$retVal = activity_listall($fm, $activity, $userID, $macADD);
						}
						break;
					}
					*/
					default:{
						echo '<div class="username">invalid activity</div>';
					}
				}
			}
		}
	}
}
//echo json_encode($retVal,JSON_PRETTY_PRINT);




//--------------------------------------------------------------------------------------------
?>
