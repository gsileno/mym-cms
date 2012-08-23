<?php 

// Distributed as part of "MyM - avant CMS"
// -----------------------------------------------------------------
// You should have received a copy of the GNU Lesser General Public License
// along with this program.  If not, see <http://www.gnu.org/licenses/>.
// -----------------------------------------------------------------   
// (c) Giovanni Sileno 2006, 2010 - giovanni.sileno@mexpro.it

// basic MyM element field
enum('_TEXT',      // a generic text
     '_LONGTEXT',  // a long text
     '_MYMTEXT',   // a long text with MyM preprocessing
     '_EMAIL',     // an email
     '_NUMBER',    // a general integer
     '_FILE',      // a file
     '_IMAGE',     // a file image (.jpg)
     '_VIDEO',     // a file video (.flv)
     '_AUDIO',     // a file audio (.mp3)
     '_DATE',      // a date
     '_ID',        // an id of a MyMelement associated 
     '_LISTID',    // a list of MyMelements associated
     '_FLAG',      // an enumerative value (an index of a given table)
     '_FLAGRADIO', // an enumerative value in a radio list
     '_LNG',       // an enumerative value (an index of a set_lng table, defined in pre.php)
     '_ONDB',      // a structure on which this element is associated (to be used with _ONID)
     '_ONID',      // the id of the element to which this element is associated
     '_OWNER',     // the id of the writer/modifier     
     '_OWNERIP',   // the ip address of the writer/modifier
     '_OWNERLNG',   // the ip address of the writer/modifier     
     '_NOW',       // a date, now() (when recording is performed)
     '_PRIV');     // an enumerative value (privilege 0-16)

// main MyM field scopes
enum('_OWNERFIELD',          // record for the owner user 
     '_DERIVATIONOFFIELD', // record for the root, original element
     '_LANGUAGEFIELD',       // record for the language
     '_VIEWFIELD',           // field to be print with MyMbasicprint (MyMprocess)
     '_DATEFIELD');          // record for the creation (or modification) date
	 
// Type of texts
enum('_CS','_CI'); // Case sentitive / Case insensitive
   
// Type of integers
enum('_SIGNED','_UNSIGNED'); // Signed / Unsigned

global $mysql;
if (!isset($mysql)) $mysql = defined('MYM_MYSQL');

class MyMtype {  
  var $type = UNDEFINED;
  var $obligatory = false;
  
  // default value
  var $defaultvalue = UNDEFINED;
  
  // For TEXT, LONGTEXT, NUMBER
  var $minvalue = UNDEFINED;
  var $maxvalue = UNDEFINED;
  var $minlength = UNDEFINED;
  var $maxlength = UNDEFINED;
  var $collate = _CI;
  var $sign = _UNSIGNED;
  
  // For FLAG
  var $set = UNDEFINED; // name of the static array

  // For IMAGE
  var $maxwidth = UNDEFINED;
  var $maxheight = UNDEFINED; // TODO
  var $minwidth = UNDEFINED;
  var $minheight = UNDEFINED;  
  var $thumbsize = UNDEFINED;
  var $squaredthumb = UNDEFINED;
  
  // For ID/ListID/OnID/Owner
  var $db = UNDEFINED;    // name of db of the element associated to id
  var $what = UNDEFINED;  // data to be shown on select form 
  var $where = "";        // data selection in database

  // Attach Name/Value of the variable on type
  var $name = UNDEFINED;
  // Attach Label for the variable in the input form
  var $label = UNDEFINED;

  var $doubled = false;   // double form needed for check
  var $starred = false;   // input form covered with stars ********

  var $primary = false;   // it has a univocal value in the database

  var $notinformifnew = false;   // do not print in the form if a new object will be created

  var $readpriv = false;
  var $writepriv = false;

  // Constructor
  function MyMtype($type, $name) {
    trace(1, "MyMtype constructor (field $name typed $type)...");
    $this->name = $name;
    $this->type = $type;
  }

  function isStarred() {
    $this->starred = true;  
  }

  function isPrimary() {
    $this->primary = true;  
  }

  function isDoubled() {
    $this->doubled = true;  
  }
  
  function isObligatory() {
    $this->obligatory = true;  
  }

  function defaultValue($value = UNDEFINED) {
    $this->defaultvalue = $value;  
  }
  
  // for all
  function hasPriv($readpriv, $writepriv) { 
    $this->readpriv = $readpriv;    
    $this->writepriv = $writepriv;    
  }

  function notInFormIfNew() {
    $this->notinformifnew = true;  
  }

  function setLabel($label) {
    $this->label = $label;
  }

  // for ID
  function isIdof($db, $what = UNDEFINED, $where = "") { 
    $this->db = strtolower($db);    
    if ($what == UNDEFINED) 
      tracedie("MyMtype > Id / ListId type $db > Sorry, options name link has been not defined. ");
    
    $this->what = $what;
    if ($where != "") 
      $this->where = $where;
  }

  // for ListID
  function isListIdof($db, $what = UNDEFINED, $where = "") { 
    $this->isIdof($db, $what, $where);
  }

  // for FLAG
  function isIndexof($array) { 
    $this->set = $array;    
  }

  // for Number
  function sign($sign) { 
    $this->sign = $sign;    
  }

  // for Text
  function collate($collate) { 
    $this->collate = $collate;    
  }

