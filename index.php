<?php 
/*
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

error_reporting(E_ALL);
ini_set("display_error", "1");

define('MYM_ADMIN_TRACE', 0);
define('ELEM_PER_PAGE', 5);

//////////// helper functions

function printtest($bool) {
  if (!$bool) print(" <em>failed</em>");
  else print(" <em>OK</em>");
}

function islogged($minpriv = 0) {
  // if there is no user management
  if (!defined("MYM_USER_DB")) {
    return true;
  } // else
  else {
    if (issession(MYM_USER_DB.'_priv')) 
      if (session(MYM_USER_DB.'_priv') > $minpriv)
        return true;
  }
  return false;
} 

function redirect($url="index.php") { ?>
  <script type="text/JavaScript">
  <!--
  setTimeout("location.href = '<?php echo $url ?>';", 700);
  -->
  </script>
<?php
}
  
//////////// MyMadmin class

class MyMadmin {
  var $elem = UNDEFINED, $structures = UNDEFINED, 
      $plugin = UNDEFINED, $plugins = UNDEFINED,
      $a = UNDEFINED, $o = UNDEFINED, $id = UNDEFINED,
      $username = UNDEFINED, $password = UNDEFINED, $email = UNDEFINED, $valcode = UNDEFINED, $logged = false;
  
  // read and interpret the input parameters (given by GET or POST)
  
  function pre() {
  
    $this->a = strtolower(getpost("a")); // Read action (i.e. user command)
    $this->o = strtolower(getpost("o")); // Read object
  
    $this->structures = listfiles(MYM_STRUCTURES_REALPATH);
    
    // find all the MyM plugins 

    if (is_dir("./plugins")) {
      $this->plugins = listfiles("./plugins");    
    } else {
      $this->plugins = array();
    }
    $this->plugin = strtolower(getpost("plugin"));
    
    $array = $this->plugins;
    
    if ($this->plugin != UNDEFINED) {
      if (!in_array($this->plugin, $this->plugins) || !islogged()) {
        $this->plugin = UNDEFINED;
        $this->a = 'home';
      }
    }    
    
    if ($this->plugin == UNDEFINED) {
      if ($this->o != UNDEFINED) {
        if ($this->a != 'login2') {
        
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
            $this->id = get("id");  
            if ($this->id != UNDEFINED) 
              $this->elem->MyMsetId($this->id);
            else 
              $page = get("page", 1);
            break;
       
          case 'write2':
            $this->id = post("id");
            if ($this->id != UNDEFINED) 
              $this->elem->MyMread($this->id);
            $this->elem->MyMsetbypost();
            break;
            
          case 'modify2':
            $this->elem->MyMsetbypost(true);
            break;        
      
          case 'write':   // for new versions of elements
          case 'modify':
          case 'delete':
            $this->id = get("id");
            $this->elem->MyMsetId($this->id);
            break;
      
          // special cases
        
          case 'login':
          case 'login2':
            $this->username = strtolower(post("username"));
            $this->password = post("password");
            break;
          
          case 'validate2':
            $this->email = strtolower(getpost("email"));
            $this->valcode = strtoupper(getpost("valcode"));     
      	  break;
      	                
      	case UNDEFINED:        
      	  break;
        }
      } else {
        if ($this->a == UNDEFINED) {     trace_r(MYM_ADMIN_TRACE + 3, "plugins found: ", $this->plugins);
    trace_r(MYM_ADMIN_TRACE + 3, "plugin called: ", $this->plugin);
          if (islogged()) $this->a = 'home';
          else $this->a = 'login';
        }     
      }
    } 
  }
  
  
  // execute the command prepared by pre()
    
  function run() {
    global $set_lng, $set_lngcode;
    global $buildstring, $nested;
  
    trace(MYM_ADMIN_TRACE + 1, "index > switching to command ".$this->a.(($this->o!=UNDEFINED)?", db ".$this->o."...":""));

    // List of actions
    switch ($this->a) {
    
      case 'readall':        
      case 'read':
        
        if ($this->id != UNDEFINED) print("<h2>Read</h2>\n");
        else print("<h2>List</h2>\n");
        
        if ($this->elem->MyMcheckpriv(_READ) != _NONE) {  
          
          // Read a list of elements
          if ($this->id == UNDEFINED) {
            
            if (!issession('elemperpage'))
              wsession('elemperpage', ELEM_PER_PAGE);
            else
              wsession('elemperpage', get('elemperpage'));
            
            $page = get('page', 1);
            $limit = ($page - 1) * session('elemperpage').', '. ($page * session('elemperpage'));
            
            if (($this->elem->Field(_DERIVATIONOFFIELD)) && $this->a == 'read')
              $result = $this->elem->MyMadvlistprint("", "", $limit);
            else
              $result = $this->elem->MyMlistprint("", "", $limit);
              
            // if there isn't any element
            if ($result != false && $result[2] == 0) {
              print("<p>Sorry, There are no ".$this->o."s recorded.</p>\n");
              if ($this->elem->MyMcheckpriv(_WRITE) != _NONE) {  
                // print("<div id='line'>&nbsp;</div>\n");
                print("<p>Please insert one:</p>\n");
                print("<h2>Write</h2>\n");              
                $this->elem->MyMprecheck();
                $this->elem->MyMwrite();             
              } 
            } 
            // if something is found                      
            else {
              
              $npages = ceil($result[2] / session('elemperpage'));
              
              $first = session('elemperpage') * ($page - 1) + 1;
              $last = session('elemperpage') * ($page - 1) + $result[1] ;
              
              $toppages = "";
              if ($page > 1) {
                $toppages .= "<a href='index.php?a=$this->a&o=$this->o&page=" . ($page-1) ."'>&laquo; previous page </a>";
                if ($page < $npages)
                  $toppages .= " | ";
              }
              if ($page < $npages)
                $toppages .= "<a href='index.php?a=$this->a&o=$this->o&page=" . ($page+1) ."'>&raquo; next page</a>";
   
              $bottompages = "";
              for ($thispagenum = 1; $thispagenum <= $npages; $thispagenum = $thispagenum + 1) {      
                if ($thispagenum > 1)
                  $bottompages .= " | ";
  
                if ($thispagenum == $page) $bottompages .= " <strong>";
                $bottompages .= "<a href='index.php?a=$this->a&o=$this->o&page=". $thispagenum  ."'>".$thispagenum. "</a>";
                if ($thispagenum == $page) $bottompages .= "</strong> ";
              }             
              
              print("<p>".$this->o."s $first - $last of ".$result[2]."</p>\n");
              
              print("<p>\n");
              print($toppages."<br />\n");
              print("pages: ".$bottompages."<br />\n");
              print($this->o."s per page: ");
              
              for ($newelemperpage = 5; $newelemperpage <= 40; $newelemperpage = $newelemperpage * 2) {
                if ($newelemperpage > 5)
                  print(" | ");
                if ($newelemperpage == session('elemperpage')) print("<strong>");
                print("<a href='index.php?a=$this->a&o=$this->o&elemperpage=$newelemperpage'>$newelemperpage</a>");
                if ($newelemperpage == session('elemperpage')) print("</strong>");
              }
              print("</p>\n");
              
            }
            
          }      
          // Read a single element
          else {     
            $this->elem->MyMreadprint();
  		  
            if (($this->elem->Field(_DERIVATIONOFFIELD)) && $this->a == 'read') {
              print('<div class="box">');
  		     list($listold, $listlng) = $this->elem->MyMadvothers();       
              if ($listold != NULL) {
                   print("<p>Historical versions: <br/>");
                   if (!($datefield = $this->elem->Field(_DATEFIELD)))
                     $datefield = 'id';
                   while ($version = array_pop($listold))
                  print(makelink($version[$datefield], 'read', $this->elem->db, $version['id'], "index.php", ($this->elem->id == $version['id'])));
                   print("</p>");
                 } 
                 if ($listlng != NULL) {
                            print("<p>Languages (most recent versions): <br/>");
                            $lngfield = $this->elem->Field(_LANGUAGEFIELD);
                   while ($version = array_pop($listlng)) {
                              $flag = "<img src='".MYM_RELATIVE_PATH."/img/ext_".$version[$lngfield].".png' /></a>"; // TO BE CORRECTED: alt field
                              print(makelink($flag, 'read', $this->elem->db, $version['id'], "index.php", ($this->elem->$lngfield == $version[$lngfield])));
                   }
                   print("</p>");
                 }
                  print('</div>');
              }
            } 
            }
         else  {
          print("<p>Sorry, you are not allowed to perform this action.</p>\n");
         }     
  
        break;
    
      case 'write':
        print("<h2>Write</h2>\n");
        $this->elem->MyMprecheck();
        $this->elem->MyMwrite();     
        break;
         
      case 'modify':
        print("<h2>Modify</h2>\n");
        $this->elem->MyMprecheck();
        $this->elem->MyMwrite(false);     
        break;
    
      case 'write2':
        print("<h2>Record</h2>\n");
        if (!$this->elem->MyMpostcheck()) {
          print "<p>Sorry, your form is not valid, please check:</p>\n";
          print("<h2>Write</h2>\n");              
          $this->elem->MyMprecheck();      
          $this->elem->MyMwrite(true, false);     
        } else {
             $result = $this->elem->MyMrecord(); // new record, or new version of existing record
            
          if ($result) {
            print "<p>This record has been saved!!</p>\n";
            $this->id = UNDEFINED;
            $this->elem = new $this->o();          
            $this->a = 'read';
            print "<p>Redirecting to ".makelink("&raquo; Control Panel")."...</p>\n";
            redirect();
          }
          else {
            print "<p>Sorry, this record could not be saved correctly.</p>\n";
            print("<h2>Write</h2>\n");                       
            $this->elem->MyMprecheck();      
            $this->elem->MyMwrite(true, false);     
          }
        }
        break;
            
      case 'modify2':
        print("<h2>Update</h2>\n");
        if (!$this->elem->MyMpostcheck()) {
          print "<p>Sorry, your form is not valid, please check:</p>\n";
          print("<h2>Modify</h2>\n");                      
          $this->elem->MyMprecheck();      
          $this->elem->MyMwrite(false, false);     
        } else {
	  $result = $this->elem->MyMrecord(false); 
            
          if ($result) {
            print "<p>This record has been updated!!</p>\n";
            $this->a = 'read';
            print "<p>Redirecting to ".makelink("&raquo; Control Panel")."...</p>\n";
            redirect();
          }
          else {
            print "<p>Sorry, this record could not be saved correctly.</p>\n";
            print("<h2>Modify</h2>\n");                      
            $this->elem->MyMprecheck();      
            $this->elem->MyMwrite(false, false);     
          }
        }
        break;
         
      case 'delete':
        print("<h2>Delete</h2>\n");
        if ($this->elem->MyMdelete()) {
          print "<p>This record has been deleted.</p>\n";
          print "<p>Redirecting to ".makelink("&raquo; Control Panel")."...</p>\n";
          redirect();          
        }      
        else {
          print "<p>Sorry, this record could not be deleted.</p>\n";
        }      
        $this->id = UNDEFINED;
        $this->elem = new $this->o();       
        $this->a = 'read';
        $this->run();
        break;
    
      //
      case 'validate':
        print("<h2>Validate</h2>\n");
        require_once(MYM_PATH."/core/admin.php");
        MyMvalidate("user");
        print("<p>If you are not registered, don't wait: \n");
        print(makelink("&raquo; create your account!", "write","user")."</p>\n");
        break;
        
      //
      case 'validate2':
        print("<h2>Validate</h2>\n");
        require_once(MYM_PATH."/core/admin.php");
        require_once(MYM_PATH."/core/admin.php");
        if (MyMcheckvalidate("user", $this->email, $this->valcode)) { 
          print "<p>Redirecting to ".makelink("&raquo; Control Panel")."...</p>\n";
          redirect();
        } else {
          print("<p>Please enter again your email and validation code:</p>\n");
          MyMvalidate("user");
          print("<p>If you are not registered, don't wait: ");
          print(makelink("&raquo; create your account!", "write","user")."</p>\n");
        }
        break;
  
      //         
      case 'login':
        print("<h2>Login</h2>\n");
        require_once(MYM_PATH."/core/admin.php");
        MyMlogin("user");
        print("<p>If you are not registered, don't wait: \n");
        print(makelink("&raquo; create your account!", "write","user")."</p>\n");
        break;
        
      //
      case 'login2':
        print("<h2>Login</h2>\n");
        require_once(MYM_PATH."/core/admin.php");
        if (MyMchecklogin("user", $this->username, $this->password, true)) { // correct password
          if (MyMloginOk("user", $this->username, $this->password)) 
            print("<p>Login successful.</p>\n"); 
          else 
            print("<p>Sorry, not successful login.</p>\n"); 
          print "<p>Redirecting to ".makelink("&raquo; Control Panel")."...</p>\n";
          redirect();
        } else {
          print("<p>Please enter again your login:</p>\n");
          MyMlogin("user");
          print("<p>If you are not registered, don't wait: ");
          print(makelink("&raquo; create your account!", "write","user")."</p>\n");
        }
        break;
      
      //
      case 'logout':
        print("<h2>Logout</h2>\n");
        require_once(MYM_PATH."/core/admin.php");
        MyMlogout("user");
        print "<p>Your logout has been successful.</p>\n";
        print "<p>Redirecting to ".makelink("&raquo; Control Panel")."...</p>\n";
        redirect();
        break;  
        
      // 
      case 'reset':
        if (issession(MYM_USER_DB.'_priv')) {
          if (session(MYM_USER_DB.'_priv') >= MYM_ADMIN_PRIV) {
            print("<h2>Reset all images...</h2>\n");
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
                      $filename = $this->elem->$key;                     
		      if (is_file($this->elem->fileabspath($filename))) {
		        print("File $filename |");
		        if (is_file($this->elem->fileabspath("thumb_".$filename))) {
		           unlink($this->elem->fileabspath("thumb_".$filename));
			   print(" thumb removed |");
		        }
		        if (is_file($this->elem->fileabspath("resized_".$filename))) {
		           unlink($this->elem->fileabspath("resized_".$filename)); 
			   print(" resized removed |");
		        }
                        print("<br/>\n");		      
		      }
		    }
		  }
                }
              } else
                  print ("<p><strong>Error</strong> forbidden action.</p>");
          
            }
  
            break;
          }
        }
	
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
     /*    
      case 'build':
        if (issession(MYM_USER_DB.'_priv')) {
          if (session(MYM_USER_DB.'_priv') >= MYM_ADMIN_PRIV) {
            print("<h2>building MyM...</h2>\n");
	    MyMinclude("/core/MyMbuild.php");
  
            $newpath = MYM_STRUCTURES_REALPATH."/../compiled/";
  
            print("<p>");
            if (!is_dir($newpath))
              print("Sorry. $newpath not existing...<br/>\n");
            else {
              for ($i = 0; $i < count($this->structures); $i++) {
                MyMincludestructure($this->structures[$i]);
                $this->elem = new $this->structures[$i]();
                $rules = $this->elem->MyMrules();
  
                print("Creating file <strong>".$this->structures[$i].".php</strong>...");
                if (($file = fopen(realpath($newpath)."/".$this->structures[$i].".php", "w")) === NULL) {
                  fclose($file);
                  print("Sorry. Some error occurred...<br/>\n");
                }
                else {
                  $buildstring = "";
                  $nested = 0;
                  print("<br/>\n");
  
                  $this->elem->buildHead();
                  $this->elem->buildConstants($rules);
                  $this->elem->buildClassHead();
                  $this->elem->buildClassProperties($rules);
                  $this->elem->buildMyMcheckpriv();
                  $this->elem->buildMyMprocessprint($rules);
                  $this->elem->buildMyMprint($rules);
                  $this->elem->buildMyMwrite($rules);
                  $this->elem->buildClassEnd();
                  $this->elem->buildEnd();
  
                  $this->elem->buildInputForm($rules);
                  $this->elem->buildPrecheckItem($rules);
                  $this->elem->buildMyMcheckpriv();
                  $this->elem->buildMyMprint($rules);
                  $this->elem->buildMyMread($rules);
                  $this->elem->buildMyMreadprint();
                  $this->elem->buildMyMadvlist();
                  $this->elem->buildMyMlist();
                  $this->elem->buildMyMlistprint();
                  $this->elem->buildMyMadvlistprint();
                  $this->elem->buildMyMwrite($rules, "testindex.php");
                  $this->elem->buildMyMsetId();
                  $this->elem->buildMyMset();
                  $this->elem->buildMyMsetbypost();
                  $this->elem->buildMyMget();
                  $this->elem->buildMyMrecord($rules);
                  $this->elem->buildMyMdelete($rules);
                  $this->elem->buildMyMprecheck();
                  $this->elem->buildMyMpostcheck();
                  $this->elem->buildClassEnd();
                  $this->elem->buildEnd();
  
                  if (fwrite($file, $buildstring) === FALSE) {
                    print("Cannot write to file ".MYM_STRUCTURES_REALPATH."/../compiled/".$this->structures[$i].".php");
                  }
  
                  fclose($file);
                }
              }
            }
  
            print("</p>\n");
            print "<p>Redirecting to ".makelink("&raquo; Control Panel")."...</p>\n";
            break;
          }
        }           
   */
        
      case 'about':
        print("<div id='table2col'>");
        print("<table>");
        print("<tr><td class='left'>&nbsp;</td><td>");
        print("<h1>MyM</h1>\n");
        print("</td></tr>");
        print("<tr><td class='left'>site URI</td><td class='right'>".ROOT_URI."</td></tr>\n");
        print("<tr><td class='left'>administrator email</td><td class='right'>".MYM_ADMIN_EMAIL."</td></tr>\n");
        print("<tr><td class='left'>MyM Version</td><td class='right'>".MYM_VERSION."</td></tr>\n");
        print("<tr><td class='left'>absolute path to MyM</td><td class='right'>".MYM_PATH."</td></tr>\n");      
        print("<tr><td class='left'>relative path to MyM</td><td class='right'>".MYM_RELATIVE_PATH."</td></tr>\n");      
        print("<tr><td class='left'>path to structures</td><td class='right'>".MYM_STRUCTURES_REALPATH."</td></tr>\n");
        print("<tr><td class='left'>path to modules</td><td class='right'>".MYM_MODULES_REALPATH."</td></tr>\n");
        
        print("<tr><td class='left'>users database</td><td class='right'>".(defined("MYM_USER_DB")?MYM_USER_DB:"not activated")."</td></tr>\n");      
        print("<tr><td class='left'>not logged user priv</td><td class='right'>".MYM_NOT_LOGGED_USER_PRIV."</td></tr>\n");                  
        print("<tr><td class='left'>standard new user priv</td><td class='right'>".MYM_NEW_USER_PRIV."</td></tr>\n");            
        print("<tr><td class='left'>admin priv</td><td class='right'>".MYM_ADMIN_PRIV."</td></tr>\n");            
        
        print("<tr><td class='left'>cache</td><td class='right'>");
        if (defined('MYM_CACHE')) print("activated");
        else print("not activated");
        print("</td></tr>\n");      
        if (defined('MYM_CACHE')) {
          print("<tr><td class='left'>cache expires in (sec)</td><td class='right'>".MYM_CACHE_EXPIRE_TIME."</td></tr>\n"); 
          print("<tr><td class='left'>cache path</td><td class='right'>".MYM_CACHE_REALPATH."</td></tr>\n");
        }
        
        print("<tr><td class='left'>session expires in (min)</td><td class='right'>".MYM_SESSION_EXPIRE_TIME."</td></tr>\n");      
        
        print("<tr><td class='left'>structures</td>");
              print("<td class='right'>\n");
        for ($i = 0; $i < count($this->structures); $i++) {
          print($this->structures[$i].' ');
        }           
        print("</td></tr>\n");
        
        print("<tr><td class='left'>MySQL</td><td class='right'>");
        if (defined('MYM_MYSQL')) print("activated");
        else print("not activated");
        print("</td></tr>\n");      
        if (defined('MYM_MYSQL')) {
          print("<tr><td class='left'>MySQL server</td><td class='right'>".MYM_MYSQL_SERVER."</td></tr>\n");                 
          print("<tr><td class='left'>database</td><td class='right'><strong>".MYM_MYSQL_DB. "</strong> (connection test");
          require_once(MYM_PATH."/core/baseMySQL.php");
          printtest(testmysqlconnect());
          print(")</td></tr>\n");
        }
        else
          print("<tr><td class='left'>txtDB path</td><td class='right'>".MYM_TXTDB_REALPATH."</td></tr>\n");
  
        print("<tr><td class='left'>default upload path</td><td class='right'>".MYM_UPLOAD_REALPATH."</td></tr>\n");
  
        print("<tr><td class='left'>languages</td><td class='right'>");
        
        for ($i=0; $i<count($set_lngcode); $i++) {   
          print("  <a href='index.php?lng=".$set_lngcode[$i] ."'");       
          if(session('lng') == $set_lngcode[$i]) print(" class='current'");
          print("><img src='".MYM_RELATIVE_PATH."/img/ext_".$set_lngcode[$i].".png' alt='".$set_lng[$i]."' /></a>");
        }
        print("</td></tr>\n");
        print("<tr><td class='left'>&nbsp;</td><td class='rightbutton'>");
        /* if (((issession(MYM_USER_DB.'_priv')) && (session(MYM_USER_DB.'_priv') >= MYM_ADMIN_PRIV)) || is_file(APP_PATH.'/installing.now')) {
            if (defined('MYM_MYSQL')) print("<a href='index.php?a=createdb'><strong>Create MySQL Tables</strong></a> ");
            else print("<a href='index.php?a=createdb'><strong>Create TxtDB Tables</strong></a>");
	    print(" | <a href='index.php?a=createmodules'><strong>Create standard modules</strong></a>");
        } */
        if ( defined("MYM_USER_DB") && ((issession(MYM_USER_DB.'_priv')) && (session(MYM_USER_DB.'_priv') >= MYM_ADMIN_PRIV))) {
          // print(" | <a href='index.php?a=build'><strong>Build MyM</strong></a> | ");
          print("<a href='index.php?a=resize'><strong>Resize images</strong></a>");
	  print(" | <a href='index.php?a=reset'><strong>Remove thumb/resized images</strong></a>");
        } else print("&nbsp;");
        print("</td></tr></table>");      
        print("</div>");
        break;

      case 'home':
      
        if (file_exists("./custom.php")) { // in the admin directory of the app
          include_once("./custom.php");
		  print("<hr/>");
		}      
		      
        print("<h2>Structures</h2>");
        $structures = $this->structures;    
        $plugins = $this->plugins;
        
        print("<ul class='structures'>\n");     
        for ($i = 0; $i < count($structures); $i++) {
          MyMincludestructure($structures[$i]);
          $this->elem = new $structures[$i]();
          
          $priv = $this->elem->MyMcheckpriv(_WRITE);
          if ($priv != _NONE) {  
           print("  <li><strong><a"); if ($this->o == $structures[$i]) print(" class='active'"); print(" href='index.php?o=".$structures[$i]."'>".$structures[$i]."</a></strong></li>\n");
          } 
        }
        print("</ul>\n"); 

        print("<div class='clear'>&nbsp;</div>");
        if (count($plugins) > 0) {
		  print("<h2>Plugins</h2>");
          print("<ul class='structures'>\n");     
          for ($i = 0; $i < count($plugins); $i++) {
            print("  <li><strong><a href='index.php?plugin=".$plugins[$i]."'>".$plugins[$i]."</a></strong></li>\n");
          }
          print("</ul>\n");         
        }
        break;
        
      default:  
        print("<h2>Not valid action.</h2>");
    }     
  }

  function runplugin() {
    print("<h2> Plugin: <em>".$this->plugin."</em></h2>");
    require_once(ADMIN_PATH."/plugins/".$this->plugin.".php");
  }

}



