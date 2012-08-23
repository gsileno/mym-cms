<?php
/*
   File: download.php | (c) Giovanni Sileno 2006, 2007
   Distributed as part of "MyM - avant CMS"
   -----------------------------------------------------------------
   This file is part of MyM.

   MyM is free software; you can redistribute it and/or modify
   it under the terms of the GNU Lesser General Public License as published by
   the Free Software Foundation; either version 3 of the License, or
   (at your option) any later version.

   MyM is distributed in the hope that it will be useful,
   but WITHOUT ANY WARRANTY; without even the implied warranty of
   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
   GNU Lesser General Public License for more details.
   
   You should have received a copy of the GNU Lesser General Public License
   along with this program.  If not, see <http://www.gnu.org/licenses/>.
   -----------------------------------------------------------------
   This file contains the download counter functions for MyM.
*/

function MyMdownload($filedb = UNDEFINED, $filename = UNDEFINED) {

  if ($filedb == UNDEFINED || $filename == UNDEFINED) {
    $filedb = MYM_FILE_DB;       
    $filename = get('file');   
    if ($filename == UNDEFINED)
      return false;
  }
 
  $file = MYM_UPLOAD_PATH.'/'.$filename;
  $error = false;
  if (file_exists($file)) {
  
    requireonce(MYM_PATH_STRUCTURES.'/'.$filedb.'.php');
    $fileobj = new $filedb();
    
    list($list, $n, $tot)  = $fileobj->MyMlist("file = $filename");
    if ($n == 0) 
      $error = true;
    else {
      $fileobj->counter++;
      $fileobj->MyMrecord();
      
      $size = filesize($file);
      header('Content-Type: application/octet-stream');
      header('Content-Disposition: attachment; filename='.$HTTP_GET_VARS['get']);
      header('Content-Length: '.$size);
      readfile($path);
    }
  } else $error = true;
    
  if ($error) {
    echo "<font face=$textfont size=2>";
    echo "<p><strong>Error</strong> The file [<b>$get$extension</b>] is not available for download.</p>>";
    echo "<p>Please contact the web administrator.</p>"; 
  }
  
}
?>
