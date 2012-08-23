<?php

  MyMinclude('core/MyMextention'); 

  // Plugin interface for mym
 
  class Jscalendar extends MyMextention {
    
    function htmlhead() {
       $urlpath = ROOT_URI."/".MYM_EXT_PATH."/".strtolower(__CLASS__);
  
       $head = "<link rel='stylesheet' type='text/css' media='all' href='".$urlpath."/calendar-brown.css' title='summer' />\n";
       $head .= "<script type='text/javascript' src='".$urlpath."/calendar.js'></script>\n";
       $head .= "<script type='text/javascript' src='".$urlpath."/lang/calendar-".session('lng').".js'></script>\n";
       $head .= "<script type='text/javascript' src='".$urlpath."/runcalendar.js'></script>\n";
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


