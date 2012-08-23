<?php
// ::::::::::::::::: 
// :::::: MYM :::::: 
// ::::::::::::::::: 
// a v a n t   C M S
//
// developed by mexpro  
// jan <dot> hus <at> junkhead <dot> it
// --------------------------------------
// ./structure/sources/__audio.php
// --------------------------------------

$structname = "audio";
trace(1, "structures | ".__CLASS__." > included");
require_once(MYM_PATH."/core/MyMbuild.php");

class __Audio extends MyMbuild
{
   // Content
   var $file = UNDEFINED; 

   // Constructor
   function __Audio() {
     trace(1, "structures | ".__CLASS__." constructor...");
     $this->id = UNDEFINED;
     $this->db = strtolower(__CLASS__);
   }

   function Field($use) {
     switch ($use) {
       case _VIEWFIELD : return 'file';
       default: return false;       
     }
   }    
      
   // return the Privilege
   function Privilege($action) {
     switch ($action) {
       case _READ      : return 0;
       case _WRITE     : return 0;
       case _DELETE    : return 0;
       case _READOWN   : return 0;
       case _WRITEOWN  : return 0;
       case _DELETEOWN : return 0;
       default: tracedie("structures | ".__CLASS__." > Sorry, action $action not recognised.");       
     }
   }

   // return the MyM Type object for the given field
   function Type($field) {
     trace(1, "structures | ".__CLASS__." | give type of a field ($field)");
     require_once(MYM_PATH."/core/MyMtype.php");

     switch ($field) {
       case 'file': 
         $type = new MyMtype(_AUDIO, $field);        
         $type->isObligatory();         
         break;         
        
       default: tracedie("structures | ".__CLASS__." > Sorry, field $field not recognised.");       
     }
     return $type;
   }
}

?>