<?php 
/*
   File: MyMelement.php | (c) Giovanni Sileno 2006, 2007
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
   This file contains the kernel functions for MyM.
*/
define("MYM_ELEMENT_TRACE", 0);

trace(MYM_ELEMENT_TRACE + 1, "core | MyMelement > included");

global $mysql;
if (!isset($mysql)) $mysql = defined(MYM_MYSQL);

MyMinclude("/core/MyMprocess");
MyMinclude("/core/baseMyM");
if ($mysql) MyMinclude("/core/baseMySQL");
else { MyMinclude("/core/baseTxtDB");  MyMinclude("/core/txtDB");  }

class MyMelement
{  
   var $id = UNDEFINED; // TODO in PHP5 protected
   var $db = UNDEFINED; // TODO in PHP5 protected

   // GENERAL DB for USER MANAGEMENT
   function ownerdb() { // General db for management user    // UM
     if (defined("MYM_USER_DB")) return MYM_USER_DB;         // UM
     else return "";                                         // UM 
   }                                                         // UM
   
   // in the general case set the same of the general config
   function usemysql() {
     return defined('MYM_MYSQL');
   }                    

   // in the general case there are many object saved in a DB (true if singleton)
   function staticarray() {
     return false;
   }

   // -------------------------------------------
   //  Upload path management
   // -------------------------------------------

   // in the general case everything is saved in the main upload path
   function uploadinternalpath() {
     return './';
   }

   // function to give absolute position to uploaded files
   function fileabspath($filename) {
       return MYM_UPLOAD_REALPATH."/".$this->uploadinternalpath().'/'.$filename;
   }

   // function to give url position to uploaded files
   function fileurlpath($filename) {
       $path = MYM_UPLOAD_PATH."/".$this->uploadinternalpath().'/'.$filename;
       
       $pattern = array('|\./|', '|/{2,}|');
       $replace = array("", "/");

       $path = preg_replace($pattern, $replace, $path);

       $path = ROOT_URI."/".$path;
       return $path;
   }
   
   function filename($key) {
      if (is_file($this->fileabspath($this->$key)))
        return $this->fileurlpath($this->$key);
      else 
        return false;
   }
   
   function resized_filename($key) {
     if (is_file($this->fileabspath("resized_".$this->$key)))
        return $this->fileurlpath("resized_".$this->$key);
     else 
        return $this->filename($key);
   }

   function thumb_filename($key) {
     if (is_file($this->fileabspath("thumb_".$this->$key)))
        return $this->fileurlpath("thumb_".$this->$key);
     else 
        return $this->filename($key);
   }

   // -------------------------------------------
   //  Handling flags array inside the class
   // -------------------------------------------
      
   function flagarray($name) {
       if (!method_exists($this, "flagarray_$name")) {
         print("<p class='MyMmsg'><span class='Error'><strong>Warning</strong> there is no flag array <em>$name</em>.</span></p>");
         return false; 
       }
       eval('$array = $this'."->flagarray_$name();");
       return $array;
   }
      
   function flagarray_value($name, $index)  {   
       $array = $this->flagarray($name);
       if (!$array) return false;

       if ($index < count($array))
         return $array[$index]; 
       else 
         print("<p class='MyMmsg'><span class='Error'><strong>Warning</strong> index $i has no correspondent in the flag array <em>$name</em>.</span></p>");
       return false;
   }

   function flagarray_index($name, $field)  {   
       $array = $this->flagarray($name);
       if (!$array) return false;
       
       $array = array_flip($array);
       
       if (array_key_exists($field, $array))
         return $array[$field]; 
       else 
         print("<p class='MyMmsg'><span class='Error'><strong>Warning</strong> field <em>$field</em> has no correspondent in the array flag <em>$name</em>.</span></p>");
       return false;
   }
   
   // -------------------------------------------
   //  Privilege Management
   // -------------------------------------------

   // getpriv
   // return the priv of the current user
   function getpriv() {
     $userdb = $this->ownerdb();
     return session($userdb.'_priv', MYM_NOT_LOGGED_USER_PRIV);
   }
   
   function isyours() {
     $userdb = $this->ownerdb();
     $uid = $this->Field(_OWNERFIELD);
     return (session($userdb.'_id') == $this->$uid);
   }   

   function MyMpriv($action = UNDEFINED) {
     if ($action == UNDEFINED) return false;

     if (!defined("MYM_USER_DB")) return true;
          
     $priv = $this->MyMcheckpriv($action);
     
     if ($priv == _NONE) return false;
     
     if ($priv == _OWN)
       if ($this->isyours()) return true;
       else return false;
       
     return true;
   }

   // MyMcheckpriv
   // check if current session privilege is sufficent for the 
   // current session user to do the action $action.
   // Inputs: $action is the action to be performed 
   // Returns an enum value: _NONE, _OWN, _ALL
   function MyMcheckpriv($action = UNDEFINED) {
     trace(MYM_ELEMENT_TRACE + 3, $this->db." > MyMcheckpriv (action: $action)");
     
     if ($action == UNDEFINED) return false;
       
     if (!defined("MYM_USER_DB")) return true;
     
     $userpriv = $this->getpriv();

     if ($userpriv >= $this->Privilege($action))    
       return _ALL;

     if ($this->Field(_OWNERFIELD) && $userpriv >= $this->Privilege($action + 3))
         return _OWN;

     return _NONE;
   } 

   // MyMprocessprint()
   function MyMprocessprint($link = false, $makelink = "makelink") { 
     trace(MYM_ELEMENT_TRACE + 3, $this->db." > MyMprocessprint");

     $key = $this->Field(_VIEWFIELD);
     if ($key) {
       $rule = $this->Type($key);
       $item = $rule->basicprintItem($this->$key, session($this->ownerdb().'_priv', MYM_NOT_LOGGED_USER_PRIV));
       if ($link)
         return $makelink($item, 'read', $this->db, $this->id);
       else
         return $item;
     }
   }
   
   // MyMprint()
   function MyMprint() {
     trace(MYM_ELEMENT_TRACE + 3, $this->db." > MyMprint");
          
     $rules = $this->MyMrules();
   
     print("<div id='table3col' class='".$this->db."'>\n");
     print("<table>");
     
     $first = true;
     $arraykeys = array_keys($rules);     
     for($i = 0; $i < count($arraykeys); $i++) {
       $key = $arraykeys[$i];
       $item = $rules[$key]->printItem($this->$key, session($this->ownerdb().'_priv', MYM_NOT_LOGGED_USER_PRIV));
              
       if ($item) {
         print("<tr>");
         print("<td class='ultraleft'>");
         if ($first) {
           print(makelink($this->id.".", 'read', $this->db, $this->id));
           $first = false;
         }
         else
           print("&nbsp;");
         print("</td>\n");
         print($item);
         print("</tr>");
       }
     }
     print("</table>");
     print("</div>\n");
   }

