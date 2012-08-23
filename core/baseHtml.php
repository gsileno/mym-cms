<?php

// Distributed as part of "MyM - avant CMS"
// -----------------------------------------------------------------
// You should have received a copy of the GNU Lesser General Public License
// along with this program.  If not, see <http://www.gnu.org/licenses/>.
// -----------------------------------------------------------------   
// (c) Giovanni Sileno 2006, 2010 - giovanni.sileno@mexpro.it

// -------------------------------------------------------------
//  create links with index.php and the GET variables
// -------------------------------------------------------------

  // command and object link 
  function makelink($string = "&nbsp;", $a = UNDEFINED, $o = UNDEFINED, $id = UNDEFINED, $page = "index.php", $active = false) { 
    $link = "";
    $link .= "<a ";
    if ($active) $link .= "class='active' ";
    $link .= "href='$page";
  
    if ($a != "" && $a != UNDEFINED) {
       $link .= "?a=".$a;
       if ($o != UNDEFINED)
         $link .= "&o=".$o;
    }
    else if ($o != UNDEFINED && $o != "")
      $link .= "?o=".$o;
  
    if ($id != UNDEFINED)
      $link .= "&id=".$id;
  	
    $link .= "'>$string</a>\n";
  
    return $link;
  }
   
  // Build a list of options 
  // name is the name (textual) of the option
  // value is the selected value
  // set is the list of options availables
  // values is the list of values for each option
  function InputSelectOption($name, $value, $set = UNDEFINED, $values = UNDEFINED, $readonly = false, $other = "", $undefined = true, $empty = false) {
    global $txt;
      
    // TODO.. set deve partire sempre da 0...  
      
    $form = "\n";
    $form ="    <select id='".$name."' name='".$name."'";
    if ($readonly)
      $form .= " class='readonly' readonly";
    if ($other != "") 
      $form .= " $other ";
    $form .= ">\n";
      
    if (($set == NULL || $set == UNDEFINED) && !$empty) 
      $form .= "      <option value='".UNDEFINED."' selected>{$txt['noneavailable']}</option>\n";
    else { 
      if ($value == UNDEFINED && $undefined && !$empty) $form .="      <option value='".UNDEFINED."' selected>{$txt['choose']}</option>\n";    
      for ($i=0; $i<count($set); $i++) {
        if ($values != UNDEFINED) 
          $ivalue = $values[$i];
        else 
          $ivalue = $i;
        $form .="      <option value='$ivalue' ";
        if ($value == $ivalue) $form .="selected"; 
        $form .=">".$set[$i]."</option>\n";
      }
    }
      
    $form .= "    </select>\n  ";
      
    return $form;
  }
  
  
  function InputCheckboxOption($name, $selected = array(), $set = UNDEFINED, $values = UNDEFINED, $readonly = false, $other = "", $undefined = true, $empty = false) {
    global $txt;
      
    // TODO.. set deve partire sempre da 0...  
      
    $form = '';
    
    if ($selected == UNDEFINED)
      $selected = array();
    
    if (($set == NULL || $set == UNDEFINED) && !$empty) 
      $form .= "      No $name available\n";
    else { 
      for ($i=0; $i<count($set); $i++) {
        if ($values != UNDEFINED) 
          $ivalue = $values[$i];
        else 
          $ivalue = $i;
        $form .= "<div class='checkboxinput'><input type='checkbox'";
        if ($other != "") $form .= " $other";
        $form .=" name='$name$i' value='$ivalue'";
        if (in_array($ivalue, $selected)) $form .=" checked";
        if (is_array($readonly)) if (in_array($ivalue, $readonly)) $form .=" disabled='disabled'";
        $form .=" /><span class='checkboxoption'>$set[$i]</span></div>\n";
      }
    }
      
    return $form;
  }
  
  // Build a list of radio options 
  // name is the name (textual) of the option
  // value is the selected value
  // set is the list of options availables
  // values is the list of values for each option
  function InputRadioOption($name, $value, $set = UNDEFINED, $values = UNDEFINED, $readonly = array(), $other = "", $undefined = true, $empty = false) {
  
    $form = '';
    
    if (($set == NULL || $set == UNDEFINED) && !$empty) 
      $form .= "      No $name available\n";
    else { 
      for ($i=0; $i<count($set); $i++) {
        if ($values != UNDEFINED) 
          $ivalue = $values[$i];
        else 
          $ivalue = $i;
        $form .= "<div class='radioinput'><input type='radio'";
        if ($other != "") $form .= " $other";
        $form .=" name='$name' value='$ivalue'";
        if ($value == $ivalue) $form .=" checked";
        if (is_array($readonly)) if (in_array($ivalue, $readonly)) $form .=" disabled='disabled'";
        $form .=" /><span class='radiooption'>$set[$i]</span></div>\n";
      }
    }
      
    return $form;
  }

  /**
   * HTML functions
   */
  function htmlhead($lng = "en", $charset = "utf-8") { 
    print("<!DOCTYPE html PUBLIC '-//W3C//DTD XHTML 1.0 Transitional//EN' 'http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd' />\n");
    print("<html xmlns='http://www.w3.org/1999/xhtml' xml:lang='$lng' lang='$lng'>\n");
    print("<head>\n");
    print("<meta http-equiv='content-type' content='text/html; charset=$charset' />\n");
  }
    
  function title($title = "") { 
    print('<title>'.$title.'</title>'."\n");
  }

  function js($file = NULL) { 
    if ($file!=NULL) {
      print("<script type='text/javascript' src='$file'></script>\n");
    } 
  } 

  function css($file = NULL, $condition = NULL) {
    if ($file!=NULL) {
      if ($condition != NULL) print ("\n<!--[if $condition]>");
      print("<link href='$file' rel='Stylesheet' type='text/css' />");
      if ($condition != NULL) print ("<![endif]-->\n");
    } 
  } 

  function favicon($file = NULL) {
    if ($file!=NULL) {
      print("<link rel='icon' href='$file' type='image/x-icon' />\n");
      print("<link rel='shortcut icon' href='$file' type='image/x-icon' />\n");
    }   
  }
  
  function description($description = NULL) {
    if ($description != NULL)
      print('<meta name="description" content="'.$description.'" />'."\n");
  }
  
  function keywords($keywords = NULL) {
    if ($keywords != NULL)
      print('<meta name="keywords" content="'.$keywords.'" />'."\n");
  } 
  
  function htmlbody($class = NULL) {
    print("</head>\n<body");
    if ($class != NULL) print(" class='$class'");
    print(">\n");
  }   
   
  function dynmenucssjs() { 
    print("<script type='text/javascript' src='./js/dynMenu.js'></script>\n");
    print("<script type='text/javascript' src='./js/browserdetect.js'></script>\n");
    print("<style type='text/css'>@import './css/menu.css';</style>\n");
  } 
  
  function calendarjs($path = false) {  
    if (!$path) $path = MYM_PATH."/ext/jscalendar/";
      
    print("<link rel='stylesheet' type='text/css' media='all' href='".$path."calendar-brown.css' title='summer' />\n");
    print("<script type='text/javascript' src='".$path."calendar.js'></script>\n");
    print("<script type='text/javascript' src='".$path."calendar-".session('lng').".js'></script>\n");
    print("<script type='text/javascript' src='".$path."runcalendar.js'></script>\n");
  } 
  
  function playerjs($path = false) {
    if (!$path) $path = MYM_RELATIVE_PATH."/ext/flashflvplayer/";
    print("<script type='text/javascript' src='".$path."ufo.js'></script>\n");  
  } 
 
  function rundynmenujs() {
    print("<script type='text/javascript'>\n");
    print("initMenu();\n");
    print("</script>\n");
  } 

  function htmlend() {
    print("</body>\n</html>\n");
    if (defined('MYM_CACHE')) if (MYM_CACHE) CacheOff(MYM_CACHE_REALPATH, session('lng').".cache");
  }

  function languages_flags($page = "index.html", $optionalgetvariables = "") {
    global $set_lngcode, $set_lng;

    for ($i=0; $i<count($set_lngcode); $i++) {
      print("  <a href='$page?lng=".$set_lngcode[$i]);
      if ($optionalgetvariables != "")
        print("&amp;".$optionalgetvariables);
      print("'");

      if(session('lng') == $set_lngcode[$i]) print(" class='current'");
      print("><img src='".MYM_RELATIVE_PATH."/img/ext_".$set_lngcode[$i].".png' alt='".$set_lng[$i]."' /></a>\n");
    }
  }

  function languages_names($page = "index.html", $optionalgetvariables = "") {
    global $set_lngcode, $set_lng;
    
    for ($i=0; $i<count($set_lngcode); $i++) {
      print("  <a href='$page?lng=".$set_lngcode[$i]);
      if ($optionalgetvariables != "")
        print("&amp;".$optionalgetvariables);

      /* if ($action == UNDEFINED || $o == UNDEFINED) print("s=$s'");
      else if ($id == UNDEFINED) print("a=$action&amp;o=$o'");
      else print("a=$action&amp;o=$o&amp;id=$id'"); */

      if(session('lng') == $set_lngcode[$i]) print(" class='current'");
        print(">".$set_lng[$i]."'</a>\n");
    }
  }
  
