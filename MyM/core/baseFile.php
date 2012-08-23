<?php

// Distributed as part of "MyM - avant CMS"
// -----------------------------------------------------------------
// You should have received a copy of the GNU Lesser General Public License
// along with this program.  If not, see <http://www.gnu.org/licenses/>.
// -----------------------------------------------------------------   
// (c) Giovanni Sileno 2006, 2010 - giovanni.sileno@mexpro.it

// -------------------------------------------------------------
//  return the list of files in a given path, 
//  without the extension
// -------------------------------------------------------------
function listfiles($path) {
  
  if (!is_dir($path)) {
    print("Sorry: [". $path ."] is not a valid directory.");
    return null;
  }
  else {
    $files = array();
    $dhandle = opendir("$path");
    while ($file = readdir($dhandle)) {
      if (($file == ".") || ($file == "..") || ($file == "") || ($file[0] == ".")) {}
      else {
        $file = preg_replace("/(\w+).(\w+)$/", "$1", $file);
        array_push($files, $file); // TO BE CORRECT
      }
    }
    sort($files);
    reset($files);
  }
  
  return $files; 
}


function listfileswithext($path) {
  
  if (!is_dir($path)) {
    print("Sorry: [". $path ."] is not a valid directory.");
    return null;
  }
  else {
    $files = array();
    $dhandle = opendir("$path");
    while ($file = readdir($dhandle)) {
      if (($file == ".") || ($file == "..") || ($file == "") || ($file[0] == ".")) {}
      else {
        array_push($files, $file); // TO BE CORRECT
      }
    }
    sort($files);
    reset($files);
  }
  
  return $files; 
}