  // for Images
  function maxHeight($value) {
    $this->maxheight = $value;
  }
  function maxWidth($value) {
    $this->maxwidth = $value;
  }
  function minHeight($value) {
    $this->minheight = $value;
  }
  function minWidth($value) {
    $this->minwidth = $value;
  }  
  function thumbSize($value) {
    $this->thumbsize = $value;
  }
  function squaredThumb($value) {
    $this->squaredthumb = $value;
  }
  
  // for Numbers / Date
  function MinMaxValue($minvalue, $maxvalue) {
    $this->minvalue = $minvalue;
    $this->maxvalue = $maxvalue;
  }

  // for Text/Numbers (digit)
  function MinMaxlength($minlength, $maxlength) {
    $this->minlength = $minlength;
    $this->maxlength = $maxlength;
  }

  //
  // JavascriptCheck()
  //
  
  function JavascriptCheck($priv = UNDEFINED) { 
    trace(1, "MyMtype | JavascriptCheck > ");
    require_once MYM_PATH."/core/baseJavascript.php";

    if (($this->readpriv != false && $this->readpriv > $priv) 
       || ($this->writepriv != false && $this->writepriv > $priv))
       return; 

    $validation = "";
          
    trace_r(1, "MyMtype | JavascriptCheck > object > ", $this);          
          
    switch ($this->type) {
    
      case _TEXT:      
        if ($this->doubled)
          $validation .= InputEquals($this->name, $this->name."2");        
      
      case _MYMTEXT: 
      case _LONGTEXT: 
        if ($this->minlength != UNDEFINED)
          $validation .= InputMinLength($this->minlength, $this->name);
        if ($this->maxlength != UNDEFINED)
          $validation .= InputMaxLength($this->maxlength, $this->name);      
        if ($this->obligatory)
          $validation .= InputNotEmpty($this->name);
        break;

      case _EMAIL:  
        $validation = InputEmail($this->name);
        if ($this->obligatory)
          $validation .= InputNotEmpty($this->name);
        if ($this->doubled)
          $validation .= InputEquals($this->name, $this->name."2");        
        break;
        
      case _NUMBER: 
        $validation = InputNumber($this->name);
        if ($this->minvalue != UNDEFINED)
          $validation .= InputMinValue($this->minvalue, $this->name);        
        if ($this->maxvalue != UNDEFINED)
          $validation .= InputMaxValue($this->maxvalue, $this->name);
        if ($this->minlength != UNDEFINED)
          $validation .= InputMinLength($this->minlength, $this->name);
        if ($this->maxlength != UNDEFINED)
          $validation .= InputMaxLength($this->maxlength, $this->name);          
        if ($this->obligatory)
          $validation .= InputNotEmpty($this->name);
        break;

      case _AUDIO:    
      case _VIDEO:
      case _FILE:    
      case _IMAGE:
        // if ($this->obligatory)      
        //   $validation .= InputNotEmpty("file_".$this->name);
        break;

      case _PRIV:      
        if ($this->obligatory)      
          $validation .= InputDefined($this->name);
        break;

      case _ONDB: 
      case _LNG:
      case _FLAGRADIO:    
      case _FLAG:    
      case _ONID:           
      case _ID:      
      case _OWNER:           
      case _OWNERIP: 
      case _OWNERLNG: 
      
        if ($this->obligatory)
          $validation .= InputDefined($this->name);
        break;

      case _LISTID:
        if ($this->obligatory) {
          $validation .= InputTreeNotEmpty($this->name);      
        }
        $validation .= ConvertTree($this->name);
        break;

      case _DATE: 
      case _NOW:          
        if ($this->obligatory)      
          $validation .= InputNotEmpty($this->name);
        break;
        
      default: tracedie("MyMtype > JavascriptCheck > Sorry, field '".$this->name."' not recognised.");           
    }
    
    trace(1, "MyMtype | JavascriptCheck > $validation");    
    
    return $validation;
  }

