<?php

MyMinclude("/core/MyMbuild.php");
$structures = listfiles(MYM_STRUCTURES_REALPATH);
$modulespath = MYM_MODULES_REALPATH;

if (!is_dir($modulespath)) {
  print(" > Sorry. $modulespath not existing...<br/>\n");
  die();
}

for ($j = 0; $j < count($structures); $j++) {
  $structure = $structures[$j];
  MyMincludestructure($structure);
  print("Generating <strong>$structure</strong> module... ");
  $elem = new $structure(); // create object
  $rules = $elem->MyMrules();
  $filename = $modulespath)."/".$structure.".php";
  
  print("Creating file <strong>".$structure.".php</strong>...");
  if (is_file($filename) {
    print($structure." > Sorry, the module file already exists, it will not be overwritten.<br/>\n");
  } else if (($file = fopen($filename, "w")) === NULL) {
    fclose($file);
    print($structure." > ERROR occurred in opening the module file...<br/>\n");
  } else {
    $buildstring = "";
    $nested = 0;
    print("<br/>\n");
  
    elem->buildHead();
    elem->buildConstants($rules);
    elem->buildClassHead();
    elem->buildClassProperties($rules);
    elem->buildMyMcheckpriv();
    elem->buildMyMprocessprint($rules);
    elem->buildMyMprint($rules);
    elem->buildMyMwrite($rules);
    elem->buildClassEnd();
    elem->buildEnd();
  
    if (fwrite($file, $buildstring) === FALSE) {
      print($structure." > ERROR: cannot write to file $filename!<br/>\n");

    }
  
    fclose($file);
  }
}
  