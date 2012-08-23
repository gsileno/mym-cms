<?php 
/*
   File: txtDB.php | (c) Giovanni Sileno 2006, 2010
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
   When executed, this file creates the tables as plain text databases.
*/

if (!defined('MYM_PATH'))
  define('MYM_PATH', realpath(dirname(__FILE__).'/../.'));

/* £ define('MYM_PATH_STRUCTURES', realpath(MYM_PATH.'/'.MYM_STRUCTURES_PATH));
if (!is_dir(MYM_PATH_STRUCTURES)) die(' > Not valid structures path: '.MYM_PATH_STRUCTURES.'.');
define('MYM_PATH_TXTDB', realpath(MYM_PATH.'/'.MYM_TXTDB_PATH));
if (!MYSQL) if (!is_dir(MYM_PATH_TXTDB)) die(' > Not valid txtDB path: '.MYM_PATH_TXTDB.'.'); */

if (!defined('APP_RELATIVE_PATH'))
    show_error('Please define the relative path to MyM from your script directory.');

/* require_once(MYM_PATH."/core/baseMyM.php");
require_once(MYM_PATH."/core/txtdb.php");

MyMsetuppath(APP_RELATIVE_PATH); */

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

