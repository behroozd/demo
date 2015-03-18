<?php

require_once 'filemaker/FileMaker.php';

// local  define('FM_HOST', '192.168.128.136');
  ///      this url also works for local:      http://ap1s-mac-mini.local/

// colocated Mac
  define('FM_HOST', '75.98.16.6');
//local
  define('FM_FILE', 'showcase.fmp12');
  define('FM_USER', 'admin');
  define('FM_PASS', '12345');


	$fm = new FileMaker(FM_FILE,FM_HOST,FM_USER,FM_PASS);


//$databases = $fm->listDatabases();

  
 $layout_name = 'user';


	$layouts = $fm->listLayouts();    


	$request = $fm->newFindAllCommand($layout_name);
	$result = $request->execute();
        $record = $result->getRecords();
   //$recordid = 1;
 
// $record = $fm->getRecordById($layout_name, $recordid);


        echo "<pre>";
       print_r($record);
        echo "</pre>"; 
  

if (FileMaker::isError($record)) {
 die('<p>'.$record->getMessage().' (error '.$record->code.')</p>');
 }


  
  
 // If   it is  $record = $result->getRecords(); then this part is used
  
  
  
	//echo json_encode($records); exit();
    $arr = Array();
    foreach($record as $key){
            $fields = $key->getFields();
    }
    $fieldArray = $fields;
    $gnar = Array();
    foreach($fields as $row){
            $gnar[] = [$row];	
    }
    $fresh = Array();
    foreach($gnar as $key){
	$fieldName = $key[0];
	$into = [];
	foreach($record as $key){
        $field = $key->getField($fieldName);
        $flag = 'false';
        foreach($into as $val){
        	if($field == $val){
        		$flag = 'true';
        	}
        }
        if($flag == 'false'){
        	array_push($into,$field);	
        }
        
	}
	array_push($fresh,[$fieldName => $into]);

    }


    print_r($fresh);




/*


 // If   it is   $record = $fm->getRecordById($layout_name, $recordid); then this part is used

 $layout_object = $record->getLayout();
 
 $field_objects = $layout_object->getFields();
 
 $page_content .= '<table border="1">';
 foreach($field_objects as $field_object) {
        $field_name = $field_object->getName();
        $field_value = $record->getField($field_name);
        $field_value = htmlspecialchars($field_value, ENT_QUOTES);
        $field_value = nl2br($field_value);
        $page_content .= '<tr><th>'.$field_name.'</th><td>'.$field_value.'</td></tr>';
 }
 $page_content .= '</table>'."\n";

# check the layout for portals


 $portal_objects = $layout_object->getRelatedSets();

foreach($portal_objects as $portal_object) {
    $page_content .= '<table border="1">';

    $page_content .= '<tr>';
   $field_names = $portal_object->listFields();
     foreach($field_names as $field_name) {
 
        $page_content .= '<th>'.str_replace('::', ' ', $field_name).'</th>';
     }
  $page_content .= '</tr>';
 
  # get the name of the current portal object
 $portal_name = $portal_object->getName();

 # get the records related to this record, based on the portal name
$related_records = $record->getRelatedSet($portal_name);
if (FileMaker::isError($related_records)) {
 $page_content .= '<td colspan="'.count($field_names).'">no related records</td>';
 } else {
 foreach($related_records as $related_record) {
 foreach($field_names as $field_name) {

 $field_val = $related_record->getField($field_name);
 $field_val = htmlspecialchars($field_val, ENT_QUOTES);
 $field_val = nl2br($field_val);
 $page_content .= '<td>'.$field_val.'</td>';
 }
 $page_content .= '</tr>';
 }
 }
 $page_content .= '</table>'."\n";

}
 echo $page_content;

  
  
  
  
  */
  
  
  
  /// Output JSON sample
  

?>

HTTP/1.1 200 OK
Content-Type: application/json

{
  "users":[
    {
      "user_id":1,
      "username":"Chris",
      "email":"chris@ap1.io",
      "password":"pass99"
    },
    {
      "user_id":5,
      "username":"Joe",
      "email":"joe@ap1.io",
      "password":"pass99"
    },
      {
      "user_id":11,
      "username":"Rich",
      "email":"rich@ap1.io",
      "password":"pass99"
    }
    
  ]
}