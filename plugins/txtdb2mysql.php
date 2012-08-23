<?php 

  MyMinclude('core/txtDB.php'); 
  MyMinclude('core/baseMySQL.php'); 

  global $mysql;
  
  if ($mysql != "")
    die("This MyM must use a txtdb database. Check your config.");
    
  // Connection and selection of the database
  $connection = connect("txtdb2mysql > migration to mysql tables"); 

  $structures = listfiles(MYM_STRUCTURES_REALPATH);

  foreach ($structures as $o) {
    MyMincludestructure($o);
    $elem = new $o();  
 
    $result = $elem->MyMlist();
            
    print("######### Migrating <strong>");print($o);print("</strong> data...<br/>");

    // if there isn't any element
    if ($result != false && $result[2] == 0) {
      print("<p>Sorry, There are no ".$o."s recorded.</p>\n");            
    }   
    else {
      foreach ($result[0] as $id) {
        $elem->MyMread($id); 
            
        global $mysql; $mysql = "1"; // enable mysql        
        $elem->MyMrecord();
        $mysql = ""; // disable mysql again
      }
    }        
  } 