//////////////////////////////////////////
// setup GET / POST variables
//////////////////////////////////////////


///---------------------------------------------
//   View
///---------------------------------------------
function loginMenu($o, $a, $structures) {

  print("<ul>\n");
  print("  <li>\n");  
  switch ($_SESSION['lng']) {
    default:
    case 'it':
      print("<img src='".MYM_RELATIVE_PATH."/img/ext_it.png' alt='Italiano' />");break;
    case 'en':
      print("<img src='".MYM_RELATIVE_PATH."/img/ext_uk.png' alt='English' />");break;
    case 'de':
      print("<img src='".MYM_RELATIVE_PATH."/img/ext_de.png' alt='Deutsch' />");break;        
    case 'es':
      print("<img src='".MYM_RELATIVE_PATH."/img/ext_es.png' alt='Espa�ol' />");break;        
    case 'pt':
      print("<img src='".MYM_RELATIVE_PATH."/img/ext_pt.png' alt='Portugu�s' />");break;        
    case 'fr':
      print("<img src='".MYM_RELATIVE_PATH."/img/ext_fr.png' alt='Fran�ais' />");break;
  }
  print("  </li>\n");    

  
  // if there is user management
  if (defined("MYM_USER_DB")) {
    $logged = islogged() && ($a != "logout");
      
    if ($logged) {   
      print("  <li><a "); if ($a == UNDEFINED || $a == "") print("class='active'"); print("href='index.php'>Admin</a></li>\n"); 

      print("  <li><a ");
      if ($a == "logout") print("class='active' "); 
      print("href='index.php?a=logout'>Logout</a></li>\n");
    } 
    else { 
      print("  <li><a ");
      if ($a == "login") print("class='active' ");  
      print("href='index.php?a=login'>Login</a></li>\n");
    } 
  } else {
	print("  <li><a "); if ($a == UNDEFINED || $a == "") print("class='active'"); print("href='index.php'>Admin</a></li>\n"); 
  }
  
  print("  <li><a ");
  if ($a == "about") print("class='active' ");  
  print("href='index.php?a=about'>About</a></li>\n");
  
  print("</ul>\n");
}

