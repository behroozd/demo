<?php
header('Content-type: application/json');
require_once 'filemaker/FileMaker.php';
require_once 'php_functions.php';
$iniParameters = parse_ini_file("config.ini",true);
$showJASON=true;
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
	if( !isset($inData['activity']) ){
		$retVal=array(
			"result"   => 0,
			"activity" => 'null',
			"message"  => "invalid activity"
		);
	}else{
		if( !isset( $inData['listname'] ) || trim( $inData['listname'] )=='' ){
			$retVal=array(
				"result"   => 0,
				"activity" => $inData['activity'],
				"message"  => "invalid listname"
			);
		}else{
			switch( $inData['activity'] ){
				case "list":{
					$retVal=makeList($fm, $inData['activity'], $inData['listname'], 0);
					break;
				}
				case "dellhtml":{
					if( !isset( $inData['id'] ) || trim( $inData['id'] )=='' ){
						$retVal=array(
							"result"   => 0,
							"activity" => $inData['activity'],
							"message"  => "invalid id"
						);
					}else{
						$retVal=deleteList($fm, $inData['activity'], $inData['listname'], $inData['id'] );
					}
					$showJASON=false;
					if( $retVal['result']==0){
						echo $retVal['message'];
					}else{
						echo 'ok';
					}
					break;
				}
				case "listhtml":{
					$retVal=makeList($fm, $inData['activity'], $inData['listname'], 1);
					$showJASON=false;
					header('Content-type: text/html');
					if( $retVal['result']==0 ){
						?>
						<div>
							<h3 style='color:#f00'>Error : </h3>
							<p>activity: <b><i><?php echo $retVal['activity']; ?></i></b></p>
							<p>listname: <b><i><?php echo $retVal['listname']; ?></i></b></p>
							<p>message : <b><?php echo $retVal['message']; ?></b></p>
						</div>
						<?php
					}else{
						$keys=array_keys( $retVal );
						?><div><?php
						foreach($retVal[$inData['listname']] as $item){
							?>
							<div class="m-row" id="m_row_<?php echo $item['id']; ?>">
								<div class="delete-button">
									<a onclick="callDelete(<?php echo $item['id']; ?>)" style="cursor:pointer" id="myDel_<?php echo $item['id']; ?>">x</a>
								</div>
								<div class="m-title">
									<b style="padding-left:10px; width:80px;"><?php echo $item['item']; ?></b><br />
								</div>
								<i style="padding-left:30px;display:none" id="myRes_<?php echo $item['id']; ?>"></i>
							</div>
							<?php
						}
						?>
						</div>
						<script type="text/javascript">
							function callDelete(id){
								var xmlhttp;
								document.getElementById("myRes_"+id).innerHTML="wait . . .";
								document.getElementById("myRes_"+id).style.display="";
								if( window.XMLHttpRequest ){// code for IE7+, Firefox, Chrome, Opera, Safari
									xmlhttp=new XMLHttpRequest();
								}else{// code for IE6, IE5
									xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
								}
								xmlhttp.onreadystatechange=function(){
									if( xmlhttp.readyState==4 && xmlhttp.status==200 ){
										//alert("m_row_"+id);
										document.getElementById("myRes_"+id).innerHTML=xmlhttp.responseText;
										if(xmlhttp.responseText=='ok'){
										//	document.getElementById("myDel_"+id).style.display='none';
											document.getElementById("m_row_"+id).style.display='none';
										//	document.getElementById("myRes_"+id).innerHTML='Deleted';
										}
									}
								}
								xmlhttp.open("POST","list.php",true);
								xmlhttp.setRequestHeader("Content-type","application/x-www-form-urlencoded");
								xmlhttp.send("activity=dellhtml&listname=<?php echo $inData['listname']; ?>&id="+id);
							}
						</script>

						<style>
						
						.m-row {
							height:72px; max-width:420px;font-family: arial; font-size: 24px; padding-top: 15px; border-bottom: thin solid #888888;
						}
						
						.m-title {
							 margin-top: 24px;
						}
						
						.delete-button{
							float: right;font-family: arial;font-size: 30px;background-color: #CDCDCD; padding: 10px 20px 10px 20px;  border: thin solid #888888; 
						}
						
						</style>

						<?php
					}
					break;
				}
				case "add":{
					if( !isset( $inData['newitem'] ) || trim( $inData['newitem'] )=='' ){
						$retVal=array(
							"result"   => 0,
							"activity" => $inData['activity'],
							"message"  => "invalid newitem"
						);
					}else{
						$retVal=addList($fm, $inData['activity'], $inData['listname'], $inData['newitem']);
					}
					break;
				}
				default:{
					$retVal=array(
						"result"   => 0,
						"activity" => $inData['activity'],
						"message"  => "invalid activity"
					);
					break;
				}
			}
		}
	}
	if( $showJASON ){ echo json_encode($retVal,JSON_PRETTY_PRINT); }
}
?>