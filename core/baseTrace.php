<?php

// Distributed as part of "MyM - avant CMS"
// -----------------------------------------------------------------
// You should have received a copy of the GNU Lesser General Public License
// along with this program.  If not, see <http://www.gnu.org/licenses/>.
// -----------------------------------------------------------------   
// (c) Giovanni Sileno 2006, 2010 - giovanni.sileno@mexpro.it

// debugging traces print functions
function trace($level, $string) {
  if ($level >= MYM_DEBUG_TRACE) {
    echo "<pre><strong>MYM</strong> [trace] $string</pre> \n";  
  }
}

function trace_r($level, $string, $array) {
  if ($level >= MYM_DEBUG_TRACE) {
    echo "<pre><strong>MYM</strong> [trace] $string \n";
    print_r($array);
    print "</pre>";
  }
}

function tracedie($string) {
  echo "<pre><strong>MYM</strong> [fatal] $string</pre> \n";  
  die();
}

function show_error($error) {
  print("<p class='error'>$error</p>");
  die();
}

function show_warning($error) {
  print("<p class='error'>$error</p>");
}

function log_message($filename, $message) {

  $log_path = './log';
  
  if (!is_dir($log_path))
    if (!mkdir($log_path)) {
      show_warning("log_message > The directory [$log_path] can't be created.");
      return;
    }
      
  $file = $log_path."/".$filename.".log";  
  
  if (is_file($file)) if (!is_writable($file)) {
    show_warning("log_message > The file [$file] is not writable.");
    return;
  } 
    
  if (!$fhandle = fopen($file, "a")) {
     show_warning("log_message > The file [$file]  can't be opened."); return;
  }
  
  if (flock($fhandle, LOCK_EX)) { // do an exclusive lock     
    if (fwrite($fhandle, date("[Y-m-d H:i]")." $message\n") === FALSE) {
      show_warning("(log_message) > The file [$file] can't be written.");
	  flock($fhandle, LOCK_UN); // release the lock
      fclose($fhandle);
      return;
    }
    
    flock($fhandle, LOCK_UN); // release the lock
    fclose($fhandle);        
    return;
  } else {
    show_warning("(log_message) >  The file [$file] can't be locked.");
    return;
  }
}