   // This functions returns (other versions IDs in the same language,  most recent ID for each other language )  
   function othersId($unid = UNDEFINED, $lng = UNDEFINED, $unidfield = UNDEFINED, $lngfield = UNDEFINED, $datefield = UNDEFINED) {
     trace(MYM_ELEMENT_TRACE + 2, $this->db." > othersId (unid = $unid, lng = $lng, unidfield = $unidfield, lngfield = $lngfield, datefield = $datefield)");
     global $mysql;

     if ($mysql) {
     
       // look for the history of elements
       $query = "SELECT id";
       if ($datefield != 'id') $query .= ", $datefield";      
        $query .= " FROM ".MYM_MYSQL_PREFIX.$this->db."s WHERE $unidfield = '$unid'";
       if ($lngfield != UNDEFINED) $query .= " AND $lngfield = '".$lng."'";
        $query .= " ORDER BY $datefield DESC";
       trace(MYM_ELEMENT_TRACE + 2, " > othersId > All historical version of unid $unid query: $query.");
       list($listold, $n) = ListQuery('othersId > History of elements > ', $query);       
       // for ($i = 0; $i < $n; $i ++) 
       trace_r(MYM_ELEMENT_TRACE + 2, " > othersId > All version of unid $unid ", $listold);

       $listlng = NULL;
       if ($lngfield != UNDEFINED) {
         // look for disponibles languages
         $query = "SELECT $lngfield FROM ".MYM_MYSQL_PREFIX.$this->db."s WHERE $unidfield = '$unid';";       
         list($listlng, $nlng) = ListQuery('othersId > Possible languages > ', $query);       
         
         for ($i = 0; $i < $nlng; $i ++) {
             // look for the last modified element for each language         
           $query = "SELECT id FROM ".MYM_MYSQL_PREFIX.$this->db."s WHERE $unidfield = '$unid' AND $lngfield = '".$listlng[$i]['lng']."' ORDER BY $datefield DESC LIMIT 1;";
           trace(MYM_ELEMENT_TRACE + 2, " > othersId > Last version of unid $unid in language ".$listlng[$i].": $query.");
           $id = OneQuery('othersId', $query);       
           trace_r(MYM_ELEMENT_TRACE + 2, " > othersId > Last modified in this lng : ", $id);
           $listlng[$i]['id'] = $id;
          }        
       }        
     }
     else {

       $dbtable = openDB($this->db);
       
       // look for the history of elements
       // if ($datefield != 'id') $query .= ", $datefield";      
       $query = "(\$".$unidfield." == ".$unid.")";
       if ($lngfield != UNDEFINED) $query .= " && (\$".$lngfield." == '".$lng."')";
        
       $listid = $dbtable->select($query);
       $n = count($listid);
       $listold = $dbtable->order($datefield, false, $listid, true);
       
       trace(MYM_ELEMENT_TRACE + 2, " > othersId > All historical version of unid $unid query: $query.");
       trace_r(MYM_ELEMENT_TRACE + 2, " > othersId > All version of unid $unid ", $listold);

       $listlng = NULL;
       if ($lngfield != UNDEFINED) {

         $query = "(\$".$unidfield." == ".$unid.")";
         $listid = $dbtable->select($query);
       
         // look for disponibles languages
         $lngs = $dbtable->distinct($lngfield, $listid); 
         $nlng = count($lngs);

         trace_r(MYM_ELEMENT_TRACE + 2, " > othersId > Language available for unid $unid : ", $lngs);
         
         for ($i = 0; $i < $nlng; $i ++) {
               
           // look for the last modified element for each language         
           $query = "(\$".$unidfield." == ".$unid.") && (\$".$lngfield." == '".$lngs[$i]."')";
           trace(MYM_ELEMENT_TRACE + 2, " > othersId > Last version of unid $unid in language ".$lngs[$i].": $query.");
           
           $listid = $dbtable->select($query);
           $result = $dbtable->order($datefield, false, $listid);
           $id = $result[0];
           
           trace_r(MYM_ELEMENT_TRACE + 2, " > othersId > Last modified in this lng : ", $id);
           $listlng[$i][$lngfield] = $lngs[$i];
           $listlng[$i]['id'] = $id;
          }        
       }
      }
     
     return array($listold, $listlng);
   }   

   // Choose the right ID (most recent and in the right language, and return it
   // if others is true this functions returns (right ID, other same language IDs, other languages most recent ID)  
   function chooseId($unid = UNDEFINED, $unidfield = UNDEFINED, $lngfield = UNDEFINED, $datefield = UNDEFINED) {   
     trace(MYM_ELEMENT_TRACE + 2, $this->db." > chooseId (unid = $unid, unidfield = $unidfield, lngfield = $lngfield, datefield = $datefield)");

     global $mysql;
     if ($mysql) {
     
       // look for the last modified element
       if ($lngfield == UNDEFINED) {
         $query = "SELECT id FROM ".MYM_MYSQL_PREFIX.$this->db."s WHERE $unidfield = '$unid' ORDER BY $datefield DESC LIMIT 1;";
         $id = OneQuery('chooseId', $query);       
         trace(MYM_ELEMENT_TRACE + 2, " > chooseId > Last modified ($query): ". $id);
       } 
       else {
         // look for the last modified element in the language given by the session
         $query = "SELECT id FROM ".MYM_MYSQL_PREFIX.$this->db."s WHERE $unidfield = '$unid' AND $lngfield = '".session('lng')."' ORDER BY $datefield DESC LIMIT 1;";       
         $id = OneQuery('chooseId', $query);       
         trace_r(MYM_ELEMENT_TRACE + 2, " > chooseId > Last modified id by session lng ($query): ", $id);
         
         if ($id == NULL) {
           // look for the last modified element in the default language         
           $query = "SELECT id FROM ".MYM_MYSQL_PREFIX.$this->db."s WHERE $unidfield = '$unid' AND $lngfield = '".MYM_DEFAULT_LANGUAGE."' ORDER BY $datefield DESC LIMIT 1;";
           $id = OneQuery('chooseId', $query);       
           trace_r(MYM_ELEMENT_TRACE + 2, " > chooseId > Last modified by default lng ($query): ", $id);
        
           if ($id == NULL) {
             // look for the last modified element in the first available language       
             $query = "SELECT id FROM ".MYM_MYSQL_PREFIX.$this->db."s WHERE $unidfield = '$unid' ORDER BY $datefield DESC LIMIT 1;";
             $id = OneQuery('chooseId', $query);       
             trace_r(MYM_ELEMENT_TRACE + 2, " > chooseId > Last modified ($query): ", $id);
           } 
         }
       }
       
       $result = $id;
     }
     else {

       $dbtable = OpenDB($this->db);

       // look for the last modified element
       if ($lngfield == UNDEFINED) {

         $query = "\$".$unidfield." == ".$unid;
         $listid = $dbtable->select($query);
         $result = $dbtable->order($datefield, false, $listid);
         $id = $result[0];
         trace(MYM_ELEMENT_TRACE + 2, " > chooseId > Last modified ($query): ". $id);
       } 
       else {
         // look for the last modified element in the language given by the session       
         $query = "(\$".$unidfield." == ".$unid.") && (\$".$lngfield." == '".session('lng')."')";
         $listid = $dbtable->select($query);
         $result = $dbtable->order($datefield, false, $listid);
         if ($result) {
           $id = $result[0];
           trace_r(MYM_ELEMENT_TRACE + 2, " > chooseId > Last modified id by session lng ($query): ", $id);
         }

         if (!$result) {
           // look for the last modified element in the default language         
           $query = "(\$".$unidfield." == ".$unid.") && (\$".$lngfield." == '".MYM_DEFAULT_LANGUAGE."')";
           $listid = $dbtable->select($query);
           $result = $dbtable->order($datefield, false, $listid);
           if ($result) {
             $id = $result[0];
             trace_r(MYM_ELEMENT_TRACE + 2, " > chooseId > Last modified by default lng ($query): ", $id);
           }
        
           if (!$result) {
             // look for the last modified element in the first available language
             $query = "\$".$unidfield." == ".$unid;
             $listid = $dbtable->select($query);
             $result = $dbtable->order($datefield, false, $listid);
             if ($result) {
               $id = $result[0];
               trace_r(MYM_ELEMENT_TRACE + 2, " > chooseId > Last modified by default lng ($query): ", $id);
             }
           } 
         }
       }
     
     }

     if (!$result) tracedie(" > chooseId > There must be some error. (db : ".$this->db.", unid : ".$unid.").");
  
     return $id;           
   }