function actionMenu($o, $a, $structures, $elem, $id) {
  
  if ($o != UNDEFINED && $a != 'login2') {
    
    print ("<strong>$o</strong>\n");
    
    print("<ul>\n");
    
    if ($a == 'read' && $id != UNDEFINED) {
      print("<div id='line'>&nbsp;</div>\n");
      if ($id != UNDEFINED)
        print("$o n.$id");
    
      if ($elem->MyMcheckpriv(_WRITE) != _NONE) { 
        print("  <li><a "); if ($a == "modify") print("class='active' "); print("href='index.php?o=$o&a=modify&id=$id'>Modify / Update</a></li>");
      }    
      if ($elem->Field(_DERIVATIONOFFIELD)) {  
        print("  <li><a "); if ($a == "write") print("class='active' "); print("href='index.php?o=$o&a=write&id=$id'>Create New version</a></li>");
      }       
      if ($elem->MyMcheckpriv(_DELETE) != _NONE) {     
        print("  <li><a "); if ($a == "delete") print("class='active' "); print("href='index.php?o=$o&a=delete&id=$id'>Delete</a></li>");
      }         
    }
     
    if ($elem->MyMcheckpriv(_WRITE) != _NONE) { 
      print("<div id='line'>&nbsp</div>\n");
      print("  <li><a "); if ($a == "write") print("class='active' "); print("href='index.php?o=$o&a=write'>New</a></li>\n");
    }      
     
    if ($elem->MyMcheckpriv(_READ) != _NONE) { 
      print("<div id='line'>&nbsp</div>\n");
      print("  <li><a "); 
      if ($a == "read" && ($id == UNDEFINED || $id == '')) 
        print("class='active' "); 
      print("href='index.php?o=$o&a=read'>List</a></li>\n");

      if ($elem->Field(_DERIVATIONOFFIELD)) {  
        print("  <li><a "); if ($a == "readall" && ($id == UNDEFINED || $id == '')) print("class='active' "); print("href='index.php?o=$o&a=readall'>List All</a></li>\n");    
      }
    }      
     
    print("</ul>\n");
  }
}

