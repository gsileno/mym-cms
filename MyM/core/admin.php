<?php
/*
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
   This file contains the login functions for MyM.
*/

if (defined('MYM_MYSQL')) require_once(MYM_PATH."/core/baseMySQL.php");
else require_once(MYM_PATH."/core/baseTxtDB.php");
  
   // -------------------------------------------
   //  User Management
   // -------------------------------------------
   // TO BE CORRECT : to be applied only on objects with name, pass

   // MyMchecklogin
   // check a login (username, password)
   // if not given it check the session variables.
   function MyMchecklogin($userdb = MYM_USER_DB, $username = UNDEFINED, $password = UNDEFINED) {
     global $txt;
     
     if ($userdb == UNDEFINED) return false;
     
     // if not definied inputs are given by session variables
     if ($username == UNDEFINED) $username = session($userdb.'_name');
     if ($password == UNDEFINED) $password = decryptate(session($userdb.'_pass'));

     // if input are still not definied return
     if ($username == UNDEFINED || $password == UNDEFINED) return false;
     
     trace(1, "MyMchecklogin: username $username, password $password");
  
     if (MYM_VALIDATION == '') {
     
       if (defined('MYM_MYSQL')) { // FIXME: Implement
         $query = "SELECT id, password FROM ".MYM_MYSQL_PREFIX.$userdb."s WHERE login = '".$username."';";
         $user = Query($userdb." > MyMchecklogin", $query);  
         if ($user != NULL) 
           list($id, $password2) = array_values($user);
         else {
           print("<p>".$txt['notknownuser']."</p>");
           return false;
         }
       }
       else {
         $usertable = openDB($userdb);
         
         $listid = $usertable->select("\$login == '$username'");
         if ($listid != NULL) {
           $id = $listid[0];
           $user = $usertable->readElement($id);
           $password2 = $user['password'];     
         }
         else {
           print("<p>".$txt['notknownuser']."</p>");
           return false;
         }         
       }                
       
     } else {
       
       if (defined('MYM_MYSQL')) { // FIXME: Implement
       
         $query = "SELECT id, password, val FROM ".MYM_MYSQL_PREFIX.$userdb."s WHERE login = '".$username."';";
         $user = Query($userdb." > MyMchecklogin", $query);
         if ($user != NULL) 
           list($id, $password2, $val) = array_values($user); // TO BE CORRECT!!
         else {
           print("<p>".$txt['notknownuser']."</p>");
           return false;
         }
         
       }
       else {
         $usertable = OpenDB($userdb);
         
         $listid = $usertable->select("\$login == '$username'");
         if ($listid != NULL) {
           $id = $listid[0];
           $user = $usertable->readElement($id);
           $password2 = $user['password'];
           $val = $user['val'];
         }
         else {
           print("<p>".$txt['notknownuser']."</p>");
           return false;
         }         
       }
    
       if ($val == false) {
         print("<p>".$txt['notvalidateduser']."</p>");
         return false;
       }
     }
  
     if ($password == $password2)
        return $id;

     return false;
   }

   // MyMlogin()
   // Login form 
   function MyMlogin($userdb = MYM_USER_DB, $fileaction = "index.php") {
     if ($userdb == UNDEFINED) return false;
     
     print("<div id='table2col'>");
     print("<form action='$fileaction' method='POST'>\n");
     print("  <input type='hidden' name='a' value='login2'>\n");
     print("  <input type='hidden' name='o' value='".$userdb."'>\n"); // TO BE CORRECT to user multiple user groups
     print("  <table>\n");
     print("    <tr><td class='left'>Username</td><td class='right'><input type='text' name='username' value='' /> </td></tr>\n");
     print("    <tr><td class='left'>Password</td><td class='right'><input type='password' name='password' value='' /> </td></tr>\n");
     print("    <tr><td class='left'>&nbsp;</td> <td class='rightbutton'><input class='firstbutton' type='submit' value='Login' /></tr>\n");
     print("  </table>\n");
     print("</form>\n");
     print("</div>");
   }

   // MyMloginOk()
   // execute a login
   // record to session the user id, login data
   function MyMloginOk($userdb = UNDEFINED, $username = UNDEFINED, $password = UNDEFINED) {
     
     if ($userdb == UNDEFINED || $username == UNDEFINED || $password == UNDEFINED) {
       $userdb = MYM_USER_DB;
                           
       if (!issession($userdb."_name") || !issession($userdb."_pass"))
         return false;
       else {
         $username = session($userdb."_name");
         $password = decryptate(session($userdb."_pass"));
       }
     }

     if (defined('MYM_MYSQL')) { // FIXME: to be implement
       $query = "SELECT id, priv, lng, password FROM ".MYM_MYSQL_PREFIX.$userdb."s WHERE login = '".$username."';";
       list($id, $priv, $lng, $password) = array_values(Query($userdb." > MyMloginOk", $query));
     } else {
       $usertable = OpenDB($userdb);
       $listid = $usertable->select("\$login == '$username'");
       $id = $listid[0];
       $user = $usertable->readElement($id);
       $lng = $user['lng'];
       $priv = $user['priv'];
       $password = $user['password'];
     }
  
     wsession($userdb."_priv", $priv);               // privilege
     wsession($userdb."_name", $username);           // username
     wsession($userdb."_pass", cryptate($password)); // password
     wsession($userdb."_id", $id);                   // userid  
     
     wsession("lng", $lng);
    
     return true;
   }

   // MyMlogout()
   // execute a logout
   // itinialize to undefined the login data 
   function MyMlogout($userdb = UNDEFINED) {
     if ($userdb == UNDEFINED) return false;
     
     $arraykeys = array_keys($_SESSION);
     for ($i = 0; $i < count($arraykeys); $i++) 
       unset($_SESSION[$arraykeys[$i]]);
     
   }   
   
   // -------------------------------------------
   //  Validation Management
   // -------------------------------------------
   // TO BE CORRECT : to be applied only on objects with email, val, valcode

   // 
   function MyMvalidate($userdb = UNDEFINED, $fileaction = "index.php") {   
     if ($userdb == UNDEFINED) return false;
       
     print("<div id='table2col'>");
     print("<form action='$fileaction' method='post'>\n\n");
     print("  <input type='hidden' name='a' value='validate2' />\n");
     print("  <input type='hidden' name='o' value='".$userdb."' />\n");
     print("  <table>\n");
     print("    <tr><td class='left'>Email</td><td class='right'><input type='text' name='email' value='' /></td></tr>\n");
     print("    <tr><td class='left'>Validation Code</td><td class='right'><input type='valcode' name='valcode' size=10 value='' /></td></tr>\n");
     print("    <tr><td class='left'>&nbsp;</td> <td class='rightbutton'><input class='firstbutton' type='submit' value='Enter' /></td></tr>\n");
     print("  </table>\n");
     print("</form>\n");
     print("</div>");     
   }   
   
   // MyMcheckvalidate
   function MyMcheckvalidate($userdb = UNDEFINED, $emailvalue = UNDEFINED, $valcode = UNDEFINED)
   {
     trace(3, $userdb." > MyMvalidate : email $emailvalue, valcode $valcode");     

     // if input are still not definied return
     if ($emailvalue == UNDEFINED || $valcode == UNDEFINED) return false;
     
     // check if it is already validated.
     if (defined('MYM_MYSQL')) {
       
       $query = "SELECT id, valcode, val FROM ".MYM_MYSQL_PREFIX.$userdb."s WHERE email = '".$emailvalue."';";
       $user = Query($userdb." > MyMvalidate >", $query); 
       if ($user != NULL) 
         list($id, $valcode2, $val)  = array_values($user); // TO BE CORRECT!!
       else {
         // print("<p>Sorry, this user is unknown.</p>");
         return false;
       }
     }
     else {
       $usertable = OpenDB($userdb);
        
       $listid = $usertable->select("\$email == '$emailvalue'");
       if ($listid != NULL) {
         $id = $listid[0];
         $user = $usertable->readElement($id);
         $valcode2 = strtoupper($user['valcode']);
         $val = $user['val'];
       }
       else {
         // print("<p>Sorry, this user is unknown.</p>");
         return false;
       }         
     }
     
  
     if ($val == true) { // validation already done
       print("<p>This validation has been already done.</p>"); 
       return true;
       
     } else if ($valcode2 == $valcode) { // correct validation              
       if (defined('MYM_MYSQL')) {
         $update = "UPDATE ".$userdb."s SET val = TRUE WHERE id = '".$id."';";
         $id = updateQuery($userdb." > MyMrecord", $update);   
       }
       else {
         $user['val'] = 1; // TRUE as integer
         $usertable->modifyElement($id, $user);                    
       }              
       return true;
     } else // validation code not right
       return false;
     
     trace(1, $userdb." < MyMvalidate end.");
     return true;
   } 
   
   // TO BE CORRECT: only for email defined users
   function recordUserOk()
   {
     print ("<p>Your profile has been recorded!! </p>");
     if (mail(
       $this->email, 
       "Welcome to MyM!", 
       "Hello,\n\n  we have successfully recorded your profile.\n\n"
      ."-------------------------------------------\n"
      ."Login: ".$this->name."\n"
      ."Password: ".$this->pass."\n"
      ."Validation Code: ".$this->valcode."\n"
      ."-------------------------------------------\n\n"
      ."Please validate your profile following this link: \n"
      .MYM_URI."/index.php?a=validuser&login=".$this->name."&valcode=".$this->pass."\n\n"
      ."or typing manually your Login and the Validation Code at the page: \n"
      .MYM_URI."/index.php?a=validuser\n\n"
      ."Thank you for your interest, \n"
      ."  MyM Staff\n\n",
       "From: ".MYM_ADMIN_EMAIL."\r\n"))  
       print("<p>An email has been sent to your email, follow the instructions written in it to validate your account. </p>");
     else 
       print("<p>Sorry, an internal error has stopped the validation process. Please <a href='mailto:".MYM_ADMIN_EMAIL."'>contact us</a>, writing your login <strong>".$this->name."</strong> to recover it.</p>");
   }


?>