   function advanced() {
     global $txt;
   
      // Look for derivation field
     if (!($unidfield = $this->Field(_DERIVATIONOFFIELD)))
       $unidfield = "id";

      // Look for language field
     if (!($lngfield = $this->Field(_LANGUAGEFIELD))) 
       $lngfield = UNDEFINED;
      
      // Look for date field
     if (!($datefield = $this->Field(_DATEFIELD))) 
       $datefield = 'id';
        
      // Check the right of the user        
     $checkpriv = $this->MyMcheckpriv(_READ);                             // UM
     if ($checkpriv == _NONE) {                                           // UM
       print("<p class='MyMmsg'>".$txt['nopermission']."</p>");  // UM
     }                                                                    // UM

     return array($unidfield, $lngfield, $datefield, $checkpriv);
   }
   
   // Find the other versions recorded in the db for a given element
   function MyMadvothers($id = UNDEFINED, $lng = UNDEFINED) {
     trace(MYM_ELEMENT_TRACE + 1, $this->db." > MyMadvothers (id = $id)");

     list($unidfield, $lngfield, $datefield, $checkpriv) = $this->advanced();
      
     // Check inputs
     if ($id != UNDEFINED)
     $this->MyMread($id);
     $unid = $this->$unidfield;      

     if ($lng == UNDEFINED) {
       if ($lngfield != UNDEFINED) 
         $lng = $this->$lngfield;
     }

     if ($unid == UNDEFINED || $checkpriv == _NONE)
       return false;

     return $this->othersId($unid, $lng, $unidfield, $lngfield, $datefield);
   }
   
   // Read in a intelligent way: the last upate, in the choosed language (if defined)
   function MyMadvread($id = UNDEFINED, $what = "*") {
     trace(MYM_ELEMENT_TRACE + 3, $this->db." > MyMadvread (id = $id, what = $what)");

     list($unidfield, $lngfield, $datefield, $checkpriv) = $this->advanced();      

      // Check inputs 
      if ($id != UNDEFINED)
       $this->MyMread($id);
     $unid = $this->$unidfield;      

     if ($unid == UNDEFINED || $checkpriv == _NONE)
        return false;
     
      $id = $this->chooseId($unid, $unidfield, $lngfield, $datefield);
     trace(MYM_ELEMENT_TRACE + 3, $this->db." > MyMadvread > id : ". $id);
     return $this->MyMread($id, $what);
   }                                                                    

   function OnlyOwn($op = "=") {
     return $this->Field(_OWNERFIELD)." $op '".session($this->ownerdb()."_id")."'";
   }

   // Read a single element from database and return it as an array
   function MyMreadarray($id = UNDEFINED, $what = "*") {   
     trace(MYM_ELEMENT_TRACE + 3, $this->db." > MyMread (id = $id, what = $what)");
     global $txt, $mysql;
     
      // Check inputs 
      if ($id != UNDEFINED)
        $this->id = $id;
      else if ($this->id == UNDEFINED)
        return false;
        
     if ($what == "" || $what = UNDEFINED)
       $what = "*";
       
      // Check the right of the user        
     $checkpriv = $this->MyMcheckpriv(_READ);                             // UM
     if ($checkpriv == _NONE) {                                           // UM
       print("<p class='MyMmsg'>".$txt['nopermission']."</p>");  // UM
       return false;                                                      // UM
     }                                                                    // UM
            
     if ($mysql) {
       $query = "SELECT $what FROM ".MYM_MYSQL_PREFIX.$this->db."s WHERE id = '".$this->id."'";      
       if ($checkpriv == _OWN)              // UM
         $query .= "AND ".$this->OnlyOwn(); // UM

       $result = Query('MyMread', $query);
     }
     else {
       
       $dbtable = OpenDB($this->db);
       
       $result = $dbtable->readElement($this->id);

       if ($checkpriv == _OWN)                // UM
         if ($result[$this->Field(_OWNERFIELD)] != session($this->ownerdb()."_id")) // UM
            $result = false;
       
     }     
     return $result;
   }                        
   
   // Read a single element from database and store it in the calling object
   function MyMread($id = UNDEFINED, $what = "*") {
     global $mysql;
     $result = $this->MyMreadarray($id, $what);
     
     if ($result != NULL) { 
       if ($mysql)     
         $this->MyMsetfromMySQL($result);
       else
         $this->MyMsetfromTxtDB($result);
       return true;
      }
      else
        return false;
   }

   // Read a single element from database and print its values
   // WARNING: this function change the content of the object because it executes MyMread
   function MyMreadprint($id = UNDEFINED, $what = "*", $printfunction = "MyMprint") {
   
     trace(MYM_ELEMENT_TRACE + 3, $this->db." > MyMreadprint (id = $id, what = $what, printfunction = $printfunction)");

     if ($this->MyMread($id, $what)) {  
        $this->$printfunction();
       return true;
     }
     return false;
   }

