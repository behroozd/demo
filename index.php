<?php
header('Content-type: application/json');
require_once 'filemaker/FileMaker.php';
require_once 'php_functions.php';
$printJASON=true;
$iniParameters = parse_ini_file("config.ini",true);
if( $iniParameters==false ){
	$retVal=array(
		"result"  => 0,
		"message" => "config file not found"
	);
}else{
	//------------------------------------------------
	$keys = array_keys($_POST);
	$inData=array();
	foreach($keys as $key){
		$inData[strtolower($key)]=$_POST[$key];
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
					case 'alluser':{
						$retVal = activity_alluser($fm, $activity);
						break;
					}
					case 'referral':{
						$macADD = trim($inData['macaddress']);
						if( $macADD=='' ){
							$retVal=array(
								"activity" => $activity,
								"result"   => 0,
								"message"  => "invalid macaddress "
							);
						}else{
							$retVal = activity_referral($fm, $activity, $macADD);
						}
						$printJASON=false;
						header('Content-type: text/html');
						if($retVal['result']==1){ echo htmlspecialchars_decode( $retVal['html'] ); }
						if($retVal['result']==0){ echo 'Error: '.$retVal['message']; }
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
if( $printJASON ){ 
	echo json_encode($retVal,JSON_PRETTY_PRINT); 
}
//--------------------------------------------------------------------------------------------
?>