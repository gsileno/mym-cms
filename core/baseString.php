<?php

// Distributed as part of "MyM - avant CMS"
// -----------------------------------------------------------------
// You should have received a copy of the GNU Lesser General Public License
// along with this program.  If not, see <http://www.gnu.org/licenses/>.
// -----------------------------------------------------------------   
// (c) Giovanni Sileno 2006, 2010 - giovanni.sileno@mexpro.it

 function remove_spaces($string) {
    $string = stripslashes($string);
    $pattern = array("/^\s+/", "/\s{2,}/", "/\s+\$/");
    $replace = array("", " ", "");
    $string = preg_replace($pattern, $replace, $string);
    return $string;
  }

  function remove_doublebr($string) {
    $string = remove_spaces($string);
    $string = stripslashes($string);
    $pattern = array("/^(<br[\/]?>)+\s*/", "/(<br[\/]?>)+\$/", "/((<br[\/]?>)+\s*){2,}/");
    $replace = array("", "", "</p><p>");
    $string = preg_replace($pattern, $replace, $string);
    $string = "<p>".$string."</p>";
    return $string;
  }

  function remove_tags($string) {
    $pattern = array('/<.*>/U');
    $replace = array("");
    $string = preg_replace($pattern, $replace, $string);
    return $string;
  }
  
  function remove_doublenl($string) {
    $string = stripslashes($string);
    $pattern = array('/^(\n)+\s*/', '/(\n)+\$/', '/((\n)+\s*){2,}/');
    $replace = array("", "", "\n\n");
    $string = preg_replace($pattern, $replace, $string);
    $string = $string."\n";
    return $string;
  }
  
  // reduce a text content to a max number of char, splitting if necessary at [c] tag
  function reduce($string, $maxchar = 200) {
    $splitted = preg_split("/\[c[\/]*\]/", $string);
    $string = substr($splitted[0], 0, $maxchar);

    // replace the last word with ...
    if (strlen($string) > $maxchar - 1) 
      $string = substr($string, 0, strrpos($string, " "))." &hellip;";
      
    return $string;
  }

  // split a text content following the [c/] tag
  function split_paragraphs($string, $maxchar = 200) {
    $splitted = preg_split("/\[c[\/]*\]/", $string, -1, PREG_SPLIT_NO_EMPTY);
    
    return $splitted;
  }

  function capitalize($string) {
     $array = explode(' ', $string);
     
     for ($i = 0; $i < count($array); $i++) {
       $string = $array[$i];
       $string = trim($string);
       $string = strtolower($string);
       $string[0] = strtoupper($string[0]);
       $array[$i] = $string;
     }
     
     $string = implode(' ', $array);
     return $string;
  }
  
  // ----------------------------------------
  //  generate a random alfanumerical string
  // ----------------------------------------
  function generaterandomstring($length = 10) {
    $keycode = "Tanto va la gatta al lardo che ci lascia lo zampino";
    $length = strlen($keycode);
       
    $string = "";
    for ($i = 0; $i < $length; $i++) {
      $key = strtoupper($keycode[rand(0, $length)]);
        if ($key == " ")
          $key = rand(0, 9);
        $string .= $key;
      }
    return string;
  } 
  
  // ----------------------------------------
  //  crypting functions // TO BE DONE
  // ----------------------------------------
  
  function cryptate($string) {
     if (is_array($string)) {
	   if (function_exists("json_encode")) // ONLY in PHP > 5.2.0
         return base64_encode(addslashes(implode("#TNT-ACME#", $string)));
       else 
         return base64_encode(addslashes(json_encode($string)));
     }               
     else 
       return base64_encode($string);
  }
       
  function decryptate($string) {
     $string = base64_decode($string);
     if (function_exists("json_decode"))  // ONLY in PHP > 5.2.0
       $array = json_decode(stripslashes($string), true);  
     else 
       $array = explode("#TNT-ACME#", stripslashes($string));
     
     if (is_array($array)) 
       return $array;
     else 
       return $string;    
  }