  //
  // basicPrintItem()
  // Write the Item.
  //
  function basicPrintItem($value = UNDEFINED, $priv = UNDEFINED, $file = UNDEFINED, $realfile = UNDEFINED, $link = UNDEFINED) {
    global $mysql;
    
    trace(1, "basicPrintItem > Type = ". $this->type .", value = $value ");
	
    if ($value == UNDEFINED || $value == "" || $value == NULL)
      return false;

    if ($priv == UNDEFINED) 
      return false;
    
    if ($priv != UNDEFINED) {
      if ($this->readpriv != false && $this->readpriv > $priv)
        return false;
    }     
        
    switch ($this->type) {
      case _MYMTEXT: 
        $output = MYMprocess($value);
        break;         

      case _TEXT:      
        if ($this->starred)
          $output = "...shh!! :)";
        else
          $output = MyMbasicprocess($value);
	  break;

      case _LONGTEXT: 
          $output = MyMbasicprocess($value);
	  break;
        
     case _FILE:    
        $file = $path."/$value";
        if (file_exists($realpath."/$value"))
          $output .= "( <a href='$file'>Download ".$this->name."</a> )";
        else 
          $output = "(<em>Sorry, here there was a file - not valid filename.</em>)";
        break; 
        
      case _VIDEO:
        $file = ROOT_URI."/".MYM_UPLOAD_PATH."/$value";
        $realfile = MYM_UPLOAD_REALPATH."/$value";
        if (file_exists($realfile)) {
          $output .= "<a href='http://www.macromedia.com/go/getflashplayer'>Get the Flash Player</a> to see this player\n";
          $output .= "<script type='text/javascript'>\n";
      	  $output .= "var FO = {	movie:'".MYM_RELATIVE_PATH."/ext/flashflvplayer/flvplayer.swf',width:'240',height:'140',majorversion:'7',build:'0',bgcolor:'#FFFFFF',\n";
          $output .= "   flashvars:'file=$file&showdigits=false&autostart=false&showfsbutton=true' };\n";
	      $output .= "   UFO.create(	FO, 'player".$this->name."');\n";
          $output .= "</script>\n";
        } 
        else 
          $output = "(<em>Sorry, here there was a video - not valid filename.</em>)";
        break; 

      case _AUDIO:
        $file = ROOT_URI."/".MYM_UPLOAD_PATH."/$value";
        $realfile = MYM_UPLOAD_REALPATH."/$value";
        if (file_exists($realfile)) {
          $output = "<object type='application/x-shockwave-flash' data='".MYM_RELATIVE_PATH."/ext/dewplayer/dewplayer.swf?son=$file' width='200' height='20'>\n";
          $output .= "<param name='movie' value='".MYM_RELATIVE_PATH."/ext/dewplayer/dewplayer.swf?son=$file' /></object>\n";
        } 
        else 
          $output = "(<em>Sorry, here there was an audio object - not valid filename.</em>)";
        break; 
        
      case _IMAGE:
        $realfile = MYM_UPLOAD_REALPATH."/$value";
        $realthumbfile = MYM_UPLOAD_REALPATH."/thumb_$value";
        $realmaxfile = MYM_UPLOAD_REALPATH."/resized_$value";
        $file = ROOT_URI."/".MYM_UPLOAD_PATH."/$value";
        $thumb = ROOT_URI."/".MYM_UPLOAD_PATH."/thumb_$value";
        $max = ROOT_URI."/".MYM_UPLOAD_PATH."/resized_$value";
        
        if (is_file($realfile)) {
          if (is_file($realthumbfile))
            $output = "<img src='$thumb' />\n";
          else if (is_file($realmaxfile))
            $output = "<img src='$max' />\n";
          else {            
            $output = "<img src='$file' />\n";          
          }
        } 
        else 
          $output = "(<em>Sorry, here there was an image here - not valid filename.</em>)";
        break; 

      case _OWNER:           
      case _ID:
        if ($this->db == UNDEFINED || $this->what == UNDEFINED)
          $output = $value;         
        else {
          if ($mysql) {
            $key = keyRead($this->db, $this->what, $value);
            $output = "<a href='index.php?o=".$this->db."&id=".$value."'>$key</a>";
          }
          else 
            $output = $value;   // TO BE CORRECTED
        }
        break;

      case _FLAGRADIO:
      case _FLAG:
        $output = $this->set[$value];
	break;

      case _LNG:  
      case _EMAIL:  
      case _NUMBER:    
      case _ONDB: 
      case _OWNERIP:           
      case _OWNERLNG:           
      case _PRIV:          
      case _ONID:     
      case _LISTID:      
      case _DATE:      
      case _NOW:            
        $output = $value;
        break; 
        
      default: 
        tracedie("MyMelement > BasicPrintItem > Sorry, field '".$this->name."' (".$this->type.") not recognised.");           
    }
    
    return $output;  
  }      
  
