<?php 

MyMinclude("core/baseMySQL.php");

// Connection and selection of the database
$connection = connect("createmysql > creation of mysql tables"); 

$structures = listfiles(MYM_STRUCTURES_REALPATH);

for ($j = 0; $j < count($structures); $j++) {
  $structure = $structures[$j];  
  MyMincludestructure($structure);
  $elem = new $structure(); // create object
  if (!$elem->staticarray()) {
    print("Generating <strong>$structure</strong> table... ");
    $rules = $elem->MyMrules(); // take rules of the object
    $query = $elem->MySQLcreateQuery($rules);
    $result = mysql_query($query);
    if (mysql_errno() > 0) print($structure." > ERROR: Creation Query failed. <br/>". mysql_errno() . ": " . mysql_error(). "\n" . "<br />");
    else print("Table <strong>$structure</strong> created.<br />");    
  } else {
    print("<strong>$structure</strong> will be a static array recorded in a file, not a table.<br />");
  }
}