<?php
header('Content-type: application/json');
require_once 'filemaker/FileMaker.php';
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
	$activity  = ($inData['activity']=="")? 'inout' : trim(strtolower($inData['activity']));
	$activity  = trim(strtolower($inData['activity']));
	$macADD    = $inData['macaddress'];
	$beaconid  = $inData['uuid'];
	//------------------------------------------------
	if( $inData['macaddress']=='' || $inData['uuid']=='' ){
		$retVal=array(
			"result"   => 0,
			"activity" => $activity,
			"message"  => "invalid macaddress or uuid"
		);
	}else{
		$request = $fm->newFindCommand( 'user' );
		$request->addFindCriterion('macaddress', $macADD   );
		$request->addFindCriterion('beaconid'  , $beaconid );
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
			$userID  = $records[0]->getRecordId();
			$retVal="";
			switch( strtolower( $inData['presence '] ) ){
				case 'in':{
					$retVal = setInOut( $fm, $userID, 1, $beaconid );
					break;
				}
				case 'out':{
					$retVal = setInOut( $fm, $userID, 0, $beaconid );
					break;
				}
			}
			if($retVal==""){
				switch($activity){
					case 'inout':{
						$retVal = activity_inout($fm, $activity, $userID );
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
echo json_encode($retVal,JSON_PRETTY_PRINT);
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
function activity_inout($fm, $activity, $userID ){
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
					"username"   => $record->getField('userName'  ),
					"fname"      => $record->getField('fname'     ),
					"lname"      => $record->getField('lname'     ),
					"email"      => $record->getField('email'     ),
					"password"   => $record->getField('password'  ),
//					"macaddress" => $record->getField('macaddress'),
					"timestamp"  => $record->getField('timestamp' ),
					"inout"      => $record->getField('inout'     ),
					"image"      => $record->getField('image'     )
				);
				array_push( $retVal['users'] , $user );
			}
		}
	}
	return $retVal;
}
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
?>