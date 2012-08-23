<?php

  MyMinclude('core/MyMextention'); 

  // Plugin interface for mym
 
  class Selectbox extends MyMextention {
    
    function htmlhead() {
       $urlpath = ROOT_URI."/".MYM_EXT_PATH."/".strtolower(__CLASS__);
         
       $head = "<script type='text/javascript' src='".$urlpath."/selectbox.js'></script>\n";
       return $head;
    }
  
    function htmlcurrent() {
    }    
  
    function htmltop() {
    }    
    
    function htmlbottom() {
    }    

  }


?>


