<?php

// Distributed as part of "MyM - avant CMS"
// -----------------------------------------------------------------
// You should have received a copy of the GNU Lesser General Public License
// along with this program.  If not, see <http://www.gnu.org/licenses/>.
// -----------------------------------------------------------------   
// (c) Giovanni Sileno 2006, 2010 - giovanni.sileno@mexpro.it

error_reporting(E_ALL);

// -------------------------------------------------------------
//  return the real time for page compilation statistics
// -------------------------------------------------------------
function microtime_float() {
   list($usec, $sec) = explode(" ", microtime());
   return ((float)$usec + (float)$sec);
}

require_once("baseConstants.php");
require_once("baseVariables.php");
require_once("baseString.php");
require_once("baseTrace.php");
require_once("basePrint.php");
require_once("baseHtml.php");
require_once("baseJavascript.php");
require_once("baseFile.php");

require_once(MYM_RELATIVE_PATH."/tools/captcha.php");

if (!function_exists('ceiling') )
{
  function ceiling($number, $significance = 1) {
    return ( is_numeric($number) && is_numeric($significance) ) ? (ceil($number/$significance)*$significance) : false;
  }
}





