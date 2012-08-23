<?php
trace(1, "structures | user > included");

require_once(MYM_PATH."/core/MyMbuild.php");

class User extends MyMbuild
{
   // Content
   var $login = "", $password = "",
       $email = "", 
       $realname = "", $surname = "", $address = "", $profile = "",
       $lng = UNDEFINED,
       $priv = UNDEFINED, $date = UNDEFINED;
       
       /* $valcode = UNDEFINED,
       $val = false; */

   // Constructor
   function User() {
     trace(1, "structures | user constructor...");     
     $this->id = UNDEFINED;
     $this->db = strtolower(__CLASS__);
   }
   
   // return the Privilege
   function Privilege($action) {
     switch ($action) {
       case _READ      : return 2;
       case _WRITE     : return 2;
       case _DELETE    : return 2;
       case _READOWN   : return 1;
       case _WRITEOWN  : return 0;
       case _DELETEOWN : return 2;
       default: tracedie("structures | ".__CLASS__." | privilege > Sorry, action $action not recognised.");       
     }
   }

   function Field($use) {
     switch ($use) {
       case _OWNERFIELD : return 'id';
       default: return FALSE;       
     }
   }    
         
   // return the MyM Type object for the given field
   function Type($field) {
     trace(1, "structures | ".__CLASS__." | give type of a field ($field)");
     require_once(MYM_PATH."/core/MyMtype.php");

     switch ($field) {
         
       // TO BE CORRECT: Obligatory to enable User Management
       case 'priv': 
         $type = new MyMtype(_PRIV, $field);
         $type->hasPriv(3, 3);
         break;

       case 'login': 
         $type = new MyMtype(_TEXT, $field);
         $type->isObligatory(); 
         $type->isPrimary();
         break;
         
       case 'password':        
         $type = new MyMtype(_TEXT, $field);
         $type->isObligatory(); 
         $type->isDoubled();
         $type->isStarred();
         break;
         
       case 'profile':  
         $type = new MyMtype(_LONGTEXT, $field);
         break;

       case 'address': 
       case 'realname': 
       case 'surname': 
         $type = new MyMtype(_TEXT, $field);
         break;

       case 'email': 
         $type = new MyMtype(_EMAIL, $field);
         $type->isObligatory(); // TO BE CORRECT: Obligatory only to enable Validation
         $type->isDoubled();
         break;
         
       case 'date':
         $type = new MyMtype(_NOW, $field);
         $type->hasPriv(3, 3);         
         break;
       case 'lng': 
         $type = new MyMtype(_LNG, $field);
         $type->isObligatory();
         break;

       // TO BE CORRECT: Obligatory only to enable Validation
       case 'valcode': 
         $type = new MyMtype(_TEXT, $field);
         $type->collate(_CS);
         $type->hasPriv(3, 3);         
         break;
         
       case 'val': 
         $type = new MyMtype(_NUMBER, $field);
         $type->sign(_UNSIGNED); 
         $type->hasPriv(3, 3);         
         break;  

       default: tracedie("structures | ".__CLASS__." > Sorry, field $field not recognised.");       
     }
     return $type;
   }
}

?>