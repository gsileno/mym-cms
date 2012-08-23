<?php

// Distributed as part of "MyM - avant CMS"
// -----------------------------------------------------------------
// You should have received a copy of the GNU Lesser General Public License
// along with this program.  If not, see <http://www.gnu.org/licenses/>.
// -----------------------------------------------------------------   
// (c) Giovanni Sileno 2006, 2010 - giovanni.sileno@mexpro.it

// --------------------------------------------------
// basic read GET / POST / FILES variable functions
// --------------------------------------------------

// if there is a variable definied name return it
//    else return default
function getpost($name, $default = UNDEFINED) {
  if (isset($_GET[$name]))           
    return $_GET[$name];
  elseif (isset($_POST[$name])) 
    return $_POST[$name];
  else
    return $default;
}

function get($name, $default = UNDEFINED) {
  if (isset($_GET[$name]))           
    return $_GET[$name];
  else
    return $default;
}

function post($name, $default = UNDEFINED) {
  if (isset($_POST[$name])) 
    return $_POST[$name];
  else
    return $default;
}

function postcheckbox($name, $set) {
  
  $selected = array();
  for ($i = 0; $i < count($set); $i++) {
    $optioni = post("$name$i");
    if ($optioni != UNDEFINED) 
      if (in_array($optioni, $set)) 
        array_push($selected, $optioni);
  }
  
  return $selected;
}

// return the session variable name
function session($name, $default = UNDEFINED) {
  if (isset($_SESSION[$name])) 
    if (decryptate($_SESSION[$name]) == UNDEFINED)
      return $default;
    else
      return decryptate($_SESSION[$name]);
  else
    return $default;
}

// return the existence of a session variable name
function issession($name) {
  return (isset($_SESSION[$name]) && decryptate($_SESSION[$name]) != UNDEFINED);
}

// return the field value of the session variable name
function sessionarray($field, $name) {
  return decryptate($_SESSION[$name][$field]);
}

function files($name) {
  if (isset($_FILES[$name])) 
    return $_FILES[$name];
  else
    return NULL;
}

function getpostsession($name, $session, $default = UNDEFINED) {
  if (isset($_SESSION[$session][$name]))
    $default = decryptate($_SESSION[$session][$name]);
    
  if (isset($_GET[$name]))           
    return $_GET[$name];
  elseif (isset($_POST[$name])) 
    return $_POST[$name];
  else
    return $default;
}

// --------------------------------------------------
//  basic writing session variable functions
// --------------------------------------------------

// if $_SESSION['$name'] is not set or if $value != UNDEFINED 
//   then $_SESSION['$name'] = $value
function wsession($name, $value = UNDEFINED, $force = false) {
  if (isset($_SESSION[$name]) && $force == false) 
    if ($value == UNDEFINED)
      return;

  $_SESSION[$name] = cryptate($value);
}

// if $_SESSION['$name']['$field'] is not set or if $value != UNDEFINED 
//   then $_SESSION['$name']['$field'] = $value
function wsessionarray($field, $name, $value = UNDEFINED) {
  if (isset($_SESSION[$name][$field])) 
    if ($value == UNDEFINED)
      return;

  $_SESSION[$name][$field] = cryptate($value);
}