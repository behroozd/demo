<?php
include 'FileMaker.php';
$mac= $_POST['macaddress'];
$presence= $_POST['presence'];
echo($mac);
echo("<br/>");
echo($presence);

//$fm = new FileMaker('KTTC_Database.fmp12', '199.38.217.147', 'jay', 'jklmjklm');


$fm = new FileMaker('demoNew.fmp12', '75.98.16.6', 'admin', '12345');


$cmd = $fm->newAddCommand('firsthit');
$cmd->setField('macaddress', $mac);
$cmd->setField('presence', $presence);
$result = $cmd->execute();

echo($result);
?>