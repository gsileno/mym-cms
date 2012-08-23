<?php

// Distributed as part of "MyM - avant CMS"
// -----------------------------------------------------------------
// You should have received a copy of the GNU Lesser General Public License
// along with this program.  If not, see <http://www.gnu.org/licenses/>.
// -----------------------------------------------------------------   
// (c) Giovanni Sileno 2006, 2010 - giovanni.sileno@mexpro.it

  function p($text) {
    print("$text");
  }

  function ptxt($key, $encoding = false, $tounicode = false) {
    print(txt($key, $encoding, $tounicode));
  }

  function txt($key, $encoding = false, $tounicode = false) {
    MyMinclude('core/MyMprocess');
    
    global $txt;
    if (array_key_exists($key, $txt)) {
      if (!$encoding) return($txt[$key]);
      else { 
        if (!$tounicode) return (Unicode2Txt($txt[$key], $encoding));
        else return (Txt2Unicode($txt[$key], $encoding));
      }
    }
    else {
      return($key);
    }
  }

  function printTime($date) {
    list($d, $mon, $y, $h, $min) = sscanf($date, "%02d/%02d/%04d, %02d:%02d");
    printf("%d:%02d", $h, $min);
  } 

  function printDate($date) {
    list($d, $mon, $y, $h, $min) = sscanf($date, "%02d/%02d/%04d, %02d:%02d");
    print($d.' '.month($mon).' '.$y);
  } 

  function DateTime($date) {
    return sscanf($date, "%02d/%02d/%04d, %02d:%02d");
  }

  function printDateTime($date) {
    list($d, $mon, $y, $h, $min) = sscanf($date, "%02d/%02d/%04d, %02d:%02d");
    print($d.' '.month($mon).' '.$y.", ");
    printf("%d:%02d", $h, $min);
  } 

  function printMonth($date) {
    list($d, $mon, $y, $h, $min) = sscanf($date, "%02d/%02d/%04d, %02d:%02d");
    print(month($mon).' '.$y);
  } 
  
  function year($date) {
    list($d, $mon, $y, $h, $min) = sscanf($date, "%02d/%02d/%04d, %02d:%02d");
    return $y;
  }
  
  function month($m) {
  
      switch ($m) {
        case 1: return txt('January');
        case 2: return txt('February');
        case 3: return txt('March');
        case 4: return txt('Avril');
        case 5: return txt('May');
        case 6: return txt('June');
        case 7: return txt('July');
        case 8: return txt('August');
        case 9: return txt('September');
        case 10: return txt('October');
        case 11: return txt('November');
        case 12: return txt('December');
      }  
  }
