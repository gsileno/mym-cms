<?php
trace(1, "structures | article > included");

require_once(MYM_PATH."/core/MyMbuild.php");

class Article extends MyMbuild
{
   // Content
   var $date = UNDEFINED, $uid = UNDEFINED, $lng = UNDEFINED, $unid = UNDEFINED, 
       $title = "", $subtitle = "", $content = "", $type = UNDEFINED;
   
   // Constructor
   function Article() {
     trace(1, "structures | ".__CLASS__." constructor...");
     $this->id = UNDEFINED;
     $this->db = strtolower(__CLASS__);
   }

   function Privilege($action) {
     switch ($action) {
       case _READ      : return 0;
       case _WRITE     : return 2;
       case _DELETE    : return 2;
       case _READOWN   : return 0;
       case _WRITEOWN  : return 1;
       case _DELETEOWN : return 2;
       default: tracedie("structures | ".__CLASS__." | privilege > Sorry, action $action not recognised.");       
     }
   }

   function Field($use) {
     switch ($use) {
       case _DATEFIELD : return 'date';
       case _OWNERFIELD : return 'uid';
       case _DERIVATIONOFFIELD : return 'unid';
       case _LANGUAGEFIELD : return 'lng';
       case _VIEWFIELD : return 'title';
       default: return FALSE;       
     }
   }    
       
   // return the MyM Type object for the given field
   function Type($field) {
     trace(1, "structures | ".__CLASS__." | give type of a field ($field)");
     require_once(MYM_PATH."/core/MyMtype.php");

     switch ($field) {
       case 'date': 
         $type = new MyMtype(_NOW, $field);
         $type->hasPriv(4, 4);         
         break;    
       case 'uid': 
         $type = new MyMtype(_OWNER, $field);
         $type->isIdof('user','login');
         $type->isObligatory();
         $type->hasPriv(4, 4);
         break;
       case 'lng': 
         $type = new MyMtype(_LNG, $field);
         break;                
       case 'unid': 
         $type = new MyMtype(_ID, $field);
         $type->isIdof(__CLASS__, 'title');
         $type->hasPriv(4, 4);
         break;
         
       case 'title': 
         $type = new MyMtype(_TEXT, $field);
         $type->isObligatory();
         break;
       case 'subtitle': 
         $type = new MyMtype(_TEXT, $field);
         break;         
       case 'content': 
         $type = new MyMtype(_MYMTEXT, $field);
         $type->isObligatory();
         break;  
       case 'type': 
         $type = new MyMtype(_FLAG, $field);
         $type->isIndexof(array('Draft', 'Publish'));         
         $type->isObligatory();
         break;
         
       default: tracedie("structures | ".__CLASS__." > Sorry, field $field not recognised.");       
     }
     return $type;
   }
}

?>