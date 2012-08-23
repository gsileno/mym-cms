<?php
/*
   File: MyMbuild.php | (c) Giovanni Sileno 2006, 2007
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
   This file contains some basic MySQL function.
*/
   
   function connect() {
     trace(1, " > Connecting to DB...");
   
     if (MYM_MYSQL_PASSWORD == "")
       $connection = mysql_connect(MYM_MYSQL_SERVER, MYM_MYSQL_USER) or tracedie("> ERROR: connection to <em>".MYM_MYSQL_SERVER."</em> not successful.\n");
     else 
       $connection = mysql_connect(MYM_MYSQL_SERVER, MYM_MYSQL_USER, MYM_MYSQL_PASSWORD) or tracedie($caller."> ERROR: connection to <em>".MYM_MYSQL_SERVER."</em> successful.\n");
   
     mysql_select_db(MYM_MYSQL_DB) or tracedie(" > ERROR: connection to <em>".MYM_MYSQL_DB."</em> not successful.<br />\n". mysql_errno() . ": " . mysql_error(). "\n");
     
     return $connection;
   }
   
   function testmysqlconnect() {
     if (MYM_MYSQL_PASSWORD == "") {
       if (!mysql_connect(MYM_MYSQL_SERVER, MYM_MYSQL_USER))
         return false;
     }      
     else {
       if (!mysql_connect(MYM_MYSQL_SERVER, MYM_MYSQL_USER, MYM_MYSQL_PASSWORD))
         return false;
     }     
     
     if (!mysql_select_db(MYM_MYSQL_DB))
       return false;
     return true;
   }
   
   // Query for insertion
   // $caller is the calling function, $insertion is the insertion query
   function insertQuery($caller, $query) {
     global $nqueries;
     /* connection and selection of the database */
     // $connection = connect($caller);
   
     trace(1, "insertQuery > $query");
   
     /* insertion */
     $result = mysql_query($query); $nqueries++;  
     
     trace(1, mysql_errno() . ": " . mysql_error(). "\n" . "<br />");
     if (mysql_errno() > 0) tracedie($caller." > ERROR: Insertion query '$query' echu�e. <br/>". mysql_errno() . ": " . mysql_error(). "\n" . "<br />");
     
     $id = mysql_insert_id();
   
     /* closing connection */
     // mysql_close($connection); 
       
     return $id;
   }
   
   // Query for update
   // $caller is the calling function, $update is the insertion query
   function updateQuery($caller, $query) {
     global $nqueries;
     
     /* connection and selection of the database */
     // $connection = connect($caller);
   
     trace(1, "updateQuery > $query");
   
     /* insertion */
     $result = mysql_query($query); $nqueries++;  
       
     trace(1, mysql_errno() . ": " . mysql_error(). "\n" . "<br />");
     if (mysql_errno() > 0) tracedie($caller." > ERROR: Update query '$query' echu�e. <br/>". mysql_errno() . ": " . mysql_error(). "\n" . "<br />");
   
     /* closing connection */
     // mysql_close($connection); 
   
     return true;
   }
   
   // Query for Delete
   // $caller is the calling function, $delete is the deleting query
   function deleteQuery($caller, $query) {
     global $nqueries;
     
     /* connection and selection of the database */
     // $connection = connect($caller);
   
     trace(1, "deleteQuery > $query");
   
     /* insertion */
     $result = mysql_query($query); $nqueries++;  
       
     trace(1, mysql_errno() . ": " . mysql_error(). "\n" . "<br />");
     if (mysql_errno() > 0) tracedie($caller." > ERROR: Delete query '$query' echu�e. <br/>". mysql_errno() . ": " . mysql_error(). "\n" . "<br />");
   
     /* closing connection */
     // mysql_close($connection); 
   
     return true;
   }
   
   // Generic Univocal Query 
   function Query($caller, $query) {
     global $nqueries;
     
     trace(1, "$caller > Query > $query");
     
     $result = mysql_query($query); $nqueries++;    
     if (mysql_errno() > 0) tracedie($caller." > ERROR: the query '$query' has failed. <br/>". mysql_errno() . ": " . mysql_error(). "\n" . "<br />");
     
     $row = mysql_fetch_array($result, MYSQL_ASSOC);
     
     trace_r(1, "$caller > Result Query > ", $row);
     mysql_free_result($result);
   
     return $row;
   }
   
   // Generic Univocal Query 
   function OneQuery($caller, $query) {
     global $nqueries;
     
     trace(1, "$caller > Query > $query");
     
     $result = mysql_query($query); $nqueries++;    
     if (mysql_errno() > 0) tracedie($caller." > ERROR: the query '$query' has failed. <br/>". mysql_errno() . ": " . mysql_error(). "\n" . "<br />");
     
     $row = mysql_fetch_row($result);
     
     trace_r(1, "$caller > Result Query > ", $row);
     mysql_free_result($result);
   
     return $row[0];
   }
   
   // Generic List Query
   // Return an array and the number of elements.
   function ListQuery($caller, $query) {
     global $nqueries;
   
     trace(1, "$caller > Query > $query");
   
     $result = mysql_query($query); $nqueries++;   
     if (mysql_errno() > 0) tracedie($caller." > ERROR: the query '$query' has failed. <br/>". mysql_errno() . ": " . mysql_error(). "\n" . "<br />");
     
     $list = null;  
     $n = 0;
     while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
       $list[] = $row;
       $n = $n + 1;
     }  
   
     trace_r(1, "$caller > Result Query > ", $list);
     mysql_free_result($result);
   
     return array($list, $n);
   }

   function Keyread($table = UNDEFINED, $key = UNDEFINED, $id = UNDEFINED) {
     trace(1, " > Keyread (table = $table, key = $key, id = $id)");
     
     if ($table == UNDEFINED || $key == UNDEFINED || $id == UNDEFINED)
       return false;
         
     MyMincludestructure($table); 
	 
     if (!property_exists($table, $key))
       return false;
     
     eval('$rule = '.$table.'::Type($key);');
     
     if (method_exists($table, 'ownertable')) {
       eval('$priv = session('.$table.'::ownertable()."_priv", MYM_NOT_LOGGED_USER_PRIV);');
       if ($rule->readpriv != false && $rule->readpriv > $priv)
         return false;     
     }
     
     $query = "SELECT $key FROM ".MYM_MYSQL_PREFIX.$table."s WHERE id = '".$id."'";           
     return OneQuery("Keyread", $query);
   }
   
?>