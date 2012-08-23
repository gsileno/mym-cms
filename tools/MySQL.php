<?php 
/*
   File: MySQL.php | (c) Giovanni Sileno 2006, 2007
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
   When executed, this file creates the tables in the MySQL db.
*/


if (!defined('MYM_PATH'))
  define('MYM_PATH', realpath(dirname(__FILE__).'/../.'));

if (!defined('APP_RELATIVE_PATH'))
    show_error('Please define the relative path to MyM from your script directory.');

/* require_once(MYM_PATH."/core/baseMyM.php");

MyMsetuppath(APP_RELATIVE_PATH); */

// if (!MYSQL) die("Sorry, your setup does not require MySQL, see <strong>config.php</strong>.");

// Connection and selection of the database
$connection = connect("MySQL.php > creation of tables"); 

$structures = listfiles(MYM_PATH_STRUCTURES);

for ($j = 0; $j < count($structures); $j++) {
  $structure = $structures[$j];  
  require_once(MYM_PATH_STRUCTURES."/$structure.php");
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

mysql_close($connection);

?>
