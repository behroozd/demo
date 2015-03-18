<?php
header('Content-type: application/json');
require_once 'filemaker/FileMaker.php';
$iniParameters = parse_ini_file("config.ini",true);
if( $iniParameters==false ){
	$retVal=array(
		"result"   => 0,
		"message"  => "config file not found"
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
	$macADD = trim($inData['macaddress']);
	if($userID=='' && $macADD=='' ){
		$retVal=array(
			"result"   => 0,
			"message"  => "invalid macaddress"
		);
	}else{
		$macADD  = trim($inData['macaddress']);
		$layouts = $fm->listLayouts();
		if( FileMaker::isError($layouts) ){
			$retVal=array(
				"result"   => 0,
				"message"  => addslashes($layouts->getMessage())
			);
		}else{
/*
			$layout_name = 'user';
			$request = $fm->newCompoundFindCommand($layout_name);
			$find    = $fm->newFindRequest($layout_name);
			$find   ->addFindCriterion('macaddress', $macADD);
			$request->add(1,$find );
*/
			$request = $fm->newFindCommand( 'user' );
			$request->addFindCriterion('macaddress', $macADD );
			$result  = $request->execute();
			if (FileMaker::isError($result)) {
				if($result->code==401){
					$retVal=array(
						"result"   => 0,
						"message"  => "Authentication failed"
					);
				}else{
					$retVal=array(
						"result"   => 0,
						"message"  => addslashes($result->getMessage())
					);
				}
			}else{
				$records = $result->getRecords();
				$salesid = $records[0]->getField('salesid');
				/*
				$layout_name = 'settings';
				$request = $fm->newFindAllCommand($layout_name);
				$result  = $request->execute();
				*/
				$request = $fm->newFindCommand( 'settings' );
				$request->addFindCriterion('salesid', $salesid );
				$result  = $request->execute();
				if (FileMaker::isError($result)) {
					$retVal = array(
						"result"  => 0,
						"message" => addslashes($result->getMessage())
					);
				}else{
					$records = $result->getRecords();
					if (FileMaker::isError($records)) {
						$retVal=array(
							"result"   => 0,
							"message"  => addslashes($records->getMessage())
						);
					}else{
						$retVal=array(
							"result" => 1,
							"beaconname" => $records[0]->getField('beaconname'),
							"uuid"       => $records[0]->getField('beaconuuid'),
							"major"      => $records[0]->getField('major'     ),
							"minor"      => $records[0]->getField('minor'     )
						);
					}
				}
			}
		}
	}
}
echo json_encode($retVal,JSON_PRETTY_PRINT);
//--------------------------------------------------------------------------------------------
?>