  //
  // PrintItem()
  // Write the Item.
  // The result is an item, with a nested descriptive part in a 
  // <span class='left'> tag, and the value.
  //
  function PrintItem($value = UNDEFINED, $priv = UNDEFINED) {
    global $mysql;
    
    trace(3, "PrintItem > Type = ". $this->type .", value = $value, priv = $priv ");
	
    if ($value == UNDEFINED || $value == "" || $value == NULL)
      return false;

    if ($priv == UNDEFINED) 
      return false;
    
    if ($priv != UNDEFINED) {
      trace(1, "PrintItem | Priv : $priv >");
      if ($this->readpriv != false && $this->readpriv > $priv)
        return false;
    }     
    
    if ($this->label == UNDEFINED) 
      $this->label = $this->name;

    switch ($this->type) {
      case _MYMTEXT: 
        
        $output = "<td class='left'>".$this->label."</td> <td class='right'>".MYMprocess($value)."</td>";
        break;         

      case _TEXT:      
        if ($this->starred)
          $value = "<em>...shh!!! :)</em>";
        else {
          
          $value = MyMbasicprocess($value);
        }
        $output = "<td class='left'>".$this->label."</td> <td class='right'>".$value." </td>";
        break; 

      case _LONGTEXT: 
              
        $output = "<td class='left'>".$this->label."</td> <td class='right'>".MyMbasicprocess($value)." </td>";
        break; 
      
     case _FILE:    
        $file = ROOT_URI."/".MYM_UPLOAD_PATH."/$value";
        $output = "<td class='left'>".$this->label."</td>";
        if (is_file(MYM_UPLOAD_REALPATH."/$value")) {
          $output .= "<td class='right'>".$value;
          $output .= "( <a href='$file'>Download</a> )";
          $output .= "</td>\n";
        } else 
          $output .= "<td class='error'><strong>Error</strong> Not valid filename ($value).</td>";
        $output .= "\n";
        break; 
        
      case _VIDEO:
        $file = ROOT_URI."/".MYM_UPLOAD_PATH."/$value";
        $output = "<td class='left'>".$this->label."</td> \n";
        if (is_file(MYM_UPLOAD_REALPATH."/$value")) {
          $output .= "<td class='right'>";
          $output .= "<td id='player".$this->name."'><a href='http://www.macromedia.com/go/getflashplayer'>Get the Flash Player</a> to see this player.</td>\n";
          $output .= "<script type='text/javascript'>\n";
      	  $output .= "var FO = {	movie:'".MYM_RELATIVE_PATH."/ext/flashflvplayer/flvplayer.swf',width:'240',height:'140',majorversion:'7',build:'0',bgcolor:'#FFFFFF',\n";
          $output .= "   flashvars:'file=$value&showdigits=false&autostart=false&showfsbutton=true' };\n";
	      $output .= "   UFO.create(	FO, 'player".$this->name."');\n";
          $output .= "</script>\n";
          $output .="</td>";  
        } 
        else 
          $output .= "<td class='error'><strong>Error</strong> Not valid filename ($value).</td>";        $output .= "\n";          
        break; 

      case _AUDIO:
        $file = ROOT_URI."/".MYM_UPLOAD_PATH."/$value";
        $output = "<td class='left'>".$this->label."</td>\n";
        if (is_file(MYM_UPLOAD_REALPATH."/$value")) {
          $output .= "<td class='right'>";
          $output .= "<object type='application/x-shockwave-flash' data='".MYM_RELATIVE_PATH."/ext/dewplayer/dewplayer.swf?son=$file' width='200' height='20'>\n";
          $output .= "<param name='movie' value='".MYM_RELATIVE_PATH."/ext/dewplayer/dewplayer.swf?son=$file' /></object> <br/> &raquo;&raquo; ".$value." \n";
          $output .="</td>";  
        } 
        else 
          $output .= "<td class='error'><strong>Error</strong> Not valid filename ($value).</td>";        $output .= "\n";          
        $output .= "\n";          
        break; 
        
      case _IMAGE:
        $file = ROOT_URI."/".MYM_UPLOAD_PATH."/$value";
        $thumb = ROOT_URI."/".MYM_UPLOAD_PATH."/thumb_$value";
        $max = ROOT_URI."/".MYM_UPLOAD_PATH."/resized_$value";
        
        $output = "<td class='left'>".$this->label."</td>\n ";  
        if (is_file(MYM_UPLOAD_REALPATH."/$value")) {
          list($width, $height) = getimagesize(MYM_UPLOAD_REALPATH."/$value");
          $output .= "<td class='right'>";
          if (is_file(MYM_UPLOAD_REALPATH."/thumb_$value"))
            $output .= "<a href='$file'><img src='$thumb' /></a> <br/>&raquo;&raquo; ".$value." - ".$width."x".$height."px (thumbnail)\n";
          else if (is_file(MYM_UPLOAD_REALPATH."/resized_$value"))
            $output .= "<a href='$file'><img src='$max' /></a> <br/>&raquo;&raquo; ".$value." - ".$width."x".$height."px (resized)\n";
          else {            
            $output .= "<img src='$file' /> <br/>&raquo;&raquo; ".$value." - ".$width."x".$height."px\n";          
          }
          $output .="</td>";  
        } 
        else 
          $output .= "<td class='error'><strong>Error</strong> Not valid filename ($value).</td>";        $output .= "\n";          
        $output .= "\n";              
        break; 

      case _OWNER:           
      case _ID:
        if ($this->db == UNDEFINED || $this->what == UNDEFINED)
          $output = "<td class='left'>".$this->label."</td> <td class='right'> id ".$value." </td>";
        else {
          if ($mysql) {
            $key = keyRead($this->db, $this->what, $value);
            $output = "<td class='left'>".$this->label."</td>";
            $output .= "<td class='right'><a href='index.php?o=".$this->db."&id=".$value."'>$key</a> (id $value) </td>";
          }
          else 
            $output = "<td class='left'>".$this->label."</td> <td class='right'> id ".$value." </td>"; // TODO
        }
        break;
        
      case _LNG:  
        $output = "<td class='left'>".$this->label."</td> <td class='right'>".$value." </td>";
		break;

      case _FLAG:      
      case _FLAGRADIO:
        if ($this->set != UNDEFINED)
          $val = $this->set[$value];
        else 
          $val = "Undefined.";
        $output = "<td class='left'>".$this->label."</td> <td class='right'>".$val." </td>";
		break;
		
      case _EMAIL:  
      case _NUMBER:    
      case _ONDB: 
      case _OWNERIP: 
      case _OWNERLNG: 
      case _PRIV:          
      case _ONID:     
      case _LISTID:      
      case _DATE:      
      case _NOW:            
        $output = "<td class='left'>".$this->label."</td> <td class='right'>".$value." </td>";
        break; 
        
      default: 
        tracedie("MyMelement > PrintItem > Sorry, field '".$this->name."' (".$this->type.") not recognised.");           
    }
    
    return $output;  
  }    

  //
  // InputForm()
  // Write the input form necessary for an element of the type object.
  // $value is the first value printed in the form
  // The result is a list item, with a nested descriptive part in a 
  // <span class='left'> tag, and the input form.
  //
  
