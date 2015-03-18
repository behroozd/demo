<?php
include 'FileMaker.php';
$beaconUUID= $_POST['beaconUUID'];
$beaconMajor= $_POST['beaconMajor'];
$beaconMinor= $_POST['beaconMinor'];



//$fm = new FileMaker('KTTC_Database.fmp12', '199.38.217.147', 'jay', 'jklmjklm');


$fm = new FileMaker('demoNew.fmp12', '75.98.16.6', 'admin', '12345');

$request = $fm->newFindCommand('firsthit');
$request->addFindCriterion('beaconUUID', $beaconUUID);
$request->addFindCriterion('beaconMajor', $beaconMajor);
$request->addFindCriterion('beaconMinor', $beaconMinor);

$result = $request->execute();
$records = $result->getRecords();
echo("<html><head><title>Test In and Out</title></head><body><table>");
foreach ($records as $record){
	   echo("<tr><td>");
       echo($record->getField('presence'));
       echo("</td><td>");
       echo($record->getField('inout::name'));
       echo("</td><td>");
       echo($record->getField('timestamp'));
       echo("</td></tr>");
       
}
echo("</table></body></html>");
?>