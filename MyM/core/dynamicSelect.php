<?php
/*
   File: dynamicSelect.php | (c) Giovanni Sileno 2006, 2007
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
   This file prints values in the db as options for a select input.
*/

  // TO BE CORRECT: security HOLE!
  // Everyone could access to these results!
  
  require_once('./baseMyM.php'); // include MyM 
  MyMsetup('../');

  // Read data input
  $db = strtolower(getpost("db"));
  $what = strtolower(getpost("what"));
  $where = strtolower(getpost("where"));

        if ($db == UNDEFINED) {
          $form .= "Associated database not valid.";
        }
       	else {
          if (!defined('MYM_PATH_STRUCTURES'))
            require_once(MYM_PATH."/structures/sources/".$db.".php");
		  else
            require_once(MYM_PATH_STRUCTURES."/".$db.".php");
          
          $set = $values = NULL;         
          
          $elem = new $db();  
          
          if (!property_exists($elem, $what)) // WARNING: Associating Field not valid!
            $what = UNDEFINED; 
           
          list($list, $n, $tot) = $elem->MyMlist($where);

          if ($list != NULL) {
            while ($id = array_pop($list)) {
              $elem->MyMread($id);    
              if ($what != UNDEFINED)
                $set[] = $elem->$what;
              else
                $set[] = $id;               
              $values[] = $id;
            }
            
            $form .= InputSelectOption("onid", $value, $set, $values, ($readonly != ""));
          }
          else
            $form .= "Empty database.";
        }
        
  echo $form;
?>