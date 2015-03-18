<?php

$mac= $_POST['macaddress'];
//var_dump($mac);
include 'FileMaker.php';

$fm = new FileMaker('KTTC_Database.fmp12', '199.38.217.147', 'jay', 'jklmjklm');

$request = $fm->newFindCommand('inout');
$request->addFindCriterion('macaddress', $mac);
//var_dump($findCommand);
$result = $request->execute();
$records = $result->getRecords();
foreach ($records as $record){
       echo($record->getField('beaconUUID'));
       echo("|");
       echo($record->getField('beaconMajor'));
       echo("|");
       echo($record->getField('beaconMinor'));
}
?>