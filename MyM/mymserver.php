<?php 
/*
   File: index.php | (c) Giovanni Sileno 2006, 2011
   Distributed as part of "MyM - avant CMS"
   -----------------------------------------------------------------
   This file is part of MyM.

   MyM is free software; you can redistribute it and/or modify
   it under the terms of the GNU Lesser General Public License as published by
   the Free Software Foundation; either version 3 of the License, or
   (at your option) any later version.

   MyM is distributed in the hope that it will be useful,
   but WITHOUT ANY WARRANTY; without even the implied warranty of
   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
   GNU Lesser General Public License for more details.
   
   You should have received a copy of the GNU Lesser General Public License
   along with this program.  If not, see <http://www.gnu.org/licenses/>.
   -----------------------------------------------------------------
   This file runs the control panel of MyM.
*/

function islogged() {
  if (issession(MYM_USER_DB.'_priv')) 
    if (session(MYM_USER_DB.'_priv') > 0)
      return true;
} 
  
class MyMserver {

  var $elem = UNDEFINED, $structures = UNDEFINED, 
      $plugin = UNDEFINED, $plugins = UNDEFINED,
      $a = UNDEFINED, $o = UNDEFINED, $id = UNDEFINED,
      $username = UNDEFINED, $password = UNDEFINED, $email = UNDEFINED, $valcode = UNDEFINED, $logged = false;
  
  function pre() {
  
    $this->o = strtolower(getpost("o")); // Read object
    $this->a = strtolower(getpost("a")); // Read action (i.e. user command)
      
    $this->structures = listfiles(MYM_STRUCTURES_REALPATH);
    
    if ($this->o != UNDEFINED) {
      if ($this->a != 'login') {
        
        if (in_array($this->o, $this->structures)) {
          MyMincludestructure($this->o);
          $this->elem = new $this->o();  
                  
          // TODO: interesting CHECK - $this->elem->db == $this->o
        }    
        else {
          $this->o = UNDEFINED;
        }
      }
    } 
      
    if ($this->o != UNDEFINED) {     
      // Default action when db choosen
      if ($this->a == UNDEFINED) { 
        $this->a = 'read';
      }        
      
      switch ($this->a) { 
        // general actions
        case 'readall':
        case 'read':
          // if single element 
          $this->id = getpost("id");  
          if ($this->id != UNDEFINED) 
            $this->elem->MyMsetId($this->id);
          break;
       
        /* case 'write2':
          $this->id = getpost("id");
          if ($this->id != UNDEFINED) 
            $this->elem->MyMreadarray($this->id);
          $this->elem->MyMsetbypost();
        break;
            
        case 'modify2':
          $this->elem->MyMsetbypost(true);
          break;        
      
        case 'write':   // for new versions of elements
        case 'modify': */
        
	case 'resize':
        case 'delete':
          $this->id = get("id");
          $this->elem->MyMsetId($this->id);
          break;
      
        // special cases
        case 'login':
          $this->username = strtolower(post("username"));
          $this->password = post("password");
          break;
                	                
      	case UNDEFINED:        
      	  break;
      }
    } else {
      if ($this->a == UNDEFINED) { 
        if (islogged()) $this->a = 'none';
        else $this->a = 'login';
      }     
    }
  }
  