   // Return a list of elements, as an array of id
   // Choose in a intelligent way: for each unid, the last upate, in the choosed language (if defined)
   // TODO: I don't know how $order works
   function MyMadvlist($where = "", $order = "", $limit = "") {
     trace(MYM_ELEMENT_TRACE + 3, $this->db." > MyMadvlist (where = $where, order = $order, limit = $limit)");

     global $mysql;

     list($unidfield, $lngfield, $datefield, $checkpriv) = $this->advanced();
     if ($checkpriv == _NONE)
       return false;

     if ($mysql) {
       // Look for all the elements accessibles to the user, defined by an univocal id (UNID)
       $query = "SELECT DISTINCT $unidfield FROM ".MYM_MYSQL_PREFIX.$this->db."s ";
       if ($where != "") {
         $query .= "WHERE $where ";
         if ($checkpriv == _OWN)              // UM
           $query .= "AND ".$this->OnlyOwn(); // UM
       }
       else if ($checkpriv == _OWN)           // UM
         $query .= "WHERE ".$this->OnlyOwn(); // UM

       $querytot = $query;
      
       if ($order != "") $query .= "ORDER BY $order ";
       else $query .= "ORDER BY id DESC ";       
       if ($limit != "") $query .= "LIMIT $limit ";
     
       trace(MYM_ELEMENT_TRACE + 2, " > MyMadvlist > List Tot Unid query $querytot ");
       list($listtot, $ntot) = ListQuery('MyMadvlist', $querytot);
     
       trace(MYM_ELEMENT_TRACE + 2, " > MyMadvlist > List Unid query $query");
       list($list, $n) = ListQuery('MyMadvlist', $query);    

       $listid = NULL; $nid = 0;     
 
       // For every UNID choose the right ID (Last modified, right or possible Language)
       for ($i = 0; $i < $n; $i ++) 
         $listid[] = $this->chooseId($list[$i][$unidfield], $unidfield, $lngfield, $datefield);

     }
     else {
       
       $dbtable = OpenDB($this->db);

       if ($where == '')
         $where = true;

       if (strtolower($order) == "id")
         $order = "";

       if ($checkpriv == _OWN)                    // UM
         $where .= " && \$".$this->OnlyOwn("=="); // UM

       $listid = $dbtable->select($where);

       $asc = true;
       if ($order != "") {
         if ($pos = strpos($order, 'desc')) {
           $order = trim(substr($order, 0, $pos));
           $asc = false;
         }
         else if ($pos = strpos($order, 'asc'))
           $order = trim(substr($order, 0, $pos));
         
         $listid = $dbtable->order($order, $asc, $listid);
       }

       if ($unidfield != "id")
         $listunid = $dbtable->distinct($unidfield, $listid);
       else
         $listunid = $listid;

       $ntot = count($listunid);
         
       if ($limit != "") {
         if (strpos($limit, ','))
           list($bottom, $top) = explode(',', trim($limit)); // TODO
         else {
           $bottom = 0;
           $top = $limit;
         }
         
         $limitedunidlist = NULL;
         for($i = $bottom; ($i < $top) && ($i < count($listunid)); $i++) {
           $limitedunidlist[$i] = $listunid[$i];
         }

         $listunid = $limitedunidlist;
       } else { $bottom = 0; }

       trace_r(MYM_ELEMENT_TRACE + 2, " > MyMadvlist > List Unid ", $listunid);

       $n = count($listunid);

       // For every UNID choose the right ID (Last modified, right or possible Language)
       $listid = NULL;
       for ($i = $bottom; $i < $bottom + $n; $i ++) 
         $listid[] = $this->chooseId($listunid[$i], $unidfield, $lngfield, $datefield);

    }
     
     trace_r(MYM_ELEMENT_TRACE + 3, $this->db." > MyMadvlist > ", $listid);
     return array($listid, $n, $ntot);
   }                                                                    
   
   // Return a list of elements, as an array of id
   function MyMlist($where = "", $order = "", $limit = "") {
     trace(MYM_ELEMENT_TRACE + 3, $this->db." > MyMlist (where = $where, order = $order, limit = $limit)");

     global $mysql;
     
     $checkpriv = $this->MyMcheckpriv(_READ);                             // UM
     if ($checkpriv == _NONE) {                                           // UM
       print("<p class='MyMmsg'>".$txt['nopermission']."</p>");  // UM
       return false;                                                      // UM
     }                                                                    // UM

     if ($mysql) {

       // Look for all the elements accessibles to the user, defined by an univocal id (UNID)
        $query = "SELECT id FROM ".MYM_MYSQL_PREFIX.$this->db."s ";
       if ($where != "") {
         $query .= "WHERE $where ";
         if ($checkpriv == _OWN)              // UM
           $query .= "AND ".$this->OnlyOwn(); // UM
       }
       else if ($checkpriv == _OWN)           // UM
         $query .= "WHERE ".$this->OnlyOwn(); // UM

        $querytot = $query;
      
       if ($order != "") $query .= "ORDER BY $order ";
       else $query .= "ORDER BY id DESC ";
       if ($limit != "") $query .= "LIMIT $limit ";
     
       trace(MYM_ELEMENT_TRACE + 2, $this->db." > MyMlist > Query: ". $query);

       list($listtot, $ntot) = ListQuery('MyMlist', $querytot);
       trace_r(MYM_ELEMENT_TRACE + 2, " > MyMlist > List Tot Unid: ", $listtot);
     
       list($list, $n) = ListQuery('MyMlist', $query);     
       trace_r(MYM_ELEMENT_TRACE + 2, " > MyMlist > List Unid: ", $list);

       $listid = NULL;
       for ($i = 0; $i < $n; $i ++) 
         $listid[] = $list[$i]['id'];
     }
     else {
       $dbtable = OpenDB($this->db);

       if ($where == '')
         $where = true;

       if ($checkpriv == _OWN)                    // UM
         $where .= " && \$".$this->OnlyOwn("=="); // UM

       $listid = $dbtable->select($where);
       
       $asc = false;
       if ($order != "") {
         if ($pos = strpos($order, 'desc')) {
           $order = trim(substr($order, 0, $pos));
           $asc = false;
         }
         else if ($pos = strpos($order, 'asc')) {
           $asc = true;
           $order = trim(substr($order, 0, $pos));
         }         
         $listid = $dbtable->order($order, $asc, $listid);
       }

       $ntot = count($listid);

       if ($limit != "") {
         if (strpos($limit, ','))
           list($bottom, $top) = explode(',', trim($limit)); // TODO
         else {
           $bottom = 0;
           $top = $limit;
         }
         
         $limitedlist = NULL;
         for($i = $bottom; ($i < $top) && ($i < count($listid)); $i++) {
           $limitedlist[$i] = $listid[$i];
         }

         $listid = $limitedlist;
       }
       
       $n = count($listid);
     } 

     trace(MYM_ELEMENT_TRACE + 3, $this->db." > MyMlist > n = $n, tot = $ntot ");

     return array($listid, $n, $ntot);
   }

   // Read a list of elements from database and print their values
   function MyMlistprint($where = "", $order = "", $limit = "", $printfunction = "MyMprint") {
     trace(MYM_ELEMENT_TRACE + 3, $this->db." > MyMlistprint (printfunction = $printfunction)");

     $rules = $this->MyMrules();

     list($list, $n, $tot) = $this->MyMlist($where, $order, $limit);

     while ($list != NULL) {
       $id = array_shift($list);
       $this->MyMreadprint($id, "*", $printfunction);        
     }     
     
     return array($list, $n, $tot);
   }

   // Read a list of elements from database and print their values
   function MyMadvlistprint($where = "", $order = "", $limit = "", $printfunction = "MyMprint") {
     trace(MYM_ELEMENT_TRACE + 3, $this->db." > MyMadvlistprint (printfunction = $printfunction)");

     $rules = $this->MyMrules();

     list($list, $n, $tot) = $this->MyMadvlist($where, $order, $limit);
 
     while ($list != NULL) {
       $id = array_shift($list);
       $this->MyMreadprint($id, "*", $printfunction);        
     }
     
     return array($list, $n, $tot);
   }

