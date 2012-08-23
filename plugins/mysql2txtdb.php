<?php 


  MyMinclude('core/txtDB.php'); 
  MyMinclude('core/baseMySQL.php');     
  MyMinclude('core/baseTxtDB.php');   
  
  global $mysql;
  
  if ($mysql != "1")
    die("This MyM must use a MySQL database. Check your config.");
  
  // Connection and selection of the database
  $connection = connect("mysql2txt2 > migration to txtdb tables... fast!"); 

  set_time_limit(500);
  
  $structures = listfiles(MYM_STRUCTURES_REALPATH);

  foreach ($structures as $o) {

    MyMincludestructure($o);
    $elem = new $o();  

    $dbtable = OpenDB($elem->db);
    $dbtable->createTable(true);
      
    print("######### Migrating <strong>");print($o);print("</strong> data...<br/>");
 
    $result = $elem->MyMlist();
 
    $date_keys = array('date', 'data');
 
    // if there isn't any element
    if ($result != false) {
      if ($result[2] == 0) {
        print("<p>Sorry, There are no ".$o."s recorded.</p>\n");            
      }   
      else {       
        
        foreach ($result[0] as $id) {
          $elem->MyMread($id);            

          $array = $elem->MyMget(true);
          
          foreach ($date_keys as $key) {
            if (array_key_exists($key, $array)) {
              list($d, $mon, $y, $h, $min) = sscanf($array[$key], "%d/%d/%d, %d:%d");
              $array[$key] = mktime($h, $min, 0, $mon, $d, $y);
            }
          }
          
          $dbtable->addElement($array);
        } 
      }
    }           
  } 
  


            
?>