  function run() {
    global $set_lng, $set_lngcode;
    
    $output = array();   
  
    // List of actions
    switch ($this->a) {
    
      case 'readall':        
      case 'read':
        
        if ($this->elem->MyMcheckpriv(_READ) != _NONE) {  
          
          // Read a list of elements
          if ($this->id == UNDEFINED) {
            
            if (!issession('elemperpage'))
              wsession('elemperpage', ELEM_PER_PAGE);
            else
              wsession('elemperpage', get('elemperpage'));
            
            $page = get('page', 1);
            $limit = ($page - 1) * session('elemperpage').', '. ($page * session('elemperpage'));
            
            if (($this->elem->Field(_DERIVATIONOFFIELD)) && $this->a == 'read') {
              $result = $this->elem->MyMadvlist("", "", $limit);
              // log_message("ACTION: List elements.");              
            }
            else {
              $result = $this->elem->MyMlist("", "", $limit);
              // log_message("ACTION: List ALL elements.");
            }
            
            $output = array();            
            // if there isn't any element
            if ($result != false && $result[2] == 0) {
              // log_message("No elements.");
              $output = array();
            } 
            // if something is found                      
            else {
              // log_message($result[2]." elements found.");
              foreach ($result as $id) {
                array_push($output, $this->elem->MyMread($id));
              }
            }
            
          }      
          // Read a single element
          else {     
            array_push($output, $this->elem->MyMread());
          } 
        } else  {
          // log_message("Sorry, you are not allowed to perform this action.");
          $output = false;
        }       
        break;
    
      /* case 'write':
        // log_message("ACTION: Write a new element.");          
        $this->elem->MyMprecheck();
        $this->elem->MyMwrite();     
        break;
         
      case 'modify':
        // log_message("ACTION: Modify a new element.");                
        $this->elem->MyMprecheck();
        $this->elem->MyMwrite(false);     
        break;
    
      case 'write2':
        // log_message("ACTION: Write a new element of a new version of an existing element.");         
        if (!$this->elem->MyMpostcheck()) {
          // log_message("Sorry, the form is not valid, fill it again.");              
          $this->elem->MyMprecheck();      
          $this->elem->MyMwrite(true, false);     
        } else {
          $result = $this->elem->MyMrecord(); // new record, or new version of existing record
            
          if ($result) {
            // log_message("This record has been saved!!");
            $this->id = UNDEFINED;
            $this->elem = new $this->o();          
            $this->a = 'read';
            $this->run();
          }
          else {
            // log_message("Sorry, this record could not be saved correctly.");                  
            $this->elem->MyMprecheck();      
            $this->elem->MyMwrite(true, false);     
          }
        }
        break;
            
      case 'modify2':
        // log_message("ACTION: Update an existing element.);         
        if (!$this->elem->MyMpostcheck()) {
          // log_message("Sorry, the form is not valid, fill it again.");
          $this->elem->MyMprecheck();      
          $this->elem->MyMwrite(false, false);     
        } else {
          $result = $this->elem->MyMrecord(false);         
          if ($result) {
            // log_message("This record has been updated!!");
            $this->a = 'read';
            $this->run();
          }
          else {
            // log_message("Sorry, this record could not be saved correctly.");                 
            $this->elem->MyMprecheck();      
            $this->elem->MyMwrite(false, false);     
          }
        }
        break; */
         
      case 'delete':
        // log_message("ACTION: Delete an existing element.);   
        if ($this->elem->MyMdelete()) {
          // log_message("This record has been deleted.");
          $output = true;              
        }      
        else {
          // log_message("Sorry, this record could not be deleted.");
          $output = false;              
        }      
        $this->id = UNDEFINED;
        $this->elem = new $this->o();    
    
        break;
    
      /* 
      case 'validate':
        print("<h2>Validate</h2>");
        require_once(MYM_PATH."/core/admin.php");
        MyMvalidate("user");
        print("<p>If you are not registered, don't wait: ");
        print(makelink("&raquo; create your account!", "write","user")."</p>");
        break;
        
      //
      case 'validate2':
        print("<h2>Validate</h2>");
        require_once(MYM_PATH."/core/admin.php");
        require_once(MYM_PATH."/core/admin.php");
        if (MyMcheckvalidate("user", $this->email, $this->valcode)) { 
          print "<p>".makelink("&laquo; Home")."</p>";
        } else {
          print("<p>Please enter again your email and validation code:</p>");
          MyMvalidate("user");
          print("<p>If you are not registered, don't wait: ");
          print(makelink("&raquo; create your account!", "write","user")."</p>");
        }
        break;
      
  
      //         
      case 'login':
        // log_message("ACTION: login.);        
        require_once(MYM_PATH."/core/admin.php");
        MyMlogin("user");
        break;
        
      */        
        
      //
      case 'login':
        // log_message("ACTION: login.);        
        require_once(MYM_PATH."/core/admin.php");
        if (MyMchecklogin("user", $this->username, $this->password, true)) { // correct password
          if (MyMloginOk("user", $this->username, $this->password)) 
            // log_message("Login successful."); 
            $output = true;
          else 
            // log_message("Sorry, not successful login."); 
            $output = false;
        } else {
          // log_message("Sorry, not successful login."); 
          $output = false;
        }
        break;
      
      //
      case 'logout':
        // log_message("ACTION: logout.);  
        require_once(MYM_PATH."/core/admin.php");
        MyMlogout("user");
        // log_message("Logout successful."); 
        $output = true;
        break;  
      
        case 'resize':
        if (issession(MYM_USER_DB.'_priv')) {
          if (session(MYM_USER_DB.'_priv') >= MYM_ADMIN_PRIV) {
            print("<h2>Resizing all images...</h2>\n");
            require_once(MYM_PATH."/core/upload.php");
            for ($i=0; $i < count($this->structures); $i++) {
              $db = $this->structures[$i];
              MyMincludestructure($db);
              print("<p>reading db <strong>$db</strong>... </p>");
  
              $this->elem = new $db();
              $rules = $this->elem->MyMrules();
              $checkpriv = $this->elem->MyMcheckpriv(_WRITE);
              $imgkeys = NULL;
          
              if ($checkpriv == _ALL) {
                $keys = array_keys($rules);
                for ($j = 0; $j < count($keys); $j++) {
                  $key = $keys[$j];
                  if ($rules[$key]->type == _IMAGE) {
                    $imgkeys[] = $key;
                  }
                }
            
                if ($imgkeys != NULL) {
                  list($listid, $n, $ntot) = $this->elem->MyMlist();
              
                  for ($z = 0; $z < $n; $z++) {
                    $this->elem->MyMread($listid[$z]);
  
                    for ($j = 0; $j < count($imgkeys); $j++) {
                      $key = $imgkeys[$j];                    
                      resizeimage($this->elem->$key, MYM_UPLOAD_REALPATH."/".$this->elem->uploadinternalpath(), $rules[$key]->maxwidth, $rules[$key]->maxheight, $rules[$key]->thumbsize, $rules[$key]->squaredthumb, $rules[$key]->minwidth, $rules[$key]->minheight);
                    }
                  }
                }
              } else
                  print ("<p><strong>Error</strong> forbidden action.</p>");
          
            }
  
            break;
          }
        }
	
      case 'none':
      default:  
        
    }     
  }
  