   // Generate form for a new/existing element
   // $rules is an array generated by MyMrules   
   function MyMwrite($new = true, $read = true, $fileaction = "index.php", $hiddenfields = array(), $hiddeninputs = array()) {
     global $txt;
     trace(MYM_ELEMENT_TRACE + 1, $this->db." > MyMwrite");

     $rules = $this->MyMrules();

     if ($rules == NULL) {
       print("<p class='MyMmsg'><span class='Error'><strong>Warning</strong> this structure has no fields.</span></p>");
       return;
     }

     $checkpriv = $this->MyMcheckpriv(_WRITE);                 // UM
     if ($checkpriv == _NONE) {                                // UM
       print("<p class='MyMmsg'>".$txt['notallowed']."</p>"); // UM
       return;                                                 // UM
     }                                                         // UM

     $nextaction = 'write2';
     if ($this->id != UNDEFINED) {
       if (!$new)
         $nextaction = 'modify2';
       
       if ($read) {
         $this->MyMread();
         trace_r(MYM_ELEMENT_TRACE + 1, $this->db." > MyMwrite > Existing value read", $this);
         if ($checkpriv == _OWN) {
           $owner = $this->Field(_OWNERFIELD);
           if ($this->$owner != session($this->ownerdb()."_id")) {   // UM
             print("<p class='MyMmsg'>".$txt['notyours']."</p>"); // UM
             return false;                                                                            // UM      // UM
           }                                                                                          // UM
         }
       }         
     }
     
     trace(MYM_ELEMENT_TRACE + 1, $this->db." > MyMwrite > next action: $nextaction");

     print("<div id='table2col'>\n");
     print("<form name='".$this->db."' action='$fileaction' method='post' enctype='multipart/form-data' onSubmit='return validation();' onReset='return validation();'>\n");
     print("  <input type='hidden' name='a' value='".$nextaction."' />\n");
     print("  <input type='hidden' name='o' value='".$this->db."' />\n");
     print("  <input type='hidden' name='id' value='".$this->id."' />\n"); 
     
     foreach ($hiddeninputs as $hiddeninput => $value) {
       print("  <input type='hidden' name='$hiddeninput' value='$value' />\n"); 
     }
     
     print("  <table>\n");
     
     $arraykeys = array_keys($rules);    
     for($i = 0; $i < count($arraykeys); $i++) {
       $key = $arraykeys[$i];
       
       if ($rules[$key]->type == _OWNER) {  
          $this->$key = session($this->ownerdb()."_id");     
       } 
       else if ($rules[$key]->type == _OWNERLNG) { 
          $this->$key = session('lng');
       }       
       else if ($rules[$key]->type == _OWNERIP) { 
          $this->$key = $_SERVER["REMOTE_ADDR"];
       }       
       
       if (!in_array($key, $hiddenfields))      
         if ($form = $rules[$key]->InputForm($this->$key, session($this->ownerdb().'_priv', MYM_NOT_LOGGED_USER_PRIV)))
           print("<tr>\n  $form\n</tr>\n");
     }

     // UM
     $userdb = $this->ownerdb();
     if (session($userdb.'_id') == UNDEFINED) {
       // print("  <tr><td class='left'> &nbsp; </td> <td class='right'> &nbsp; </td></tr>");
       print("  <tr class='captcha'><td class='left'><span class='help'>{$txt['captchainfo']}</span></td> <td class='right'> <div class='input'><input type='text' id='captcha_input' name='captcha_input' size='15' /></div> <img src='".MYM_RELATIVE_PATH."/tools/captchaImage.php' alt='captcha image'/> </td></tr>");
     }

     print("  <tr><td class='left'> &nbsp; </td> <td class='rightbutton'>\n");
       print("  <input class='firstbutton' name='scratch_submit' id='scratch_submit' type='submit' value='".$txt['Save']."' />\n");
       print("  <input class='button' type='reset' value='".$txt['Reset']."' />\n");  
     print("  </td></tr>\n");
     print("  </table>\n");
     print("</form>\n");     
     print("</div>");
   }

   // Set internal id of the element 
   function MyMsetId($id = UNDEFINED) {
     // trace(MYM_ELEMENT_TRACE + 1, $this->db." > MyMsetId > Id :". $id);
     $this->id = $id;
   }
   
   // Set all internal values of the object 
   // through an array with the same names
   function MyMset($array, $force = false) {
     // trace_r(MYM_ELEMENT_TRACE + 1, $this->db." > MyMset > array :", $array);
          
     $keys = "";
     $arraykeys = array_keys($array);
     for($i = 0; $i < count($arraykeys); $i++) {
       $key = $arraykeys[$i];
       if (property_exists($this, $key)) {
         $this->$key = $array[$key];
       }
       else 
         if (!$force) print("<p class='MyMmsg'><span class='warning'><strong>Warning</strong> The key ".$key." does not exist.</span></p>");
     }
   }
   
   // Set all internal values of the object 
   // through an array with the same names
   function MyMsetfromMySQL($array) {
     // trace_r(MYM_ELEMENT_TRACE + 1, $this->db." > MyMset > array :", $array);
     
     $rules = $this->MyMrules();
     
     $keys = "";
     $arraykeys = array_keys($array);
     for($i = 0; $i < count($arraykeys); $i++) {
       $key = $arraykeys[$i];
       if (property_exists($this, $key)) {
         if ($key != 'id')
           $this->$key = $rules[$key]->fromMySQL($array[$key]);
         else
           $this->$key = $array[$key];
       }
       else 
         print("<p class='MyMmsg'><span class='warning'><strong>Warning</strong> The key ".$key." does not exist.</span></p>");
     }
   }

   // Set all internal values of the object 
   // through an array with the same names
   function MyMsetfromTxtDB($array) {
     
     $rules = $this->MyMrules();
     
     $keys = "";     
     // $this->id = $id;
     
     $arraykeys = array_keys($array);
     for($i = 0; $i < count($arraykeys); $i++) {
       $key = $arraykeys[$i];
       if (property_exists($this, $key)) {
         if ($key != 'id')
           $this->$key = $rules[$key]->fromTxtDB($array[$key]);
         else
           $this->$key = $array[$key];       
       }
       else 
         print("<p class='MyMmsg'><span class='warning'><strong>Warning</strong> The key ".$key." does not exist.</span></p>");
     }
   }

   // Set all internal values of the element
   // looking for a POST variable with the same name  
   // force is true if you want to write UNDEFINED value 
   //               when keys are not definied POST variables
   function MyMsetbypost($force = false) {
     trace(MYM_ELEMENT_TRACE + 1, $this->db." > MyMsetbypost");

     $rules = $this->MyMrules();

     $object = get_object_vars($this);
     $arraykeys = array_keys($object);
     for($i = 0; $i < count($arraykeys); $i++) {
       $key = $arraykeys[$i];
       if ($key != 'db') { // TODO - if $id ignored modify doesn't work
         trace(MYM_ELEMENT_TRACE + 2, $this->db." > MyMsetbypost > key : $key");
         if (array_key_exists($key, $_POST)) {
           if ($key != 'id')
             $this->$key = $rules[$key]->fromPost($_POST[$key]); 
           else
             $this->$key = $_POST[$key];
           trace(MYM_ELEMENT_TRACE + 2, $this->db." > MyMsetbypost > ... posted");
         }
         else if ($force)
             $this->$key = UNDEFINED;
       }           
     }
   }   
   
