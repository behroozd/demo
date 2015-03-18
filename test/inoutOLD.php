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
function display_in_html($retVal, $activity)
{
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

function display_footer()
{
	echo "</table>";
}









//  ----------------------------------------------------- Start




require_once 'filemaker/FileMaker.php';
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
/*	
	if (!isset($inData['macaddress']))
	{
		$keys = array_keys($_REQUEST);
		$inData=array();
		foreach($keys as $key){
			$inData[strtolower($key)]=$_REQUEST[$key];
		}
	}
*/
	
	$activity  = ( !isset($inData['activity']) || trim($inData['activity'])=="")? 'inout' : trim(strtolower($inData['activity']));
	$macADD    = $inData['macaddress'];
	$temp      = make_beaconid( $fm, $macADD, $inData['uuid'], $inData['major'], $inData['minor'] );
	if( $temp['beaconid']=="" ){
		echo json_encode($temp['retVal'],JSON_PRETTY_PRINT);
		exit();
	}else{
		$beaconid=$temp['beaconid'];
	}
//	$beaconid  = "{$inData['uuid']}|{$inData['major']}|{$inData['minor']}";
	//------------------------------------------------
//	if( $inData['macaddress']=='' || $inData['uuid']=='' || $inData['major']=='' || $inData['minor']=='' ){
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
			$records = $result->getRecords();
//			$userID  = checkUserTable( $records[0]->getRecordId();
			$temp    = checkUserTable( $records[0], $fm, $macADD, $beaconid );
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
						break;
					}
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
					default:{
						$retVal=array(
							"activity" => $activity,
							"result"   => 0,
							"message"  => "invalid activity"
						);
					}
				}
			}
		}
	}
}
//echo json_encode($retVal,JSON_PRETTY_PRINT);


display_in_html($retVal, $activity);