//////////////////////////////////////////
// execute action
//////////////////////////////////////////

// --------------------------------------
// MAIN
// --------------------------------------

// if MyM is loaded directly, MyM is the application (for tests)
if (!defined('APP_RELATIVE_PATH')) {
  define('CONFIG_RELATIVE_PATH', './app/config');
}
if (defined('CONFIG_RELATIVE_PATH'))
  require_once(CONFIG_RELATIVE_PATH."/config.php");
else
  die("Sorry, configuration not valid.");

if (!defined('ADMIN_PATH')) {
  define('ADMIN_PATH', dirname(__FILE__));
}

require_once(MYM_RELATIVE_PATH.'/core/baseMyM.php'); // include MyM 
MyMsetup(MYM_RELATIVE_PATH);

$time_start = microtime_float();

if (defined("MYM_USER_DB")) MyMsetupusersession();
MyMsetuplng();
MyMcorelng();

$extentions = MyMextentions(array('jscalendar', 'selectbox'));

// Debug traces
trace_r(MYM_ADMIN_TRACE + 3, "posts: ", $_POST);
trace_r(MYM_ADMIN_TRACE + 3, "gets: ", $_GET);
trace_r(MYM_ADMIN_TRACE + 3, "files: ", $_FILES);
trace_r(MYM_ADMIN_TRACE + 3, "session: ", $_SESSION);

