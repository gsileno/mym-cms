<?php

trace(1, "structures | preferences > included");

require_once(MYM_PATH."/core/MyMbuild.php");

class __Preferences extends MyMbuild
{
   // Preferences
   var $date = UNDEFINED, $title = "", $subtitle = "", $content = "", $type = UNDEFINED;

   // Constructor
   function __Preferences() {
     trace(1, "structures | ".__CLASS__." constructor...");
     $this->id = UNDEFINED;
     $this->db = strtolower(__CLASS__);
   }

   // this element is a static array recorded in a file
   function staticarray() {
     return true;
   }

   function Privilege($action) {
     switch ($action) {
       case _READ      : return 0;
       case _WRITE     : return 2;
       case _DELETE    : return 3;
       case _READOWN   : return 0;
       case _WRITEOWN  : return 2;
       case _DELETEOWN : return 2;
       default: tracedie("structures | ".__CLASS__." | privilege > Sorry, action $action not recognised.");
     }
   }

   function Field($use) {
     switch ($use) {
       case _DATEFIELD : return 'date';
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
         $type->isIndexof(array('Draft', 'Public'));
         $type->isObligatory();
         break;

       default: tracedie("structures | ".__CLASS__." > Sorry, field $field not recognised.");
     }
     return $type;
   }
}

?>