   // Return all the internal values of an element
   // into an array with the same names
   function MyMget($force = false) {
     // return get_object_vars($this); // TODO, sufficient in PHP5
     
     $object = get_object_vars($this);
     $arraykeys = array_keys($object);
     for($i = 0; $i < count($arraykeys); $i++) {
       $key = $arraykeys[$i];
       if ($key != 'db')
         if ($this->$key != UNDEFINED || $force)
           $array[$key] = $object[$key];         
     }
     return $array;
   }   
   
   // Record a new/existing element
   function MyMrecord($new = true, $um = true, $captchacheck = true) {
     global $nqueries, $txt;
     global $mysql;     

     trace(MYM_ELEMENT_TRACE + 1, $this->db." > MyMrecord");
     trace_r(MYM_ELEMENT_TRACE + 1, $this->db." > MyMrecord > element :", $this);
	 
     $rules = $this->MyMrules();
      
     if ($um) {
       $checkpriv = $this->MyMcheckpriv(_WRITE);                  // UM
       if ($checkpriv == _NONE) {                                 // UM
         print("<p class='MyMmsg'>".$txt['notallowed']."</p>");   // UM
         return false;                                            // UM
       }                                                          // UM
     }

     if (!$mysql) {
       $dbtable = OpenDB($this->db);
       trace_r(MYM_ELEMENT_TRACE + 2, " > MyMrecord > dbtable :", $dbtable);
     }

     trace(MYM_ELEMENT_TRACE + 1, "id: ".$this->id.", method: ".$this->Field(_DERIVATIONOFFIELD).", new: ". $new);

     // new record
     //     if ($this->id == UNDEFINED || ($this->Field(_DERIVATIONOFFIELD) && $new))  {
     if ($this->id == UNDEFINED || $new)  { 
       trace(MYM_ELEMENT_TRACE + 3, "Writing new (or new version of existing) record.");

       if ($this->Field(_DERIVATIONOFFIELD)) {
         $unid = $this->Field(_DERIVATIONOFFIELD);
         if ($this->id != UNDEFINED && $this->$unid == UNDEFINED) {
           $this->$unid = $this->id;
         }         
       }
                     
       // copy content and prepare query
       $array = $this->MyMget(true); 
       $arraykeys = array_keys($array);

       $keys = ""; $values = "";
       for($i = 0; $i < count($arraykeys); $i++) {
         $key = $arraykeys[$i];

         if ($key != "id") {
           trace_r(MYM_ELEMENT_TRACE + 1, $this->db." > MyMrecord > $key type : ", $rules[$key]);

           if ($array[$key] === '' || $array[$key] === NULL)
              $array[$key] = UNDEFINED;
             
           if ($keys != "") {
             $keys .= ", ";
             $values .= ", ";
           }         
           $keys .= $key;
                                       
           $type = $rules[$key]->type;
           switch ($type) {
             case _TEXT :
             case _LONGTEXT :
             case _MYMTEXT :
               if ($array[$key] != UNDEFINED) {
                 MyMinclude("/core/MyMprocess");
                 if ($um) $array[$key] = Txt2Unicode($array[$key], 'utf8', true);
                 else $array[$key] = Txt2Unicode($array[$key]);
                 if ($mysql) $array[$key] = MySQLprotection($array[$key]);
               }
               break;
             
             case _VIDEO : 
             case _AUDIO :
             case _IMAGE : 
             case _FILE : 
               $file = files("file_".$key);
               if ($file != NULL) {
                 if ($file['error'] != 4) { // empty field, not true error
                   MyMinclude("/core/upload");
                   if ($filename = doupload($file, $type, MYM_UPLOAD_REALPATH."/".$this->uploadinternalpath())) {
                     if ($type == _IMAGE)
		       resizeimage($filename, MYM_UPLOAD_REALPATH."/".$this->uploadinternalpath(), $rules[$key]->maxwidth, $rules[$key]->maxheight, $rules[$key]->thumbsize, $rules[$key]->squaredthumb, $rules[$key]->minwidth, $rules[$key]->minheight);
                     $array[$key] = $filename;
                   }  
                   else {
                     $array[$key] = UNDEFINED;
                     return false;                                                                             // UM
                   }
                 } 
               } else 
                 $array[$key] = UNDEFINED;
               break;
               
             case _LISTID:
               $array[$key] = post("list_".$key);
               break;
             
             case _DATE :
               trace(MYM_ELEMENT_TRACE + 1, " > MyMrecord > $key > calendar output date: ".$array[$key]);
               
               if ($array[$key] != UNDEFINED) {
                 list($d, $mon, $y, $h, $min) = sscanf($array[$key], "%d/%d/%d, %d:%d");
                 trace(MYM_ELEMENT_TRACE + 1, " > MyMrecord > $key > italian format: $h:$min, $d/$mon/$y");
                 $mysqldate = date("Y-m-d H:i:00", mktime($h, $min, 0, $mon, $d, $y));
                 trace(MYM_ELEMENT_TRACE + 1, " > MyMrecord > $key > mysql date: ".$mysqldate);
                 $txtDBdate = mktime($h, $min, 0, $mon, $d, $y);
                 trace(MYM_ELEMENT_TRACE + 1, " > MyMrecord > $key > UNIX timestamp date: ".$txtDBdate);
                 if ($mysql) $array[$key] = $mysqldate;
                 else $array[$key] = $txtDBdate;                 
                  }      
               break;
                  
             case _PRIV :
               $array[$key] = MYM_NEW_USER_PRIV;
               break;
                  
             case _OWNER :
               $array[$key] = session($this->ownerdb()."_id");
               break;
               
             case _OWNERIP :          
               $this->$key = $_SERVER["REMOTE_ADDR"];
               break;
              
             case _OWNERLNG :
               $array[$key] = session("lng");
               break;
               
             case _NOW :
               $array[$key] = time();
               break;               
                  
             default :
           }                       
             
           if ($type != _NOW)  {
             if ($array[$key] != UNDEFINED)
               $values .= "'".$array[$key]."'";
             else
               $values .= "NULL";
           } else $values .= "NOW()";

             
           if ($rules[$key]->primary) {
             
             // Look for existing value
             if ($mysql) {           
               $query = "SELECT id FROM ".MYM_MYSQL_PREFIX.$this->db."s WHERE ".$key." = '".$array[$key]."';";
               $result = Query($this->db." > MyMrecord > Query for primary key", $query);
             } else {
               $result = $dbtable->select("(strtolower(\$".$key.") == '".strtolower($array[$key])."')");
             }
             
             if ($result != NULL) {
               print("<p class='MyMmsg'>".txt('sorry').", <strong>".$array[$key]."</strong> ".txt('alreadyexists')."</p>"); // UM
               return false;
             }  
           }
           
         }  
       }

       
       if ($um && $captchacheck) {
         $userdb = $this->ownerdb();
         if (session($userdb.'_id') == UNDEFINED) {
           if (!captchaCheck()) { // if CAPTCHAenabled
             print("<p class='MyMmsg'>".$txt['codenotcorrect']."</p>");
             return false;
           }
         }
       }

       if ($mysql) {   
         $insertion = "INSERT INTO ".MYM_MYSQL_PREFIX.$this->db."s ($keys) VALUES ($values);";  
         trace(MYM_ELEMENT_TRACE + 1, $this->db." > MyMrecord > insert query: ". $insertion);
              
         $id = insertQuery($this->db." > MyMrecord", $insertion);   
       } else {
         trace_r(MYM_ELEMENT_TRACE + 2, " > MyMrecord > array :", $array);
         $id = $dbtable->addElement($array);
       }
         
       if ($id === 0 || $id === NULL)
         return false;
       else {
         $this->id = $id;       
         if ($this->Field(_DERIVATIONOFFIELD)) {
           if ($this->$unid == UNDEFINED) {
             if ($mysql) {   
               $update = "UPDATE ".MYM_MYSQL_PREFIX.$this->db."s SET $unid = '$id' where id = '".$this->id."';";
               updateQuery($this->db." > MyMrecord", $update);
             } else {
               $array[$unid] = $id;
               $dbtable->modifyElement($id, $array);
             }
           }         
         }
         
         $this->MyMrecordOk();         
         return true;       
       } 
     }
     
     // existing record
     else {     
       trace(MYM_ELEMENT_TRACE + 1, "Modifying record.");

       // copy content and prepare query
       $array = $this->MyMget(true); 
       $arraykeys = array_keys($array);
         
       trace_r(MYM_ELEMENT_TRACE + 1, $this->db." > MyMrecord > element :", $array);
              
       if ($this->Field(_OWNERFIELD)) {
         if ($mysql) {   
       
           $query = "SELECT ".$this->Field(_OWNERFIELD)." FROM ".MYM_MYSQL_PREFIX.$this->db."s WHERE id = ".$this->id;         
           $uid = OneQuery($this->db." > MyMrecord", $query);
         } else {
           $elem = $dbtable->readElement($this->id);
           $uid = $elem[$this->Field(_OWNERFIELD)];
         }
         
         trace(MYM_ELEMENT_TRACE + 2, $this->db." > MyMrecord > owner of element ".$this->id.": ". $uid);
      
         if ($um) {
           if ($checkpriv == _OWN && $uid != session($this->ownerdb()."_id")) { // UM
             print("<p class='MyMmsg'>".$txt['notyours']."</p>"); // UM  
             return false;                                                                             // UM
           }                                                                                           // UM
         }
       } 
       
       $attributions = "";
       for($i = 0; $i < count($arraykeys); $i++) {
         $key = $arraykeys[$i];
         
         // TODO: check on the record to avoid damage
         if ($key != "id") {        
         
           $type = $rules[$key]->type;
           switch ($type) {
             case _TEXT :
             case _LONGTEXT :
             case _MYMTEXT :
               MyMinclude("/core/MyMprocess");            
               if ($um) $array[$key] = Txt2Unicode($array[$key], 'utf8', true);
               else $array[$key] = Txt2Unicode($array[$key]);
               if ($mysql) $array[$key] = MySQLprotection($array[$key]);
               break;
             
             case _VIDEO : 
             case _AUDIO :
             case _IMAGE : 
             case _FILE :
 	     
               $file = files("file_".$key);
               if ($file != NULL) {
		 $delete = post("del_".$key);
		 if ($delete == 'yes') {
                   $path = MYM_UPLOAD_REALPATH.'/';
                       $filename = post("oldfile_".$key);
                       if (is_file($path.$filename)) {
                         if (unlink($path.$filename))
                           print("<p class='MyMmsg'>The file $filename has been deleted.</p>");
                         else
                           print("<p class='MyMmsg'><span class='warning'><strong>Warning</strong> The file $filename cannot be deleted.</p>");
                         
                         if ($type == _IMAGE) {
                           if (unlink($path."thumb_".$filename))
                             print("<p class='MyMmsg'>The file thumb_$filename has been deleted.</p>");
                           else
                             print("<p class='MyMmsg'><span class='warning'><strong>Warning</strong> The file $filename cannot be deleted.</p>");
                         
                           if (unlink($path."resized_".$filename))
                             print("<p class='MyMmsg'>The file resized_$filename has been deleted.</p>");
                           else
                             print("<p class='MyMmsg'><span class='warning'><strong>Warning</strong> The file $filename cannot be deleted.</p>");
                         }
                       }
                     $array[$key] = NULL;
		 }
                 if ($file['error'] != 4) { // empty field, not true error
                   MyMinclude("/core/upload");
                   if ($filename = doupload($file, $type, MYM_UPLOAD_REALPATH."/".$this->uploadinternalpath())) {
                     if ($type == _IMAGE)                    
                       resizeimage($filename, MYM_UPLOAD_REALPATH."/".$this->uploadinternalpath(), $rules[$key]->maxwidth, $rules[$key]->maxheight, $rules[$key]->thumbsize, $rules[$key]->squaredthumb, $rules[$key]->minwidth, $rules[$key]->minheight);
                     $array[$key] = $filename;
                   }
                 } 
	       }
	       break;
               
             case _LISTID :
               $array[$key] = post("list_".$key);
               break;
             
             case _NOW :
               /* if ($array[$key] == '' || $array[$key] == NULL || $array[$key] == UNDEFINED) 
                 $array[$key] = date("d/m/Y, H:i"); */
             case _DATE :
               trace(MYM_ELEMENT_TRACE + 1, " > MyMrecord > $key > calendar output date: ".$array[$key]);
               if ($array[$key] == '' || $array[$key] == NULL) 
                 $array[$key] = NULL;
               else if ($array[$key] != UNDEFINED) {                  
                 list($d, $mon, $y, $h, $min) = sscanf($array[$key], "%d/%d/%d, %d:%d");
                 trace(MYM_ELEMENT_TRACE + 1, " > MyMrecord > $key > italian format: $h:$min, $d/$mon/$y");
                 $mysqldate = date("Y-m-d H:i:00", mktime($h, $min, 0, $mon, $d, $y));
                 trace(MYM_ELEMENT_TRACE + 1, " > MyMrecord > $key > mysql date: ".$mysqldate);
                 $txtDBdate = mktime($h, $min, 0, $mon, $d, $y);
                 trace(MYM_ELEMENT_TRACE + 1, " > MyMrecord > $key > UNIX timestamp date: ".$txtDBdate);
                 if ($mysql) $array[$key] = $mysqldate;
                 else $array[$key] = $txtDBdate;
               }
               break;

             default: 
               break;
           }

           if ($array[$key] != UNDEFINED) {
             if ($attributions != "" )
               $attributions .= ", ";
           
               if ($array[$key] == NULL || $array[$key] == '') 
                 $attributions .= $key. " = NULL";       
               else 
                 $attributions .= $key. " = '".$array[$key]."'";       
           }
             
           if ($rules[$key]->primary) {
             
             // Look for existing value
             if ($mysql) {           
               $query = "SELECT id FROM ".MYM_MYSQL_PREFIX.$this->db."s WHERE ".$key." = '".$array[$key]."';";
               $result = Query($this->db." > MyMrecord > Query for primary key", $query);
	       if ($result != NULL) $result = $result['id'];
             } else {
               $result = $dbtable->select("(strtolower(\$".$key.") == '".strtolower($array[$key])."')");  // case insensitive
	       if ($result != NULL) $result = $result[0];
             }
	     
             if ($result != NULL && $result != $this->id) {
               print("<p class='MyMmsg'>Sorry, a $key <strong>".$array[$key]."</strong> already exists in the database.</p>"); // UM
               return false;
             }  
           }

         }
       }
       
       if ($mysql) {                  
         $update = "UPDATE ".MYM_MYSQL_PREFIX.$this->db."s SET $attributions WHERE id = '".$this->id."';"; 
         $id = updateQuery($this->db." > MyMrecord", $update);   
       }
       else {
         $dbtable->modifyElement($this->id, $array);             
       }
      
       return true;       
     }
   }
   
