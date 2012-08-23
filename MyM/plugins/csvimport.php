<?php 
  
  MyMinclude('core/upload.php');
  MyMinclude('core/txtDB.php');  
  
  $action = getpost('a', 'write'); // get action    
  $filename = getpost('csvtable');
  $csv_path = MYM_CACHE_REALPATH."/csv/";  
  
  set_time_limit(300);
  
  $structures = listfiles(MYM_STRUCTURES_REALPATH);
  $structures = array_diff($structures, array('article', 'user'));  
     
  switch ($action) {
  
    case 'write': ?>
<div id='table2col'> 
<form enctype="multipart/form-data" action="index.php" method="POST">
<input type="hidden" name="plugin" value="csvimport" />
<input type="hidden" name="a" value="upload2" />
<input type="hidden" name="MAX_FILE_SIZE" value="200000000" />
<table> 
<tr> <td class='left'>File to upload:</td> <td class='right'>  <input name="uploadedfile" type="file" /></td></tr>
<tr><td class='left'> &nbsp; </td> <td class='rightbutton'> <input type="submit" value="Upload File" /></td></tr>
</table>
</form>
</div>
<?php break;
      
    case 'upload2': 
    
      if (!is_dir($csv_path))
        if (mkdir($csv_path))
          print ("Directory $csv_path created.<br/>");
        else
          die("Sorry, directory $csv_path can't be created.<br/>");
      
      if (upload($_FILES['uploadedfile'], $csv_path, false, 0, true, ".csv")) {
        $filename = uploadedfilename($_FILES['uploadedfile']);      
        $file = uploadedfile($_FILES['uploadedfile'], $csv_path);
      } else die();
      
      $params['name'] = $filename;
      $params['path'] = $csv_path;
      $params['containsfields'] = true;
      $params['fields'] = array();
      $params['separator'] = ";".
      $params['tableext'] = "";
      
      $csvtable = new Txttable($params);
      $csvtable->openTable();      
      
      ?>

<script type="text/javascript">
function checkAllCB(name, flag)
{   
   form = document.forms[0];
   for (var x = 0; x < form.elements.length; x++) {
     if (form.elements[x].type=="checkbox")
       form.elements[x].checked = (flag == 1);            
   }
}
</script>

<div id='table2col'> 
<form enctype="multipart/form-data" action="index.php" method="POST">
<input type="hidden" name="plugin" value="csvimport" />
<input type="hidden" name="csvtable" value="<?php print($filename); ?>" />
<table> 
<tr> <td class='left'>Action:</td> 
<td class='right'>  <?php print(InputRadioOption('a', UNDEFINED, array('Add', 'Remove'), array('add', 'remove'))); ?></td></tr>
<tr> <td class='left'>Target databases:</td> 
<td class='right' id='structures'>  <?php print(InputCheckboxOption('o', UNDEFINED, array_values($structures), array_values($structures))); ?>
<div class='checkboxinput'><input type="checkbox" name="checkAll" id="checkAll" onclick="javascript:checkAllCB('o', this.checked);"/><span class='checkboxoption'>check/uncheck all</span></div></td></tr>
<tr><td class='left'> &nbsp; </td> <td class='rightbutton'> <input type="submit" value="Upload File" /></td></tr>
</table>
</form>
</div>
<?php 
      
      /* for ($i = 0; $i < count ($structures); $i++) {
        if ($i > 0) print (" | ");
        print("<a href='index.php?plugin=csvimport&o=".$structures[$i]."&a=write'>".$structures[$i]."</a>");
      }      
      break;
      
      print("<br/><a href='index.php?plugin=csvimport&csvtable=$filename&a=add'>Add</a> | <a href='index.php?plugin=csvimport&csvtable=$filename&a=remove'>Remove</a><br/>"); */
      break;

    case 'target':
      $structures = listfiles(MYM_STRUCTURES_REALPATH);
      
      print("Please choise the target db: ");
      for ($i = 0; $i < count ($structures); $i++) {
        if ($i > 0) print (" | ");
        print("<a href='index.php?plugin=csvimport&o=".$structures[$i]."&a=write'>".$structures[$i]."</a>");
      }      
      break;
      
    case 'add':
      $structures = postcheckbox('o', $structures);
      
      foreach ($structures as $structure) {
        MyMincludestructure($structure); 
        $file = $csv_path."/".$filename;
        if (!is_file($file))
          die("File $file not found.<br/>");
        
        $params['name'] = $filename;
        $params['path'] = $csv_path;
        $params['containsfields'] = true;
        $params['fields'] = array();
        $params['separator'] = ";".
        $params['tableext'] = "";
        
        $csvtable = new Txttable($params);
        $csvtable->openTable();      
        
        $ok = $ko = 0;
        for ($i = 1; $i <= $csvtable->ndata; $i++) {
          $elem = $csvtable->readElement($i);
          $object = new $structure();
          $object->MyMset($elem);    
          if ($object->MyMrecord()) $ok++; else $ko++;
          unset($object);
        }
        print ("db <strong>$structure</strong>: $ok elements added (on ".($ok+$ko).").<br/>");
      }
        
      unlink($file);
      
      print("<br/><a href='index.php?plugin=csvimport'>Back</a>");
      
      break;

    case 'remove':
      $structures = postcheckbox('o', $structures);
      
      $totalok = $totalko = 0;
      foreach ($structures as $structure) {
        MyMincludestructure($structure); 
        $file = $csv_path."/".$filename;
        if (!is_file($file))
          die("File $file not found.<br/>");
        
        $params['name'] = $filename;
        $params['path'] = $csv_path;
        $params['containsfields'] = true;
        $params['fields'] = array();
        $params['separator'] = ";".
        $params['tableext'] = "";
        
        $csvtable = new Txttable($params);
        $csvtable->openTable();      
        
        $ok = $ko = 0;      
        $object = new $structure();
        for ($i = 1; $i <= $csvtable->ndata; $i++) {
          $elem = $csvtable->readElement($i);
          global $mysql;
          if (!$mysql) list($listid, $n, $ntot) = $object->MyMlist("\$email == '".$elem['email']."'");
          else list($listid, $n, $ntot) = $object->MyMlist("email = '".$elem['email']."'");
          if ($n > 0) {
            if ($n == 1) {
              if ($object->MyMdelete($listid[0])) $ok++; else $ko++;              
            }
            else {
              die ("Something is wrong. More than one element with the same email.<br/>");
            }
          }
        }
      
        print ("db <strong>$structure</strong>: $ok elements successfully removed (on ".($ok+$ko).").<br/>");
        $totalok += $ok;
        $totalko += $ko;
      }
      
      print ("Total: $totalok elements successfully removed (".($totalok+$totalko)." found on ".$csvtable->ndata.").<br/>");
      unlink($file);
      
      print("<br/><a href='index.php?plugin=csvimport'>Back</a>");      
      break;
  
  }
     


?>