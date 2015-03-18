<style>
	label{ display:block; }
	select, input{ width:200px;margin-bottom:15px; }
</style>
<?php
require_once 'filemaker/FileMaker.php';
$iniParameters = parse_ini_file("config.ini",true);
//------------------------------------------------
if( $iniParameters==false ){
	echo "config file not found";
	exit();
}
//------------------------------------------------
$fm = new FileMaker(
			$iniParameters['database']['FM_FILE'],
			$iniParameters['database']['FM_HOST'],
			$iniParameters['database']['FM_USER'],
			$iniParameters['database']['FM_PASS']);
			
//------------------------------------------------
if( isset($_REQUEST['submit']) ){
	$fname      = trim( $_REQUEST['fname']      );
	$lname      = trim( $_REQUEST['lname']      );
	$email      = trim( $_REQUEST['email']      );
	$macaddress = trim( $_REQUEST['macAddress'] );
	$userType   = 'Customer';
	$inout      = 0;
	$salesid    = $_REQUEST['salesperson'];
	if($salesid!=0 && $fname!='' && $lname!='' && $macaddress!='' ){
		$request = $fm->newFindCommand( 'settings' );
		$request->addFindCriterion('salesid', $salesid );
		$result  = $request->execute();
		if (FileMaker::isError($result)) {
			$message =  $result->getMessage();
		}else{
			$records = $result->getRecords();
			if( FileMaker::isError($records) ){
				$message =  $records->getMessage();
			}else{
				$beaconid = $records[0]->getField('beaconuuid')."|".$records[0]->getField('major')."|".$records[0]->getField('minor');
				$newRec = $fm->newAddCommand('user');
				$newRec->setField('fname'      , $fname      );
				$newRec->setField('lname'      , $lname      );
				$newRec->setField('email'      , $email      );
				$newRec->setField('macaddress' , $macaddress );
				$newRec->setField('beaconid'   , $beaconid   );
				$newRec->setField('inout'      , $inout      );
				$newRec->setField('userType'   , $userType   );
				$newRec->setField('salesid'    , $salesid   );
				
				$result = $newRec->execute();
				if (FileMaker::isError($result)) {
					$message = $result->getMessage();
				}else{
					$message = 'New customer added';
				}
			}
		}
		unset($_REQUEST);
		?>
		<br />
		<label><?php echo $message; ?></label>
		<input type="button" onclick="window.location='users.php';" value="Back" />
		<?php
		exit();
	}
}
//------------------------------------------------
$request = $fm->newFindCommand( 'user' );
$request->addFindCriterion('userType', 'Sales' );
$result  = $request->execute();
if( FileMaker::isError($result) ){
	echo $result->getMessage();
	exit();
}
//------------------------------------------------
$records = $result->getRecords();
?>
<form method="post" enctype="application/x-www-form-urlencoded">
	<label for="salesperson">Salesperson</label>
	<select name="salesperson">
	<option value="0">Select ...</option>
	<?php
		foreach($records as $record){
			?><option value="<?php echo $record->getRecordId(); ?>"><?php echo $record->getField('fname').' '.$record->getField('lname'); ?></option><?php
		}
	?>
	</select>
	<label for="fname">First Name:</label>
	<input type="text" name="fname" value=""/>
	<label for="lname">Last Name:</label>
	<input type="text" name="lname" value=""/>
	<label for="macAddress">Mac Address:</label>
	<input type="text" name="macAddress" value=""/>
	<label for="email">email:</label>
	<input type="text" name="email" value=""/>
	<br />
	<input type="submit" value="Add Customer" name="submit"/>
</form>