   function MyMrecordOk() {     
   }
   
   // TODO: deleting a not existing element returns OK
   
   // Delete the element
   function MyMdelete($id = UNDEFINED) {
     trace(MYM_ELEMENT_TRACE + 1, $this->db." > MyMdelete");
     
     global $txt, $mysql;

      // Check inputs 
      if ($id != UNDEFINED)
        $this->id = $id;
      else if ($this->id == UNDEFINED)
        return false;
       
     if ($this->db == UNDEFINED)
       return false;
     
     $checkpriv = $this->MyMcheckpriv(_DELETE);

     if ($checkpriv == _NONE) {
       print("<p class='MyMmsg'>".$txt['notallowed']."</p>");
       return;
     }

     $this->MyMread();

     if ($checkpriv == _OWN) {
       $owner = $this->Field(_OWNERFIELD);
       if ($this->$owner != session($this->ownerdb()."_id")) {   // UM
         print("<p class='MyMmsg'>".$txt['notyours']."</p>"); // UM
         return;                                                                                  // UM
       }                                                                                          // UM
     }         

     if ($mysql) {                  
       $query = "DELETE FROM ".MYM_MYSQL_PREFIX.$this->db."s WHERE id = '".$this->id."';";
       $result = deleteQuery($this->db." > MyMdelete", $query);
     } else {
     
       $dbtable = OpenDB($this->db);
     
       ## TODO: WARNING!!! deleting is a dangerous action in txtDB,
       ## because the id is the number of the line in which the element is stored
       
       $result = $dbtable->deleteElement($this->id);
     }
     
     return true;
   } 
   