 //  return $output;
}



//////////////////////////////////////////
// setup GET / POST variables
//////////////////////////////////////////

// --------------------------------------
// MAIN
// --------------------------------------

// if MyM is loaded directly, MyM is the application (for tests)
if (!defined(APP_RELATIVE_PATH)) {
  define('APP_RELATIVE_PATH', './');
  define('CONFIG_RELATIVE_PATH', './app/config');
}
if (defined('CONFIG_RELATIVE_PATH'))
  require_once(CONFIG_RELATIVE_PATH."/config.php");

if (!defined(ADMIN_PATH)) {
  define('ADMIN_PATH', __DIR__);
}

require_once(MYM_RELATIVE_PATH.'/core/baseMyM.php'); // include MyM 
MyMsetup(MYM_RELATIVE_PATH);

$time_start = microtime_float();

MyMsetupusersession();
MyMsetuplng();
MyMserverlng();

// Debug traces
/* trace_r(MYM_ADMIN_TRACE + 3, "posts: ", $_POST);
trace_r(MYM_ADMIN_TRACE + 3, "gets: ", $_GET);
trace_r(MYM_ADMIN_TRACE + 3, "files: ", $_FILES);
trace_r(MYM_ADMIN_TRACE + 3, "session: ", $_SESSION); */

$mym = new MyMserver();

$mym->pre();

$output = $mym->run();

MyMclose(); 

print(json_encode($output));