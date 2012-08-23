<?php

// Distributed as part of "MyM - avant CMS"
// -----------------------------------------------------------------
// You should have received a copy of the GNU Lesser General Public License
// along with this program.  If not, see <http://www.gnu.org/licenses/>.
// -----------------------------------------------------------------   
// (c) Giovanni Sileno 2006, 2010 - giovanni.sileno@mexpro.it

//  simple enumerative function
function enum()
{
  $i=0;
  $ARG_ARR = func_get_args();
  if (is_array($ARG_ARR))
  {
   foreach ($ARG_ARR as $CONSTANT)
   {
     define ($CONSTANT, $i++);
   }
  }
}

// for PHP < 5.1
if (!function_exists('property_exists')) {
  function property_exists($class, $property) {
    if (is_object($class))
      $class = get_class($class);

    return array_key_exists($property, get_class_vars($class));
  }
}

// -----------------------------------
//  constants
// -----------------------------------

  define('UNDEFINED', '-1');              // undefined value

  /* find revision from Subversion */
  $rev = 0;
  $revString = '$Rev $'; 
  if (preg_match('/: ([0-9]+) \$/', $revString, $matches)) {
    $rev = $matches[1];
  }

  define("MYM_VERSION", "0.6.".$rev);          // MyM Version

  // Possible actions
  enum('_READ', '_WRITE', '_DELETE', '_READOWN', '_WRITEOWN', '_DELETEOWN'); 
  // Type of action allowed for a user (none, on own elements, on all the elements)
  enum('_NONE', '_OWN', '_ALL');