$mymadmin = new MyMadmin();

$mymadmin->pre();

$o = $mymadmin->o;
$a = $mymadmin->a;
$structures = $mymadmin->structures;

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
  <meta http-equiv="content-type" content="text/html; charset=utf-8" />
  <meta name="robots" content="noindex, nofollow" />
  <title>MyM Administration <?php if ($o != UNDEFINED && $o != "") print("&middot; ".$o); 
  if ($a != UNDEFINED && $a != "") print(" &middot; ". $a); ?> </title>
  
  <link rel="stylesheet" href="<?php print(MYM_RELATIVE_PATH);?>/css/admin.css" type="text/css" />
  
<?php print(extentions_head($extentions)); ?>

</head>
<body>

<div id="navmenu">
<?php loginMenu($o, $a, $structures); ?>
</div>

<div id="header">
  <h1><a href="index.php">MyM Control Panel</a> </h1>
 
</div>
<div id='credits'>
<p>Site powered by - <strong>MyM</strong> - revision: <?php print(MYM_VERSION); ?>  | <?php print("  <a href='".ROOT_URI."' target='_blank'>Homepage</a>\n"); ?> </p>
</div>

<div id='content'>
<?php 
  if (($mymadmin->plugin == UNDEFINED) && ($a == 'write' || $a == 'modify' || $a == 'write2' || $a == 'modify2' || $a == 'delete' || $a == 'read' || $a == 'readall')) 
    $leftcolumn = true;
  else
    $leftcolumn = false;

  if ($leftcolumn) 
    print("<div id='rightcontent'>\n");
  else
    print("<div id='rightcontent' style='width: 100%'>\n");
  
  if ($mymadmin->plugin != UNDEFINED) {
    trace(MYM_ADMIN_TRACE + 1,'Running plugin '.$mymadmin->plugin);
    $mymadmin->runplugin();
  } else {
    trace(MYM_ADMIN_TRACE + 1,'Running admin console.');
    $mymadmin->run();
  }
  
  print("</div>\n");
  
  if ($leftcolumn) {
    print("  <div id='leftcontent'>\n");
    print("    <div class='box'>\n");
    actionMenu($o, $a, $structures, $mymadmin->elem, $mymadmin->id);
    print("    </div>\n");
    
    if ($a == 'write' || $a == 'modify') {
      print("    <div class='box'>\n");
      print("tags for texts<br/>");
      print("<div id='line'>&nbsp;</div>\n");
      print("<span class='typed'>*bold*</span> &raquo; <strong>bold</strong><br/>");
      print("<span class='typed'>[b]bold[/b]</span> &raquo; <strong>bold</strong><br/>");
      print("<span class='typed'>+italic+</span> &raquo; <em>italic</em><br/>");
      print("<span class='typed'>[i]italic[/i]</span> &raquo; <em>italic</em><br/>");
      print("<span class='typed'>[h]title[/h]</span> &raquo; <strong>title</strong><br/>");
      print("<div id='line'>&nbsp;</div>\n");
      print("<span class='typed'>[list]<br/>item1<br/>item2<br/>[/list]</span><br/>");      
      print("<div id='line'>&nbsp;</div>\n");
      print("<span class='typed'>[c], [c/]</span> &raquo; &para; capoverso<br/>");
      print("<div id='line'>&nbsp;</div>\n");
      print("internal links<br/>");
      print("<span class='typed'>[u]URL[/u]</span><br/>");
      print("<span class='typed'>[u=URL]text[/u]</span><br/>");
      print("<div id='line'>&nbsp;</div>\n");
      print("external links<br/>");
      print("<span class='typed'>[url]URL[/url]</span><br/>");
      print("<span class='typed'>[url=URL]text[/url]</span><br/>");
      print("<div id='line'>&nbsp;</div>\n");
      print("external elements<br/>");
      print("<span class='typed'>[img]URL image[/img]</span><br/>");
      print("<span class='typed'>[youtube]YOUTUBE ID[/youtube]</span><br/>");
      print("<div id='line'>&nbsp;</div>\n");      
      print("MyM element with link<br/><span class='typed'>##structure|id##</span><br/>");
      print("MyM element without link<br/><span class='typed'>###structure|id###</span>");    
      print("    </div>\n");
    }
    
    print("  </div>\n");    
  }

?>

</div>

<div id='foot'>
<?php
// page compilation statistics
$time_end = microtime_float();
$time = $time_end - $time_start;

if (!defined('MYM_CACHE')) {
  print("<p>Page compiled in $time seconds");
  if (defined('MYM_MYSQL')) print (" ($nqueries Queries)");
  else print (" ($nopen File Opened)");
  print(".</p>\n");
}
?>
</div>

</body>
</html>

<?php 
MyMclose(); 
?>