  function InputForm($value = UNDEFINED, $priv = UNDEFINED) {
    global $txt, $mysql;
    trace(3, "MyMtype | InputForm (type = ".$this->name.", value = ".$value.")> ");
   
    if ($value == '' || $value == NULL) 
      $value = UNDEFINED;
      
    if ($value == UNDEFINED)
      $value = $this->defaultvalue;
   
    $readonly = "";
    if ($priv != UNDEFINED) {
      trace(1, "MyMtype | Priv : $priv >");
      if ($this->readpriv != false && $this->readpriv > $priv)
        return;
      else if ($this->writepriv != false && $this->writepriv > $priv)
        $readonly = "class='readonly' readonly";
    }     
    
    $obligatory = "";
    $obligatory2 = "";
    if ($this->obligatory) {
      $obligatory = "<strong>";
      $obligatory2 = "</strong>";
    }

    if ($this->label == UNDEFINED) 
      $this->label = $this->name;
      
    switch ($this->type) {
      case _TEXT:      
        if ($value == UNDEFINED)
          $value = "";      
        if ($this->starred) $tt = "password";
        else $tt = "text";
		
        $form = "<td class='left'>$obligatory".$this->label."$obligatory2</td><td class='right'> <input type='$tt' name='".$this->name."' value=".'"'.Unicode2Txt($value).'"'." $readonly /></td>\n";
        if ($this->doubled) 
          $form .= "</tr><tr><td class='left'>".$txt['typeagain']."</td><td class='right'> <input type='$tt' name='".$this->name."2' value='".Unicode2Txt($value)."' $readonly /></td>\n";
        break;
		
      case _MYMTEXT: 
        if ($value == UNDEFINED)
          $value = "";
        		  
        $form = "<td class='left'>$obligatory".$this->label."$obligatory2</td><td class='right'> <textarea name='".$this->name."' $readonly cols=54 rows=25>".Unicode2Txt($value)."</textarea></td>\n";
        break;
        
      case _LONGTEXT: 
        if ($value == UNDEFINED)
          $value = "";

        $form = "<td class='left'>$obligatory".$this->label."$obligatory2</td><td class='right'>";
        $form .= "<textarea name='".$this->name."' $readonly cols=54 rows=6>".Unicode2Txt($value)."</textarea>";
        // if bbcode editor
        // $form .= "<script>Init(".$this->name.",54,6,\"".Unicode2Txt($value)."\"); </script>";
        $form .="</td>\n";
        break;

      case _EMAIL:  
        if ($value == UNDEFINED)
          $value = "";
      
        $form = "<td class='left'>$obligatory".$this->label."$obligatory2</td> <td class='right'><input type='text' name='".$this->name."' $readonly value='".$value."' /></td>\n";
        if ($this->doubled) 
          $form .= "</tr><tr><td class='left'>".$txt['typeagain']."</td> <td class='right'><input type='text' name='".$this->name."2' $readonly value='".$value."' /></td>\n";
        break;
      
      case _PRIV: 
      case _NUMBER:    
        if ($value == UNDEFINED)
          $value = "";
      
        $form = "<td class='left'>$obligatory ".$this->label."$obligatory2</td> <td class='right'><input type='text' name='".$this->name."' $readonly value='".$value."' /></td>\n";
        break;
        
      case _VIDEO:
      case _AUDIO:
      case _FILE:    
        // <input type="hidden" name="MAX_FILE_SIZE" value="30000" />
        $form = "";
        if ($value != UNDEFINED && $value != NULL) {
          $file = MYM_UPLOAD_REALPATH."/".$value;
          if (!is_file($file))
            $form = "<td class='left'>Loaded File</td><td class='right'><strong>Sorry, the filename recorded ($file) is not valid.</strong></td></tr><tr>\n";
          else { 
            $form .= "<td class='left'>Loaded File</td> ";
            $form .= "<td class='right'><strong>$value</strong></td>\n";
	    $form .="</tr><tr>";
            // $form .= "<td class='right'><input type='text' size='34' name='".$this->name."' $readonly value='".$value."'></td>\n";
            // $form .= "</tr><tr><td class='left'>Check to delete</td><td class='right'> <input type='checkbox' name='del_".$this->name."' value='yes'></td></tr><tr>\n";
            // $form .= "<input type='hidden' name='oldfile_".$this->name."' value='$value' />"; */
            
          } 
        }
        $form .= "<td class='left'>$obligatory";
        if ($value != UNDEFINED) $form .= "New ".$this->label; else $form .= $this->label; 
        $form .= "$obligatory2</td> <td class='right'><input type='file' name='file_".$this->name."' /></td> \n";
        break;

      case _IMAGE:
        // <input type="hidden" name="MAX_FILE_SIZE" value="30000" />
        
        $form ='';
        if ($value != UNDEFINED && $value != NULL) {
          $file = ROOT_URI."/".MYM_UPLOAD_PATH."/$value";
          $thumb = ROOT_URI."/".MYM_UPLOAD_PATH."/thumb_$value";
          $max = ROOT_URI."/".MYM_UPLOAD_PATH."/resized_$value";
          if (!is_file(MYM_UPLOAD_REALPATH."/".$value))
            $form .= "<td class='left'>Loaded File</td><td class='right'><strong>Sorry, the filename recorded (".MYM_UPLOAD_REALPATH."/".$value.") is not valid.</strong></td></tr><tr>\n";
          else { 
            list($width, $height) = getimagesize(MYM_UPLOAD_REALPATH."/$value");
            $form .= "<td class='left'>Loaded Image</td><td class='right'>";
            if (is_file(MYM_UPLOAD_REALPATH."/thumb_".$value))
              $form .= "<a href='$file'><img src='$thumb' alt='".$this->label."'/></a> <br/>&raquo;&raquo; ".$value." - ".$width."x".$height."px (<em>thumbnail</em>)\n";
            else if (is_file(MYM_UPLOAD_REALPATH."/resized_".$value))
              $form .= "<a href='$file'><img src='$max' alt='".$this->label."'/></a> <br/>&raquo;&raquo; ".$value." - ".$width."x".$height."px (<em>resized</em>)\n";
            else
              $form .= "<img src='$file' alt='".$this->label."' /> <br/>&raquo;&raquo; ".$value." - ".$width."x".$height."px \n";          
            
            $form .="</td>";  

	    $form .="</tr><tr>";
            // $form .= "</tr><tr><td class='left'>&nbsp;</td><td class='right'><input type='text' size='34' name='".$this->name."' class='readonly' readonly value='".$value."'></td>\n";            
            // $form .= "</tr><tr><td class='left'>Check to delete</td><td class='right'> <input type='checkbox' name='del_".$this->name."' value='yes'></td></tr><tr>\n";
            // $form .= "<input type='hidden' name='oldfile_".$this->name."' value='$value' />";
	    
          } 
        }
        $form .= "<td class='left'>$obligatory";
        if ($value != UNDEFINED) $form .= "New ".$this->label; else $form .= $this->label; 
        $form .= "$obligatory2</td> <td class='right'><input type='file' name='file_".$this->name."' /></td> \n";
        break;

      case _OWNERLNG:
      case _LNG:  
        $form = "<td class='left'>$obligatory".$this->label."$obligatory2</td><td class='right'> ";
        if ($value == UNDEFINED)
		  $value = session('lng');
		$form .= InputSelectOption($this->name, $value, $GLOBALS["set_lng"],  $GLOBALS["set_lngcode"]);
        $form .= "</td>\n";
        break; 

      case _FLAG:      
        $form = "<td class='left'>$obligatory".$this->label."$obligatory2</td><td class='right'> ";
        $form .= InputSelectOption($this->name, $value, array_values($this->set), array_keys($this->set));
        $form .= "</td>\n";
        break; 

      case _FLAGRADIO:      
        $form = "<td class='left'>$obligatory".$this->label."$obligatory2</td><td class='right'> ";
        $form .= InputRadioOption($this->name, $value, $this->set);
        $form .= "</td>\n";
        break; 
      
      case _ID:
      case _OWNER:       
        $form = "<td class='left'>$obligatory".$this->label."$obligatory2</td>\n";

        if ($this->db == UNDEFINED) {
          $form .= "<td class='right'> Associated database not valid. </td>";
        }
        else {
          require_once(MYM_STRUCTURES_REALPATH."/".strtolower($this->db).".php");

          $elem = new $this->db();  
          $what = $this->what;

          $unidfield = $elem->Field(_DERIVATIONOFFIELD);
          if ($unidfield) {            
	    $elem->MyMread($value);
	    $unid = $elem->$unidfield;
          }          

          list($list, $n, $ntot) = $elem->MyMadvlist($this->where, "ID");
          $set = $values = NULL; 
	     
          $form .= "<td class='right'>";
          if ($list != NULL) {
            
            $found = false;
            for ($i = 0; $i < $n; $i++) {
            
              $id = $list[$i];
              $elem->MyMread($id, $what);        
              $set[$i] = $id." - ".$elem->$what;
              $values[$i] = $elem->id; 
	      
              if ($elem->Field(_DERIVATIONOFFIELD)) {
              
                // TODO: UNID can be not charged
                if ($unid == $elem->$unidfield)
                  $found = true;
              }          
              else {
                if ($value == $elem->id)
                  $found = true;
              }
            }
            
            if (!$found && $value != UNDEFINED) {
              $set[] = "..orphelined..";
              $values[] = $value;
            }            
            
            $form .= InputSelectOption($this->name, $value, $set, $values, ($readonly != ""));

          }
          else $form .= "&nbsp;";
          $form .= "</td>\n";          
        }           
        break; 
        
      case _ONDB: 
        $set = $values = listfiles(MYM_PATH_STRUCTURES); 

        $form = "<td class='left'>$obligatory".$this->label."$obligatory2</td>\n <td class='right'>";
        $form .= InputSelectOption($this->name, $value, $set, $values, ($readonly != ""), "onChange='sendRequest(\"".MYM_RELATIVE_PATH."/core/dynamicSelect.php?db=\" + selectedvalue(\"".$this->name."\") + \"&what=\",handleRequest);'");
        $form .= "</td>\n";        
        break;    

      case _ONID: 

        $form = "<td class='left'>$obligatory".$this->label."$obligatory2</td> ";

        if ($this->db == UNDEFINED) {
          if ($value == UNDEFINED)
            $form .= "<td class='right'><span id='dynamicBox'>Associated database not valid.</span></td>";
          else
            $form .= "<td class='right'><span id='dynamicBox'>id n.$value</span></td>";
        }
       	else {
          require_once(MYM_PATH_STRUCTURES."/".$this->db.".php");
          
          $elem = new $this->db();  
          $what = $this->what;
          $result = $elem->MyMlist("id, ".$what, $this->where, "ID");
          $list = $result[0];
          $set = $values = NULL;  
        
          if ($list != NULL) {
            $form .= "<td class='right'><span id='dynamicBox'>";        
            for ($i = 0; $i < $result[2]; $i++) {
              $object = $list[$i];
              $set[$i] = $object->$what;
              $values[$i] = $object->id;
              
              if ($value == $object->id)
                $found = true;
            }
            
            if (!$found) {
              $set[] = $txt["..orphelined.."];
              $values[] = $value;
            }            
            
            $form .= InputSelectOption($this->name, $value, $set, $values, ($readonly != ""));
            $form .= "</span></td>";
          }
        }
        
        $form .= "\n";
        break;
    
      case _LISTID:      
        if ($value == UNDEFINED) $value = post("list_".$this->name);
        $srcvalues = explode(", ", $value);
              
        $form = "<td class='left'>$obligatory".$this->label."$obligatory2</td>\n";
        
        if ($this->db == UNDEFINED) {
          $form .= "<td class='right'> Associated database not valid. </td>\n";
        }
        else {
          MyMincludestructure($this->db);
                   
          $set = $values = NULL;         
          
          $elem = new $this->db();  
          $what = $this->what;
          list($list, $n, $tot) = $elem->MyMlist($this->where);
                    
          if ($list != NULL) {

            $srcset = NULL;
            while ($id = array_pop($list)) {

              $elem->MyMread($id);
              $set[] = $elem->id." - ".$elem->$what;
              $values[] = $elem->id;
              
              // fill the original listid with associated contents
              for ($i = 0; $i < count($srcvalues); $i++) {
                if ($elem->id == $srcvalues[$i]) {
                  $srcset[$i] = $elem->id." - ".$elem->$what;
                }
              }
            }
    
            if ($value != UNDEFINED) {
              for ($i = 0; $i < count($srcvalues); $i++) {
                if (!array_key_exists($i, $srcset)) {
                  $srcset[$i] = $elem->id." - ".$txt["..orphelined.."];
                }
              }
            }

            $listname = $this->name;                    
            
            $form .= "<td class='right'>\n";        
            $form .= "  <table>\n";
            $form .= "  <tr><td class='nestedside'>\n";        
            $form .= InputSelectOption("src_".$listname, UNDEFINED, $set, $values, ($readonly != ""), "multiple size=10 onDblClick=\"copySelectedOptions(this.form['src_$listname'],this.form['$listname'],true)\"", false);
            $form .= "  </td>\n";

            $form .= "  <td class='nestedcenter'>\n";        
            $form .= "  <input class='littlebutton' type='button' name='right' value='&raquo;' onclick='copySelectedOptions(this.form[\"src_$listname\"],this.form[\"$listname\"],true)'><br/>\n";
            $form .= "  <input class='littlebutton' type='button' name='right' value='All &raquo;' onclick='copyAllOptions(this.form[\"src_$listname\"],this.form[\"$listname\"],true)'><br/>\n";
            $form .= "  <input class='littlebutton' type='button' name='left' value='&laquo;' onclick='removeSelectedOptions(this.form[\"$listname\"])'><br/>\n";
            $form .= "  <input class='littlebutton' type='button' name='left' value='All &laquo;' onclick='removeAllOptions(this.form[\"$listname\"])'>\n";
            $form .= "  </td>\n";        
            
            $form .= "  <td class='nestedside'>\n";
                        
            $form .= InputSelectOption($listname, UNDEFINED, $srcset, $srcvalues, ($readonly != ""), "multiple size=10 onDblClick=\"removeSelectedOptions(this.form['$listname'])\"", false, true);
            // $form .= "<select id='$listname' name='$listname' multiple size=10 onDblClick=\"removeSelectedOptions(this.form['$listname'])\">\n";
            // $form .= "<select name='target_$listname' multiple size=10 onDblClick=\"moveSelectedOptions(this.form['target_$listname'],this.form['$listname'],true,this.form['movepattern_$listname'].value)\">\n";
            // $form .= "</select>\n";
            $form .= "  </td></tr>\n";
            $form .= "  </table>\n";
            $form .= "</td>\n";

            $form .= "<input type='hidden' id='list_$listname' name='list_$listname' value=''>\n";
            // $form .= "<td class='left'>&nbsp;</td><td class='right' colspan='3'>\n";
            // $form .= "Do not allow moving of options matching pattern: ";
            // $form .= "<input type='text' NAME='movepattern_$listname' value=''>";
          } else $form .= "<td class='right'> Associated database empty. </td>\n";
        } 
           
        break;
             
      case _OWNERIP: 
        $form = "<td class='left'>$obligatory".$this->label."$obligatory2</td>  <td class='right'><input type='text' name='".$this->name."' $readonly value='".$value."' />\n";
        break;
        
      case _NOW:
        $value = date("d/m/Y, H:i");
      case _DATE:
        if ($value == UNDEFINED || $value == NULL) 
          $value = "";

        $form = "<td class='left'>$obligatory".$this->label."$obligatory2</td>"." <td class='right'><input type='text' name='".$this->name."' $readonly id='sel".$this->name."' value='".$value."'>";
        if ($readonly == "") { 
          $form .= "<input type='reset' value='...'";
          $form .= " onclick=\"return showCalendar('sel".$this->name."', '%d/%m/%Y, %H:%M', '24', true);\">";
        }           
        $form .= "</td>";
        break; 
        
      default: tracedie("MyMtype > InputForm > Sorry, field '".$this->name."' not recognised.");           
    }
    
    trace(1, "MyMtype | InputForm > $form");    
    
    return $form;
  }  
  