   function MyMcmd($cmd = UNDEFINED) {
     global $txt;
   
     // set inputs
     switch ($cmd) {
     
       case 'write2':
         $id = post("id");
         if ($id != UNDEFINED) 
           $this->MyMread($id);
         
         $this->MyMsetbypost();
         break;

       case 'modify2':
         $this->MyMsetbypost();
         break;
      
       case 'write':   // for new versions of elements
       case 'modify':
       case 'delete':
         $id = get("id");
         $this->MyMsetId($id);
       break;
     }
     
     // List of actions
     switch ($cmd) {
  
       case 'write':
         $this->MyMprecheck();
         $this->MyMwrite();     
         break;
       
       case 'modify':
         $this->MyMprecheck();
         $this->MyMwrite(false);     
         break;
         
       case 'write2':
         if (!$this->MyMpostcheck()) {
           print "<p class='MyMmsg'>".$txt['formnotvalid']."</p>\n";
           $this->MyMprecheck();      
           $this->MyMwrite(true, false);     
         } else {    
  
           $result = $this->MyMrecord(); // new record, or new version of existing record
          
           if ($result) {
             return true;
           }
           else {
             return false;
           }
         }
         break;
          
       case 'modify2':
         if (!$this->MyMpostcheck()) {
           print "<p class='MyMmsg'>".$txt['formnotvalid']."</p>\n";
           $this->MyMprecheck();      
           $this->MyMwrite(false, false);     
         } else {
              $result = $this->MyMrecord(false); 
          
           if ($result) {
             return true;
           }
           else {
             return false;
           }
         }
         break;
       
       case 'delete':
         if ($this->MyMdelete()) {
           return true;
         }      
         else {
           return false;
         }            
         break;
     }
   }
   
   // Check the element, Javascript client-side check
   // $rules is an array generated by MyMrules
   function MyMprecheck($hiddenfields = array()) { 
     trace(MYM_ELEMENT_TRACE + 1, $this->db." > MyMprecheck");

     $rules = $this->MyMrules();

     if ($rules == NULL) {
       print("<p class='MyMmsg'><span class='Error'><strong>Warning</strong> this structure has no fields.</span></p>");
       return;
     }

     $validation = "\n<script language='javascript' src='".MYM_RELATIVE_PATH."/core/string.js' type='text/javascript'></script>";
     $validation .= "\n<script language='javascript' type='text/javascript'><!--\n"
                   ."function validation() {\n\n";    
     
     $validation .= '  $error = ""; '."\n";
     
     $arraykeys = array_keys($rules);     
     for($i = 0; $i < count($arraykeys); $i++) {
       $key = $arraykeys[$i];

       if (!in_array($key, $hiddenfields))      
         $validation .= $rules[$key]->JavascriptCheck(session($this->ownerdb().'_priv', MYM_NOT_LOGGED_USER_PRIV));
     }

     $validation .= '  if (!$error) return true; '."\n";
     $validation .= '  else {alert($error); return false}'."\n}\n--></script>\n";

     trace(MYM_ELEMENT_TRACE + 1, $this->db." < MyMprecheck end.");
     
     print($validation);
   }

   // Check the element, server-side check 
   // always true for a basic unuseful object :P
   function MyMpostcheck() { 
     global $txt;
     $userdb = $this->ownerdb();
     
     if (session($userdb.'_id') == UNDEFINED) {
       if (!captchaCheck()) { // if CAPTCHAenabled
         print("<p class='MyMmsg'>".$txt['codenotcorrect']."</p>"); 
         return false;                                           
       }
     }
     return true;
   }
   
   // Create Query for MySQL
   // $rules is an array generated by MyMrules
   function MySQLcreatequery() {
     MyMinclude("/core/MyMtype");

     $rules = $this->MyMrules();

     if ($rules == UNDEFINED)
       tracedie($this->db." > MySQLcreatequery  > ERROR: Object rules must be given to perform this action.");

     $query = "CREATE TABLE ".MYM_MYSQL_PREFIX.$this->db."s ("
       ."id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY";
     
     $arraykeys = array_keys($rules);     
     for($i = 0; $i < count($arraykeys); $i++) {
       $key = $arraykeys[$i];
       $query .= ", ".$rules[$key]->name." ".$rules[$key]->MySQLtype();
     }

     $query .=") DEFAULT CHARACTER SET utf8;";
     
     trace_r(MYM_ELEMENT_TRACE + 2, $this->db." > MySQLcreatequery > query :",$query);

     trace(MYM_ELEMENT_TRACE + 1, $this->db." < MySQLcreatequery end.");
     return $query;
   }
   
   // Return an array (associative by names, and by numbers)
   // of the Types of the field definied in the object
   function MyMrules() { 
     trace(MYM_ELEMENT_TRACE + 1, $this->db." > MyMrules");
  
     $object = get_object_vars($this);
     $arraykeys = array_keys($object);

     for($i = 0; $i < count($arraykeys); $i++) {
       $key = $arraykeys[$i];
       if ($key != 'db' && $key != 'id') {
         $rules[$key] = $this->Type($key);
       }       
     }
     
     trace(MYM_ELEMENT_TRACE + 1, $this->db." < MyMrules end.");
     
     if (isset($rules))
       return $rules;
     else 
       return NULL;
   }
   
}
