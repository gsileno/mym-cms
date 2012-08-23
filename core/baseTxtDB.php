<?php

// Distributed as part of "MyM - avant CMS"
// -----------------------------------------------------------------
// You should have received a copy of the GNU Lesser General Public License
// along with this program.  If not, see <http://www.gnu.org/licenses/>.
// -----------------------------------------------------------------   
// (c) Giovanni Sileno 2006, 2010 - giovanni.sileno@mexpro.it

   function testtxtdbconnect() {
     // TODO: Implement
   }
   
   // Check if a given txtDB has been charged before, if not it charges it
   function OpenDB($name, $path = MYM_TXTDB_REALPATH) {
     global $nopen, $listdb;

     if ($listdb == null) $listdb = array();
     
     require_once(MYM_PATH."/core/txtDB.php");   

     trace(1, " > OpenDB (name = ".$name.", path = ".$path.")> ");
     trace_r(2, " > OpenDB > listdb :",$listdb);
   
     if (!array_key_exists($name, $listdb)) {
       $listdb[$name] = new Txttable($name, $path, true, UNDEFINED, "||");
       $listdb[$name]->openTable();
       $nopen ++;
     }
    
     trace_r(1, " > OpenDB > listdb :",$listdb);
     return $listdb[$name];
   }

?>