  //
  // MySQLtype
  //
  
  function MySQLtype() {
    // trace(1, "MyMtype | MySQLtype > ");

    switch ($this->type) {
      case _TEXT:      
      case _MYMTEXT: 
      case _LONGTEXT: 
      case _EMAIL: 
        $mysqltype = "TEXT CHARACTER SET utf8 ";
        /* if ($this->collate == _CS)
          $mysqltype .= "COLLATE utf8_unicode_cs ";
        else
          $mysqltype .= "COLLATE latin1_general_cs ";        
        break; */
        break;
        
      case _NUMBER:    
        $mysqltype = "INT ";
        if ($this->sign == _SIGNED)
          $mysqltype .= "SIGNED ";
        else
          $mysqltype .= "UNSIGNED ";        
        break;

      case _VIDEO:
      case _AUDIO:
      case _FILE:    
      case _IMAGE: 
      case _ONDB: 
      case _OWNERIP:  
      case _OWNERLNG:
      case _LNG:  
        $mysqltype = "TEXT CHARACTER SET utf8 ";break;
      case _NOW:
      case _DATE:      
        $mysqltype = "DATETIME ";break;
      case _OWNER:                   
      case _ONID:     
      case _ID:      
        $mysqltype = "INT UNSIGNED ";break;
      case _PRIV:            
      case _FLAG:      
        $mysqltype = "INT UNSIGNED ";break;
      case _LISTID:      
        $mysqltype = "TEXT CHARACTER SET utf8 ";break;
      
      default: tracedie("MyMtype > MySQLtype > Sorry, field '".$this->name."' not recognised.");           
    }
    
    // if ($this->obligatory) 
    //   $mysqltype .= "NOT NULL ";

    trace(1, "MyMtype | MySQLtype > $mysqltype");    
    
    return $mysqltype;
  }
  
