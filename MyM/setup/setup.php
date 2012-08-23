<?php 
/*
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
   This file runs a guided setup for MyM.
*/

define('MYM_PATH', dirname(__FILE__));
require MYM_PATH."/config.php";
require MYM_PATH."/core/base.php";
require MYM_PATH."/core/pre.php";

function printtest($bool) {
  if (!$bool) print(" <em>failed</em>");
  else print(" <em>OK</em>");
  return $bool;
}

// Read step
$step = getpost("step", 1);

// Read action
$a = getpost("a", "");

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
 <head>
  <meta http-equiv="content-type" content="text/html; charset=utf-8" />
  <meta name="robots" content="noindex, nofollow" />
  <title>MyM Setup <?php print("&#8250; Step ".$step); ?> </title>
  <link rel="stylesheet" href="./admin.css" type="text/css" />
 </head>
<body>
<?php

print("<div id='content'>\n");

switch ($step) { 
  case 1: 
    print("<h2>Current configuration (in <em>config.php</em>)</h2>");
  
    print("<p><span class='left'>Home Uri</span>".HOME_URI ."</p>\n");
    print("<p><span class='left'>Admninistrator Email</span>".MYM_ADMIN_EMAIL."</p>\n");
    print("<hr/>");
    print("<p><span class='left'>Structures defined in <em>./structures/</em></span>\n");
    print("<p class='right'>\n");

    $structures = listfiles(MYM_PATH."/structures/");
    for ($i = 0; $i < count($structures); $i++) {
      print($structures[$i]."</li>\n");
    }           
    
    print("</p>\n");   
    print("<hr/>");      
    print("<p><span class='left'>Users Table</span>".MYM_USER_DB."</p>\n");
    print("<p><span class='left'>Not logged user privilege</span>".MYM_NOT_LOGGED_USER_PRIV."</p>\n");
    print("<p><span class='left'>New user privilege</span>".MYM_NEW_USER_PRIV."</p>\n");
    print("<p><span class='left'>Admin privilege</span>".MYM_ADMIN_PRIV."</p>\n");
    print("<hr/>");
    print("<p><span class='left'>Default Language</span>".MYM_DEFAULT_LNG."</p>\n"); 
    print("<p><span class='left'>Other languages (in <em>pre.php</em>)</span>");
    for($i=0; $i<count($set_lngcode); $i++) {
      if ($i>0) print ", ";
      print($set_lngcode[$i]);
    }  
    print("</p>\n");
    print("<hr/>");
    print("<p><span class='left'>Session expiration time</span>".MYM_SESSION_EXPIRE_TIME."</p>\n");  
    print("<hr/>");
    print("<p><span class='left'>Default Upload Path</span>".MYM_UPLOAD_PATH."</p>\n");
    print("<hr/>");
    print("<p><span class='left'>Use of MySQL database</span>".MYSQL."</p>\n");
    print("<p><span class='left'>MySQL server</span>".MYSQL_SERVER."</p>\n");
    // print("<p><span class='left'>MySQL username</span>".MYSQL_USER."</p>\n");
    // print("<p><span class='left'>MySQL password</span>".MYSQL_PASSWORD."</p>\n");
    print("<p><span class='left'>MySQL database</span>".MYSQL_DB."</p>\n");
    print("<hr/>");
    print("<p><span class='left'>Level of debug trace</span>".MYM_DEBUG_TRACE."</p>\n");;
    print("<hr/>");    
    print("<p><span class='left'><strong>Testing database</strong></span>");
    require_once(MYM_PATH."/core/baseMySQL.php");
    $test = printtest(testconnect());
    if ($test)
      print(" Test successful: <a href='setup.php?step=2'>Next step &raquo;&raquo;</a>");
    else 
      print(" Test not successful, sorry, check up your database configuration.");
    print("</p>");
    break;
  
  case 2: 
    print("<h2>Creation of MyM tables</h2>");
    include "./core/MySQL.php";
    print("<p>Step successful: <a href='setup.php?step=3'>Next step</a>.</p>");
    break;
    
  case 3:
    print("<h2>Administrator data</h2>");
    $user = MYM_USER_DB;
    require(MYM_PATH."/structures/$user.php"); 
    $admin = new $user();  
    require_once(MYM_PATH."/core/MyMtype.php");  
    $rules = $admin->MyMrules();    

    $admin->MyMprecheck($rules);
    $admin->MyMwrite($rules, "setup.php?step=4");     
    break;

  case 4:
    print("<h2>Recording Administrator data</h2>");
    if ($a != "write2") 
      print ("<p>Sorry.An error has occurred.</p>");
    else {
      $user = MYM_USER_DB;
      require(MYM_PATH."/structures/$user.php"); 
      $admin = new $user();  
      require_once(MYM_PATH."/core/MyMtype.php");  
      $rules = $admin->MyMrules();    
    
      $admin->MyMsetbypost();  
      
      if (!$admin->MyMpostcheck()) {
        print "<p>Sorry, your form is not valid, please check:</p>\n";
        $admin->MyMprecheck($rules);      
        $admin->MyMwrite($rules);     
      } else {
        if (!$admin->MyMrecord($rules)) {
          print "<p>Sorry, this record could not be saved correctly.</p>\n";
          $admin->MyMprecheck($rules);      
          $admin->MyMwrite($rules);     
        }
        else {
          print("<p>The administrator profile has been recorded.</p>");
          print("<p>Changing privilege to superuser...</p>");          
          $update = "UPDATE ".$admin->db."s SET ".$admin->privilegefield()." = ".MYM_ADMIN_PRIV." WHERE id = '".$admin->id."';"; 
          if (updateQuery($admin->db." > MyMrecord", $update)) {
            print("<p>The administrator has been defined!!</p>\n");
            print("<p>Now <strong>MyM</strong> is ready to be used!!!</p>\n");
          }     
          else 
            print("<p>Sorry, I wasn't able to change privilege. Please do it manually.</p>\n");
        }
      }
    }
    break; 
    
  default: 
    print("<p>Sorry, step not recognised.</p>");
} 

print("</div>\n");
?> 

</body>
</html>

