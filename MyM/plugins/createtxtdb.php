<?php

MyMinclude('core/txtDB.php'); 
$structures = listfiles(MYM_STRUCTURES_REALPATH);

for ($j = 0; $j < count($structures); $j++) {
  $structure = $structures[$j];
  MyMincludestructure($structure);
  print("Generating <strong>$structure</strong> table... ");
  $elem = new $structure(); // create object

  if (!$elem->staticarray()) {
    $rules = $elem->MyMrules();

    $keys = array_keys($rules);
  
    $table = new Txttable($structure, MYM_TXTDB_REALPATH, true, $keys);
    if (!$table->createTable(false))
      print($structure." > ERROR: table creation failed. <br/>");
    else print("Table <strong>$structure</strong> created. <br/>");
  }
  else
    print("<strong>$structure</strong> will be a static array recorded in a file, not a table.<br />");
}