  function fromMySQL($value) {
    trace(1, "MyMtype | fromMySQL > $value");

    switch ($this->type) {
      case _TEXT:      
      case _MYMTEXT: 
      case _LONGTEXT:         
      case _EMAIL: 
      case _NUMBER:    
      case _VIDEO:
      case _AUDIO:
      case _FILE:    
      case _IMAGE: 
      case _ONDB: 
      case _OWNERIP:    
      case _OWNERLNG:
      case _LNG:  
      case _OWNER:                   
      case _ONID:     
      case _ID:      
      case _PRIV:            
      case _FLAG:      
      case _LISTID:      
        break;

      case _NOW:      
      case _DATE:      
       if ($value != UNDEFINED && $value != NULL) { 
          trace(1, " > MyMtype > ".$this->name." > mysql input data: $value");        
          list($y, $mon, $d, $h, $min) = sscanf($value, "%d-%d-%d %d:%d:00");          
          trace(1, " > MyMtype > ".$this->name." > italian format: $h:$min, $d/$mon/$y");
          $value = sprintf("%02d/%02d/%04d, %02d:%02d", $d,$mon, $y, $h, $min);    
          trace(1, " > MyMtype > ".$this->name." > calendar format: $value");       
        }
      break;
      
      default: tracedie("MyMtype > fromMySQL > Sorry, field '".$this->name."' not recognised.");           
    }

    return $value;
  }  

