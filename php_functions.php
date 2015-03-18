<?php
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
function makeList($fm, $activity, $listname, $type){
	$retVal=array(
			"result"   => 0,
			"activity" => $activity,
			"listname" => $listname,
			"message"  => "unknown error"
		);
	$request = $fm->newFindCommand( 'lists' );
	if( FileMaker::isError($request) ){
		$retVal=array(
			"result"   => 0,
			"activity" => $activity,
			"listname" => $listname,
			"message"  => addslashes($request->getMessage())
		);
	}else{
		$request->addFindCriterion('listname' , $listname);
		if( FileMaker::isError($request) ){
			$retVal=array(
				"result"   => 0,
				"activity" => $activity,
				"listname" => $listname,
				"message"  => addslashes($request->getMessage())
			);
		}else{
			$result  = $request->execute();
			if( FileMaker::isError($result) ){
				$retVal=array(
					"result"   => 0,
					"activity" => $activity,
					"listname" => $listname,
					"message"  => addslashes($result->getMessage())
				);
			}else{
				$records = $result->getRecords();
				if($type=0){
					$retVal=array(
						"result"     => 1,
						"activity"   => $activity,
						"listname"   => $listname
					);
				}else{
					$retVal=array(
						"result"     => 1,
						"activity"   => $activity,
						"listname"   => $listname,
						$listname    => array()
					);
				}
				foreach($records as $record){
					if($type=0){
						array_push( $retVal , $record->getField('item' ) );
					}else{
						$item = array(
							"id"   => $record->getRecordId(),
							"item" => $record->getField('item' )
						);
						array_push( $retVal[$listname] , $item );
					}
				}
			}
		}
	}
	return $retVal;
}
//--------------------------------------------------------------------------------------------
function addList($fm, $activity, $listname, $newitem){
	$retVal=array(
			"result"   => 0,
			"activity" => $activity,
			"message"  => "unknown error"
		);
	$newRec = $fm->newAddCommand('lists');
	if( FileMaker::isError($newRec) ){
		$retVal=array(
			"result"   => 0,
			"activity" => $activity,
			"message"  => addslashes($newRec->getMessage())
		);
	}else{
		$newRec->setField('listname' , $listname );
		$newRec->setField('item'     , $newitem  );
		$result = $newRec->execute();
		if (FileMaker::isError($result)) {
			$retVal=array(
				"result"   => 0,
				"activity" => $activity,
				"message"  => addslashes($result->getMessage())
			);
		}else{
			$retVal=array(
				"result"     => 1,
				"activity"   => $activity,
				"listname"   => $listname,
//				$listname    => array( $newitem )
				$newitem
			);
		}
	}
	return $retVal;
}
//--------------------------------------------------------------------------------------------
function deleteList($fm, $activity, $listname, $id){
	$retVal=array(
			"result"   => 0,
			"activity" => $activity,
			"message"  => "unknown error"
		);
	$delRec = $fm->newDeleteCommand('lists', $id);
	if( FileMaker::isError($delRec) ){
		$retVal=array(
			"result"   => 0,
			"activity" => $activity,
			"message"  => addslashes($delRec->getMessage())
		);
	}else{
		$result = $delRec->execute();
		if (FileMaker::isError($result)) {
			$retVal=array(
				"result"   => 0,
				"activity" => $activity,
				"message"  => addslashes($result->getMessage())
			);
		}else{
			$retVal=array(
				"result"     => 1,
				"activity"   => $activity,
				"listname"   => $listname
			);
		}
	}
	return $retVal;
}
//--------------------------------------------------------------------------------------------
function activity_referral($fm, $activity, $macADD){
	$retVal=array(
		"result"   => 0,
		"activity" => $activity,
		"message"  => "unknown error"
	);

	$request = $fm->newFindCommand( 'user' );
	if( FileMaker::isError($request) ){
		$retVal=array(
			"result"   => 0,
			"activity" => $activity,
			"message"  => addslashes($result->getMessage())
		);
	}else{
		$request->addFindCriterion('macaddress', $macADD );
		if( FileMaker::isError($request) ){
			$retVal=array(
				"result"   => 0,
				"activity" => $activity,
				"message"  => addslashes($result->getMessage())
			);
		}else{
			$result  = $request->execute();
			$userRecord = $result->getRecords();
			$salesid = $userRecord[0]->getField('salesid');
			$request = $fm->newFindCommand( 'user' );
			$request->addFindCriterion('id', $salesid );
			if( FileMaker::isError($request) ){
				$retVal=array(
					"result"   => 0,
					"activity" => $activity,
					"message"  => addslashes($result->getMessage())
				);
			}else{
				$result     = $request->execute();
				$saleRecord = $result->getRecords();
				
				$request = $fm->newFindCommand( 'settings' );
				$request->addFindCriterion('salesid', $salesid );
				if( FileMaker::isError($request) ){
					$retVal=array(
						"result"   => 0,
						"activity" => $activity,
						"message"  => addslashes($result->getMessage())
					);
				}else{
					$result   = $request->execute();
					$record   = $result->getRecords();
					
					$referral = $record[0]->getField('referral');
					$userOBJ  = $userRecord[0]->getField('fname').' '.$userRecord[0]->getField('lname');
					$saleOBJ  = $saleRecord[0]->getField('fname').' '.$saleRecord[0]->getField('lname');
					$referral = str_replace("%%sales%%", $saleOBJ, $referral );
					$referral = str_replace("%%user%%" , $userOBJ, $referral );
					$retVal=array(
						"result"   => 1,
						"activity" => $activity,
						"html"  => $referral
					);
				}
			}
		}
	}
	return $retVal;
}
//--------------------------------------------------------------------------------------------
function activity_alluser($fm, $activity){
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
					"users"    => array()
				);
				foreach($records as $record){
					$item = array(
						"user_id"    => $record->getRecordId(),
						"fname"      => $record->getField('fname'     ),
						"lname"      => $record->getField('lname'     ),
						"email"      => $record->getField('email'     ),
						"macaddress" => $record->getField('macaddress'),
						"beaconid"   => $record->getField('beaconid'  ),
						"timestamp"  => $record->getField('timestamp' ),
						"salesid"    => $record->getField('salesid'   ),
						"inout"      => $record->getField('inout'     )
					);
					array_push( $retVal['users'] , $item );
				}
			}
		}
	}
	return $retVal;
}
//--------------------------------------------------------------------------------------------
function activity_poke($fm, $activity, $beaconid ){
	$request = $fm->newFindCommand( 'user' );
	$request->addFindCriterion('beaconid' , $beaconid);
	$request->addFindCriterion('inout'    , 1);
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
				"fname"      => $record->getField('fname'     ),
				"lname"      => $record->getField('lname'     ),
				"email"      => $record->getField('email'     ),
				"macaddress" => $record->getField('macaddress'),
				"beaconid"   => $record->getField('beaconid'  ),
				"timestamp"  => $record->getField('timestamp' ),
				"inout"      => $record->getField('inout'     )
			);
			array_push( $retVal['users'] , $user );
		}
	}
	return $retVal;
}
//--------------------------------------------------------------------------------------------
?>