//--------------------------------------------------------------------------------------------
function activity_list($fm, $activity, $locID, $Type){
	$layout_name = 'menu';
	$layouts = $fm->listLayouts();
	if( FileMaker::isError($layouts) ){
		$retVal=array(
			"result"   => 0,
			"activity" => $activity,
			"message"  => addslashes($layouts->getMessage())
		);
	}else{
		$request =$fm->newCompoundFindCommand($layout_name);
		$find =$fm->newFindRequest($layout_name);
		$find->addFindCriterion('locationId', $locID);
//		$find->addFindCriterion('type'      , $Type );
		$find->addFindCriterion('type'      , 'menu' );
		$request->add(1,$find );
		$request->addSortRule('itemname', 1, FILEMAKER_SORT_ASCEND);
		$result  = $request->execute();
		if (FileMaker::isError($result)) {
			$retVal=array(
				"result"   => 0,
				"activity" => $activity,
				"message"  => addslashes($result->getMessage())
			);
		}else{
			$records = $result->getRecords();
			if (FileMaker::isError($records)) {
				$retVal=array(
					"result"   => 0,
					"activity" => $activity,
					"message"  => addslashes($records->getMessage())
				);
			}else{
				$retVal=array(
					"result"     => 1,
					"activity"   => $activity,
					"locationId" => $locID,
					"type"       => $Type,
					"count"      => count($records),
					"items"      => array()
				);
				foreach($records as $record){
					$item = array(
						"id"         => $record->getRecordId(),
//						"type"       => $record->getField('type'      ),
//						"locationId" => $record->getField('locationId'),
						"itemname"   => $record->getField('itemname'  ),
						"price"      => $record->getField('price'     )
					);
					array_push( $retVal['items'] , $item );
				}
			}
		}
	}
	return $retVal;
}
//--------------------------------------------------------------------------------------------
function activity_inout($fm, $activity, $beaconid ){
	$request = $fm->newFindCommand( 'user' );
	$request->addFindCriterion('beaconid' , $beaconid);
	$result  = $request->execute();
	$records = $result->getRecords();
	if (FileMaker::isError($records)) {
		$retVal=array(
			"result"   => 0,
			"activity" => $activity,
			"message"  => addslashes($records->getMessage())
		);
	}else{
		$retVal=array(
			"result"   => 1,
			"activity" => $activity,
			"count"    => count($records),
			"users"    => array()
		);
		foreach($records as $record){
			$user = array(
				"user_id"    => $record->getRecordId(),
//					"username"   => $record->getField('userName'  ),
				"fname"      => $record->getField('fname'     ),
				"lname"      => $record->getField('lname'     ),
				"email"      => $record->getField('email'     ),
//					"password"   => $record->getField('password'  ),
				"macaddress" => $record->getField('macaddress'),
				"beaconid"   => $record->getField('beaconid'  ),
				"timestamp"  => $record->getField('timestamp' ),
//					"image"      => $record->getField('image'     ),
				"inout"      => $record->getField('inout'     )
			);
			array_push( $retVal['users'] , $user );
		}
	}
	return $retVal;
}
/*
function activity_inoutOLD($fm, $activity, $userID ){
	$layout_name = 'user';
	$layouts = $fm->listLayouts();
	if( FileMaker::isError($layouts) ){
		$retVal=array(
			"result"   => 0,
			"activity" => $activity,
			"message"  => addslashes($layouts->getMessage())
		);
	}else{
		$request = $fm->newFindAllCommand($layout_name);
		$result  = $request->execute();
		$records = $result->getRecords();
		if (FileMaker::isError($records)) {
			$retVal=array(
				"result"   => 0,
				"activity" => $activity,
				"message"  => addslashes($records->getMessage())
			);
		}else{
			$retVal=array(
				"result"   => 1,
				"activity" => $activity,
				"count"    => count($records),
				"users"    => array()
			);
			foreach($records as $record){
				$user = array(
					"user_id"    => $record->getRecordId(),
//					"username"   => $record->getField('userName'  ),
					"fname"      => $record->getField('fname'     ),
					"lname"      => $record->getField('lname'     ),
					"email"      => $record->getField('email'     ),
//					"password"   => $record->getField('password'  ),
					"macaddress" => $record->getField('macaddress'),
					"beaconid"   => $record->getField('beaconid'  ),
					"timestamp"  => $record->getField('timestamp' ),
//					"image"      => $record->getField('image'     ),
					"inout"      => $record->getField('inout'     )
				);
				array_push( $retVal['users'] , $user );
			}
		}
	}
	return $retVal;
}
*/
//--------------------------------------------------------------------------------------------
function setInOut( $fm, $userID, $inout, $beaconid ){
	$retVal="";
	$edit = $fm->newEditCommand( "user", $userID);
	$edit->setField('inout', $inout);
	$result = $edit->execute();
	if (FileMaker::isError($result)) {
		$retVal=array(
			"result"   => 0,
			"message"  => addslashes($result->getMessage())
		);
	}else{
		$newRec = $fm->newAddCommand('inout');
		$newRec->setField('userid'   , $userID   );
		$newRec->setField('inout'    , $inout    );
		$newRec->setField('beaconid' , $beaconid );
		
		$result = $newRec->execute();
		if (FileMaker::isError($result)) {
			$retVal=array(
				"result"   => 0,
				"message"  => addslashes($result->getMessage())
			);
		}
	}
	return $retVal;
}
//--------------------------------------------------------------------------------------------
function activity_listall($fm, $activity){
	$layout_name = 'menu';
	$layouts = $fm->listLayouts();
	if( FileMaker::isError($layouts) ){
		$retVal=array(
			"result"   => 0,
			"activity" => $activity,
			"message"  => addslashes($layouts->getMessage())
		);
	}else{
		$request = $fm->newFindAllCommand($layout_name);
		$request->addSortRule('itemname', 1, FILEMAKER_SORT_ASCEND);
		
		$result  = $request->execute();
		if (FileMaker::isError($result)) {
			$retVal=array(
				"result"   => 0,
				"activity" => $activity,
				"message"  => addslashes($result->getMessage())
			);
		}else{
			$records = $result->getRecords();
			if (FileMaker::isError($records)) {
				$retVal=array(
					"result"   => 0,
					"activity" => $activity,
					"message"  => addslashes($records->getMessage())
				);
			}else{
				$retVal=array(
					"result"   => 1,
					"activity" => $activity,
					"count"    => count($records),
					"items"    => array()
				);
				foreach($records as $record){
					$item = array(
						"id"         => $record->getRecordId(),
						"type"       => $record->getField('type'      ),
						"locationId" => $record->getField('locationId'),
						"itemname"   => $record->getField('itemname'  ),
						"price"      => $record->getField('price'     )
					);
					array_push( $retVal['items'] , $item );
				}
			}
		}
	}
	return $retVal;
}
//--------------------------------------------------------------------------------------------
function checkUserTable( $inRecord, $fm, $macADD, $beaconid ){
	
	$retVal=array(
			"userID" => 0,
			"retVal" => array(
				"result"   => 0,
				"message"  => ""
			)
		);
	$request = $fm->newFindCommand( 'user' );
	$request->addFindCriterion('macaddress', $macADD   );
	$request->addFindCriterion('beaconid'  , $beaconid );
	$result  = $request->execute();
	if (FileMaker::isError($result)) {
		if($result->code==401){
			$newRec = $fm->newAddCommand('user');
//			$newRec->setField('userid'   , $userID   );
			$newRec->setField('fname'      , $inRecord->getField('fname') );
			$newRec->setField('lname'      , $inRecord->getField('lname') );
			$newRec->setField('email'      , $inRecord->getField('email') );
			$newRec->setField('macaddress' , $macADD                      );
			$newRec->setField('beaconid'   , $beaconid                    );
			$newRec->setField('inout'      , 1                            );
			$result = $newRec->execute();
			if (FileMaker::isError($result)) {
				$retVal['retVal']["message"]= addslashes($result->getMessage()) ;
			}else{
				$retVal['userID']=$result->getLastRecord()->getRecordID();
			}
		}else{
			$retVal['retVal']["message"]= addslashes($result->getMessage()) ;
		}
	}else{
		$records = $result->getRecords();
		$retVal['userID']=$records[0]->getRecordId();
	}
	return $retVal;
}
//--------------------------------------------------------------------------------------------
function make_beaconid( $fm, $macADD, $uuid, $major, $minor ){
	if( $uuid!="" && $major!="" && $minor!="" ){
		return array( 'beaconid'=> "{$uuid}|{$major}|{$minor}" , 'retVal' => array() );
	}else{
		$request = $fm->newFindCommand( 'user' );
		$request->addFindCriterion('macaddress', $macADD );
		$result  = $request->execute();
		if( FileMaker::isError($result) ){
			return 
				array(
					'beaconid'=> "",
					"retVal"=>array(
						"result"   => 0,
						"message"  => addslashes($result->getMessage())
					)
				);
		}else{
			$records = $result->getRecords();
			$salesid = $records[0]->getField('salesid');
			$request = $fm->newFindCommand( 'settings' );
			$request->addFindCriterion('salesid', $salesid );
			$result  = $request->execute();
			if (FileMaker::isError($result)) {
				return 
					array(
						'beaconid'=> "",
						"retVal"=>array(
							"result"   => 0,
							"message"  => addslashes($result->getMessage())
						)
					);
			}else{
				$records = $result->getRecords();
				if (FileMaker::isError($records)) {
					return 
						array(
							'beaconid'=> "",
							"retVal"=>array(
								"result"   => 0,
								"message"  => addslashes($records->getMessage())
							)
						);
				}else{
					return 
						array( 
							'beaconid'=> $records[0]->getField('beaconuuid')."|".$records[0]->getField('major')."|".$records[0]->getField('minor'),
							'retVal'=>array() 
						);
				}
			}

		}
	}
}
?>
