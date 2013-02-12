<?php

// Distributed as part of "MyM - avant CMS"
// -----------------------------------------------------------------
// You should have received a copy of the GNU Lesser General Public License
// along with this program.  If not, see <http://www.gnu.org/licenses/>.
// -----------------------------------------------------------------   
 
  require_once("base.php"); 
  
  ////////////////////////////////////////
  // Default languages for MyM messages
  ///////////////////////////////////////
  if (!isset($lngs)) {
    $lngs = array("en", "it");
  }
  for ($i = 0; $i < count($lngs); $i++) {
    $set_lngcode[] = $lngs[$i];
    $set_lng[] = lngcode2lng($lngs[$i]);
  }
  
  function lngcode2lng($lngcode) {
    switch ($lngcode) {
      case 'en': return "English";
      case 'it': return "Italiano";
      case 'fr': return "Français";
      case 'es': return "Español";
      default: return "Unknown";
    }
  }
  ///////////////////////////////////////
  
  function MyMsetuppath($path = NULL) {
  
    if ($path == NULL)
      show_error('Please define the relative path to MyM from your script directory.');
  	
    if (!defined('APP_RELATIVE_PATH'))
    define('APP_RELATIVE_PATH', $path);

    // TODO: clean this paths in error messages

    if (!defined('MYM_PATH')) define('MYM_PATH', realpath(dirname(__FILE__).'/../.'));
    
    define('ROOT_PATH', realpath(MYM_PATH.'/'.ROOT_RELATIVE_PATH));
    if (!is_dir(ROOT_PATH)) show_error('Not valid root relative path: '.ROOT_RELATIVE_PATH.'.');    
    
    define('APP_PATH', realpath(ROOT_PATH.'/'.APP_RELATIVE_PATH));
    if (!is_dir(APP_PATH)) show_error('Not valid application relative path: '.APP_RELATIVE_PATH.'.');

    // defined related to APP directory

    define('MYM_STRUCTURES_REALPATH', realpath(APP_PATH.'/'.MYM_STRUCTURES_PATH));
    if (!is_dir(MYM_STRUCTURES_REALPATH)) show_error('Not valid structures\' path: '.APP_PATH.'/'.MYM_STRUCTURES_PATH.'.');

    if (defined('MYM_MODULES_PATH')) {
      define('MYM_MODULES_REALPATH', realpath(APP_PATH.'/'.MYM_MODULES_PATH));
      if (!is_dir(MYM_MODULES_REALPATH)) show_error('Not valid modules\' path: '.APP_PATH.'/'.MYM_MODULES_PATH.'.');
    }
    
    if (defined('MYM_LANGUAGES')) {
      define('MYM_LANGUAGES_REALPATH', realpath(APP_PATH.'/'.MYM_LANGUAGES_PATH));
      if (!is_dir(MYM_LANGUAGES_REALPATH)) show_error('Not valid language path: '.APP_PATH.'/'.MYM_LANGUAGES_PATH.'.');
    }    

    // defined related to ROOT directory

    if (defined('MYM_UPLOAD_PATH')) {
      define('MYM_UPLOAD_REALPATH', realpath(ROOT_PATH.'/'.MYM_UPLOAD_PATH));
      if (!is_dir(MYM_UPLOAD_REALPATH)) show_error('Not valid upload path: '.ROOT_PATH.'/'.MYM_UPLOAD_PATH.'.');
    }

    if (defined('MYM_CACHE_PATH')) {
       define('MYM_CACHE_REALPATH', realpath(ROOT_PATH.'/'.MYM_CACHE_PATH));
       if (defined('MYM_CACHE')) if (!is_dir(MYM_CACHE_REALPATH)) show_error('Not valid cache path: '.ROOT_PATH.'/'.MYM_CACHE_PATH.'.');
    }

    if (defined('MYM_LABELS_PATH')) {
      define('MYM_LABELS_REALPATH', realpath(ROOT_PATH.'/'.MYM_LABELS_PATH));
      if (!is_dir(MYM_LABELS_REALPATH)) show_error('Not valid labels\' path: '.ROOT_PATH.'/'.MYM_LABELS_PATH.'.');
    }

    if (defined('MYM_TXTDB_PATH')) {
      define('MYM_TXTDB_REALPATH', realpath(ROOT_PATH.'/'.MYM_TXTDB_PATH));
      if (!defined('MYM_MYSQL')) if (!is_dir(MYM_TXTDB_REALPATH)) show_error('Not valid txtDB path: '.ROOT_PATH.'/'.MYM_TXTDB_PATH.'.');
    }
    
    if (defined('MYM_INI_PATH')) {
      define('MYM_INI_REALPATH', realpath(ROOT_PATH.'/'.MYM_INI_PATH));
      if (!is_dir(MYM_INI_REALPATH)) show_error('Not valid configuration (INI) path: '.ROOT_PATH.'/'.MYM_INI_PATH.'.');
    }

    // defined related to MYM directory
    
    if (defined('MYM_EXT_PATH')) {
      define('MYM_EXT_REALPATH', realpath(MYM_PATH.'/'.MYM_EXT_PATH));
      if (!is_dir(MYM_EXT_REALPATH)) show_error('Not valid plugin path: '.MYM_PATH.'/'.MYM_EXT_PATH.'.');
    }    


  }

  function MyMsetupsession() {  
    /* set the session cache expire time */
    session_cache_expire(MYM_SESSION_EXPIRE_TIME);
    /* start the session */
    session_start();
  }

  function MyMsetupconnection() {
    global $connection, $nqueries, $nopen;
    
    global $mysql;
    $mysql = defined('MYM_MYSQL') ? MYM_MYSQL : false;
    
    if ($mysql) {
      require_once("baseMySQL.php"); 
      $nqueries = 0;
      $connection = connect();
    }
    else
      $nopen = 0;
  }

  function MyMsetup($path = NULL) {
    
    MyMsetuppath($path);
    MyMsetupsession();
    MyMsetupconnection();    
  } 
  
  function MyMclose() {
    global $connection;
    
    if (defined('MYM_MYSQL')) {
      mysql_close($connection);
    }
  }
  
  function MyMsetupusersession($userdb = MYM_USER_DB) {
    
    if (session($userdb."_id") != UNDEFINED) {
      MyMinclude('/core/admin');
      if (MyMchecklogin($userdb) == false) {
        $_SESSION = array();
        die("Security check not passed.");        
      }
    }
    
  } 
  
  function MyMsetuplng() {
    global $set_lng, $set_lngcode;
  
    // if language is not set, find it!
    if (!issession("lng")) { 
      MyMinclude('/tools/language');
      wsession("lng", get_languages());
    }   
      
    // lng command
    wsession("lng", get('lng')); // lng 
    
    return;
  } 

  function MyMcorelng() {
    global $txt;
    
    $path = (MYM_PATH.'/app/lng'); // default languages for MyM messages
    $default = MYM_DEFAULT_LANGUAGE;
    
    if (is_file($path."/text-".session('lng').".php"))
      require_once($path."/text-".session('lng').".php");
    else if (is_file($path."/text-".$default.".php"))
      require_once($path."/text-".$default.".php");
    else
      show_error("File with texts not found in path (".MYM_PATH.'/app/lng'.").");
  }  
  
  function MyMlng($path = MYM_LANGUAGES_REALPATH, $default = MYM_DEFAULT_LANGUAGE) {
    global $txt;

    if (is_file($path."/text-".session('lng').".php"))
      require_once($path."/text-".session('lng').".php");
    else if (is_file($path."/text-".$default.".php"))
      require_once($path."/text-".$default.".php");
    else
      show_error("File with texts not found in path (".$path.").");
    
  }

  function MyMboot($path = NULL) {
    if ($path == NULL)
      show_error('Please define the relative path from MyM to your app directory.');
  
    MyMsetup($path);
    MyMsetupusersession();    
    
    if (defined('MYM_LANGUAGES')) 
      MyMsetuplng();
       
    MyMcorelng();

    if (defined('MYM_LANGUAGES')) 
      MyMlng();

    if (defined('MYM_CACHE')) {
      MyMinclude('/tools/cache');
      cacheOn(MYM_CACHE_REALPATH, MYM_CACHE_EXPIRE_TIME, session('lng').".cache");
    }
  } 

  function MyMinclude($filename) {
    if (!defined("MYM_PATH"))
      show_error('Please setup MyM correctly.');
    
    $filename = str_replace(".php", "", $filename); // purge old .php name;
    
    $file = MYM_PATH."/".$filename.".php";
    if (is_file($file)) 
      require_once($file);
    else
      show_error("MyMinclude > $file not found.");
  }

  function MyMextentions($extentionnames = array()) {
    $extentions = array();
    
    if (!defined("MYM_EXT_REALPATH")) {
      show_warning('Please setup MyM correctly (path for extentions missing).');
      return $extentions; 
    }
    
    if (!is_array($extentionnames)) $extentionnames = array($extentionnames);        

    foreach ($extentionnames as $extentionname) {    
      $file = MYM_EXT_REALPATH."/".$extentionname."/mym.php";
      if (is_file($file)) {
        require_once($file);
        $extentions[$extentionname] = new $extentionname; 
      }
      else
        show_error("MyMextentions > $file not found.");
    }
    
    return $extentions;
  }

  //////////////////// extentions functions
  function extention_action($extentionarray = array(), $extentionname, $action) {
    if (array_key_exists($extentionname, $extentionarray))
      if (method_exists($extentionarray[$extentionname], $action))
        return $extentionarray[$extentionname]->$action();
  }

  function extention_loaded($extentionarray = array(), $extentionname, $message = "") {
    if (!array_key_exists($extentionname, $extentionarray)) {
      print ($message);
      return false;
    } else 
      return true;
  }
  
  function extentions_head($extentionarray = array()) {
    $head = "";
    foreach ($extentionarray as $extention) {
      $head .= $extention->htmlhead()."\n";
    }
    return $head;
  }
  /////////////////////

  function MyMincludestructure($name) {
    if (!defined("MYM_STRUCTURES_REALPATH"))
      show_error('Please setup MyM correctly (path for structures missing).');

    $file = MYM_STRUCTURES_REALPATH."/".$name.".php";
    if (is_file($file)) 
      require_once($file);
    else
      show_error("MyMincludestructure > $file not found.");
  }

  function MyMincludemodule($name) {
    if (!defined("MYM_MODULES_REALPATH"))
      show_error('Please setup MyM correctly (path for modules missing).');

    $file = MYM_MODULES_REALPATH."/".$name.".php";
    if (is_file($file))
      require_once($file);
    else {
      show_warning("MyMincludemodule > $file not found.");
      MyMincludestructure($name);
    }
  }
  
  //////////////////// config retreival function
  
  function config($key = UNDEFINED) {
    global $config;
  
    if (array_key_exists($key, $config))
      return $config[$key];
    else 
      show_error("Config > Key '$key' does not exist in the configuration.");
  }
  
  //////////////////// image position functions
  function imgfile($filename, $subpath = "") {
    $file = ROOT_URI."/".MYM_UPLOAD_PATH.$subpath."/$filename";
    $thumb = ROOT_URI."/".MYM_UPLOAD_PATH.$subpath."/thumb_$filename";
    $max = ROOT_URI."/".MYM_UPLOAD_PATH.$subpath."/resized_$filename";
        
    if (file_exists(MYM_UPLOAD_REALPATH.$subpath."/$filename")) 
      return $file;   
    else
      return false; 
  }
  
  function resizedfile($filename, $subpath = "") {
    $file = ROOT_URI."/".MYM_UPLOAD_PATH.$subpath."/$filename";
    $thumb = ROOT_URI."/".MYM_UPLOAD_PATH.$subpath."/thumb_$filename";
    $max = ROOT_URI."/".MYM_UPLOAD_PATH.$subpath."/resized_$filename";
        
    if (file_exists(MYM_UPLOAD_REALPATH.$subpath."/resized_$filename"))
      return $max;   
    else if (file_exists(MYM_UPLOAD_REALPATH."/$filename")) 
      return $file;
    else
      return false; 
  }
  
  function thumbfile($filename, $subpath = "") {
    $file = ROOT_URI."/".MYM_UPLOAD_PATH.$subpath."/$filename";
    $thumb = ROOT_URI."/".MYM_UPLOAD_PATH.$subpath."/thumb_$filename";
    $max = ROOT_URI."/".MYM_UPLOAD_PATH.$subpath."/resized_$filename";
        
    if (file_exists(MYM_UPLOAD_REALPATH.$subpath."/thumb_$filename"))
      return $thumb; 
    else if (file_exists(MYM_UPLOAD_REALPATH.$subpath."/resized_$filename"))
      return $max;
    else if (file_exists(MYM_UPLOAD_REALPATH.$subpath."/$filename")) 
      return $file;
    else
      return false;    
  }