  function fromTxtDB($value) {
    trace(1, "MyMtype | fromTxtDB > $value");
    switch ($this->type) {
      case _TEXT:      
      case _MYMTEXT: 
      case _LONGTEXT: 
      case _EMAIL: 
      case _NUMBER:    
      case _VIDEO:
      case _AUDIO:
      case _FILE:    
      case _IMAGE: 
      case _ONDB: 
      case _OWNERIP:  
      case _OWNERLNG:      
      case _LNG:  
      case _OWNER:                   
      case _ONID:     
      case _ID:      
      case _PRIV:            
      case _FLAG:      
      case _FLAGRADIO:   
      case _LISTID:      
        break;

      case _NOW:      
      case _DATE:         
       if ($value != UNDEFINED && $value != NULL) { 
          $value = date("d/m/Y, H:i", $value);   
        }
      break;
      
      default: tracedie("MyMtype > fromTxtDB > Sorry, field '".$this->name."' not recognised.");           
    }

    return $value;
  }  

  function fromPost($value) {
    trace(1, "MyMtype | fromPost > $value");

    switch ($this->type) {
      case _TEXT:      
      case _MYMTEXT: 
      case _LONGTEXT: 
        $value = stripslashes($value);
        break;
        
      case _EMAIL: 
      case _NUMBER:    
      case _VIDEO:
      case _AUDIO:
      case _FILE:    
      case _IMAGE: 
      case _ONDB: 
      case _OWNERIP:      
      case _OWNERLNG:      
      case _LNG:  
      case _OWNER:                   
      case _ONID:     
      case _ID:      
      case _PRIV:            
      case _FLAG:      
      case _LISTID:      
      case _NOW:      
      case _DATE:      
      case _FLAGRADIO:
        break;
      
      default: tracedie("MyMtype > fromPost > Sorry, field '".$this->name."' not recognised.");           
    }

    return $value;
  }
}