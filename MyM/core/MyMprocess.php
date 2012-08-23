<?php
/*
   File: MyMprocess.php | (c) Giovanni Sileno 2006, 2007
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
   This file contains string traitment functions.
   
   ATTENTION: this file must be in the UTF8 encoding on some servers.
*/

define("MYM_PROCESS_TRACE", 0);

function newline2br($string, $br = "<br/>") {
  $pattern[] = "/\n/";
  $replace[] = $br;
  $string = preg_replace($pattern, $replace, $string);
  
  return $string;
}

function br2newline($string, $newline = "\n") { 
    
  $pattern[] = '/\r/i';
  $pattern[] = '/<br>/i';
  $pattern[] = '/<br\/>/i';
  $pattern[] = '/<br \/>/i';
  $replace[] = '';
  $replace[] = $newline;
  $replace[] = $newline;
  $replace[] = $newline;    

  $string = preg_replace($pattern, $replace, $string); 
  return $string;

}

function MySQLprotection($string) {
  $pattern[] = "/'/i";
  $replace[] = "\'"; 

  return preg_replace($pattern, $replace, $string); 
}

// Translation of latin/peculiar chars into HTML unicode values
// http://www.gdesign.it/pages/howto/articoli/entcar/entcar.php
function Txt2Unicode($string, $encoding = 'utf8', $protection = false) {

  trace(MYM_PROCESS_TRACE + 1, " Txt2Unicode > input string : $string");

  if (!is_string($string))
    return $string;

  $string = br2newline($string);

  list($replaces, $patterns) = conversions($encoding, $protection);
  
  $patterns = array_map('adddelimiters', $patterns);

  $string = preg_replace($patterns, $replaces, $string);

  $string = newline2br($string);

  trace(MYM_PROCESS_TRACE + 1, " Txt2Unicode > output string : $string");

  return $string;
}

function adddelimiters($string)
{
    return("/".$string."/");
}

function conversions($encoding = 'utf8', $protection = false) {
  $Unicodes = array("&hellip;", "&rdquo;", "&ldquo;",
                    "&laquo;", "&raquo;", "&ndash;", "&acute;",
                    "&middot;", "&sup1;", "&ordm;", "&agrave;", "&Agrave;",
                    "&egrave;", "&Egrave;", "&eacute;", "&Eacute;", "&ograve;",
                    "&ugrave;", "&ocirc;", "&igrave;", "&euro;");

  if ($protection) $Unicodes = array_merge(array("&lt;", "&gt;"/*, "&quot;", "&amp;", "&#039;"*/), $Unicodes);
  
  if ($encoding == '8bit')
    $TxtChars = array('…', '”', '“', 
                      '«', '»', '–', '´',
                      '·', '¹', 'º', 'à', 'À',
                      'è', 'È', 'é', 'É', 'ò',
                      'ù', 'ô', 'ì', '€');
  else if ($encoding == 'utf8')
    $TxtChars = array('â€¦', 'â€', 'â€œ', 
                      'Â«', 'Â»', 'â€“', 'Â´',
                      'Â·', 'Â¹', 'Âº', 'Ã ', 'Ã€',
                      'Ã¨', 'Ãˆ', 'Ã©', 'Ã‰', 'Ã²',
                      'Ã¹', 'Ã´', 'Ã¬', 'â‚¬');
  else
    tracedie(" > conversions > Sorry. Encoding not known.");

  if ($protection) $TxtChars = array_merge(array('<', '>' /*, '"', '&', "'"*/), $TxtChars);

  return array($Unicodes, $TxtChars);
  
}

// newlive true to convert XHTML <br/> to \n
// protection should be true for text input (to avoid conflict with '), false for textarea 
function Unicode2HTML($string, $encoding = 'utf8', $protection = false) {

  trace(MYM_PROCESS_TRACE + 1, " Unicode2HTML > input string : $string");

  if (!is_string($string))
    return $string;

  if (strlen($string) == 0)
    return $string;

  list($patterns, $replaces) = conversions($encoding);

  $patterns = array_map('adddelimiters', $patterns);

  $string = preg_replace($patterns, $replaces, $string); 
  
  trace(MYM_PROCESS_TRACE + 1, " Unicode2HTML > output string: $string");
  
  return $string;
}

// newlive true to convert XHTML <br/> to \n
// protection should be true for text input (to avoid conflict with '), false for textarea 
function Unicode2Txt($string, $encoding = 'utf8', $protection = false) {

  trace(MYM_PROCESS_TRACE + 1, " Unicode2Txt > input string : $string");

  $string = Unicode2HTML($string, $encoding, $protection);
  
  $string = br2newline($string);

  trace(MYM_PROCESS_TRACE + 1, " Unicode2txt > output string: $string");

  return $string;
}

function HTML2bbcode ($string) {

  // links
  $patterns[] = "/<a style=\"color:#cc0000\" href='(.*)'>(.*)<\/a>/U";  
  $patterns[] = "/<a style=\"color:#cc0000\" href=\"(.*)\">(.*)<\/a>/U";  
  $patterns[] = "/<a href='(.*)'>(.*)<\/a>/U";
  $patterns[] = "/<a href=\"(.*)\">(.*)<\/a>/U";
  $replaces[] = "[url=$1]$2[/url]";
  $replaces[] = "[url=$1]$2[/url]";
  $replaces[] = "[url=$1]$2[/url]";
  $replaces[] = "[url=$1]$2[/url]";

  // strong texts
  $patterns[] = "/<strong>/U";
  $patterns[] = "/<\/strong>/U";
  $replaces[] = "[b]";
  $replaces[] = "[/b]";

  // em texts
  $patterns[] = "/<em>/U";
  $patterns[] = "/<\/em>/U";
  $replaces[] = "[i]";
  $replaces[] = "[/i]";

  $string = preg_replace($patterns, $replaces, $string); 

}

function Unicode2TxtDB($string) {
  trace(MYM_PROCESS_TRACE + 1, " Unicode2TxtDB > string: ", $string);

  // field separator in txt
  $patterns[] = "/||/";
  $replaces[] = "";
  $string = preg_replace($patterns, $replaces, $string);
  return $string;
}


function Txt2TxtDB($string) {
  trace(MYM_PROCESS_TRACE + 1, " Txt2TxtDB > string: ", $string);

  $string = Txt2Unicode($string);
  $string = Unicode2TxtDB($string);

  return $string;
}

function TxtDB2Unicode($string) {
  trace(MYM_PROCESS_TRACE + 1, " TxtDB2Unicode > string: ", $string);

  return $string;
}

function TxtDB2Txt($string) {
  trace(MYM_PROCESS_TRACE + 1, " TxtDB2Txt > string: ", $string);

  $string = TxtDB2Unicode($string);
  $string = Unicode2Txt($string);
      
  return $string;
}

function MyMcode2HTML($text, $tag = false, $emoticon = false) {        
  trace(MYM_PROCESS_TRACE + 1, " MyMcode2HTML > text: ", $text);
  
  $imgpath = MYM_RELATIVE_PATH."/ext/tango/16x16";
  
  $suche = array('/\[class=\'(.+?)\'\](.+?)\[\/class\]/i',
                 '/\[class="(.+?)"\](.+?)\[\/class\]/i',
                 '/\[class=(.+?)\](.+?)\[\/class\]/i', 
                 '/\[c\/\]/i',
                 '/\[c\]/i'                 
                 );  
  
  if ($tag) {  
    $code = array('<span class="$1" > (div class $1) $2 (end div)</span>',         
                  '<span class="$1" > (div class $1) $2 (end div)</span>',         
                  '<span class="$1" > (div class $1) $2 (end div)</span>',  
                  '&para;',
                  '&para;'
                 );                                                        
  } else {
    $code = array('<span class="$1" > $2 </span>',         
                  '<span class="$1" > $2 </span>',          
                  '<span class="$1" > $2 </span>',    
                  '',
                  ''
                 );   
  }
  $text = preg_replace($suche, $code, $text);  
  
  
  if ($emoticon) {
    $suche = array('/\:\)/i',
                 '/\;\)/i',
                 '/\:D/i',
                 // '/\:p/i',
                 '/\:\'\(/i' 
                 );
        
    $code = array('<img class="icon" src="'.$imgpath.'/emotes/face-smile.png" alt=":)" />',
                '<img class="icon" src="'.$imgpath.'/emotes/face-wink.png" alt=";)" />',
                '<img class="icon" src="'.$imgpath.'/emotes/face-grin.png" alt=":D" />',
                // '<img src="'.$imgpath.'/emotes/tounge.gif" alt=":p" />',
                '<img class="icon" src="'.$imgpath.'/emotes/face-crying.png" alt=":\'(" />' 
                );
        
    $text = preg_replace($suche, $code, $text);
  }
    
  $text = bbcode2HTML($text);  
  
  return $text;
  
}

function bbcode2HTML($text) {        
  trace(MYM_PROCESS_TRACE + 1, " bbcode2HTML > text: ", $text);

  $imgpath = MYM_RELATIVE_PATH."/ext/tango/16x16";
    
  $suche = array('/(?<!\\\)\*(.+?)(?<!\\\)\*/i', 
                 '/(?<!\\\)\+(.+?)(?<!\\\)\+/i',
                 "/\\\\\*/i", 
                 "/\\\\\+/i",
                 '/\[h\](.+?)\[\/h\]/i',
                 '/\[h1\](.+?)\[\/h1\]/i',                    
                 '/\[h2\](.+?)\[\/h2\]/i',
                 '/\[b\](.+?)\[\/b\]/i',
                 '/\[i\](.+?)\[\/i\]/i',
                 //'/\[u\](.+?)\[\/u\]/i',
                 '/\[u\](.+?)\[\/u\]/i',
                 '/\[u=\'(.+?)\'\](.+?)\[\/u\]/i',
                 '/\[u="(.+?)"\](.+?)\[\/u\]/i',
                 '/\[u=(.+?)\](.+?)\[\/u\]/i',
                 '/\[url\](.+?)\[\/url\]/i',
                 '/\[url=\'(.+?)\'\](.+?)\[\/url\]/i',
                 '/\[url="(.+?)"\](.+?)\[\/url\]/i',
                 '/\[url=(.+?)\](.+?)\[\/url\]/i',
                 '/\[mail\](.+?)\[\/mail\]/i',
                 '/\[mail=\'(.+?)\'\](.+?)\[\/mail\]/i',
                 '/\[mail="(.+?)"\](.+?)\[\/mail\]/i',
                 '/\[mail=(.+?)\](.+?)\[\/mail\]/i',
                 // '/\[file(.+?)\|(.+?)\]/i',

                 '/\[hr\]/i',
                 '/\[hr\/\]/i',                  
                 '/\[img\](.+?)\[\/img\]/i',
                 '/\[youtube\](.+?)\[\/youtube\]/i'         
                 // '/\[key\](.+?)\[\/key\]/ie',
                 // '/\[quote\](.+?)\[\/quote\]/is',
                 // '/\[color=(.+?)\](.+?)\[\/color\]/i'
                 );
        
  $code = array('<strong>$1</strong>', // for astragali site
                '<em>$1</em>',         // for astragali site
                '*',
                '+',
                '<span class="header">$1</span>',         // for astragali site
                '<h1>$1</h1>',
                '<h2>$1</h2>',
                '<strong>$1</strong>',
                '<em>$1</em>',
                //'<u>$1</u>',
                '<a href="$1">$1</a>',
                '<a href="$1">$2</a>',
                '<a href="$1">$2</a>',
                '<a href="$1">$2</a>',
                '<a href="$1" class="link" target="_blank">$1</a>',
                '<a href="$1" class="link" target="_blank">$2</a>',
                '<a href="$1" class="link" target="_blank">$2</a>',
                '<a href="$1" class="link" target="_blank">$2</a>',
                '<a href="mailto:$1" class="link">$1</a>',
                '<a href="mailto:$1" class="link">$2</a>',
                '<a href="mailto:$1" class="link">$2</a>',
                '<a href="mailto:$1" class="link">$2</a>',
                // '<a href="view.php?id=$1" class="link">$2</a>',
                '<hr/>',
                '<hr/>',                
                '<img src="$1" />',   
                '<object width="425" height="344"><param name="movie" value="http://www.youtube.com/v/$1&hl=en&fs=1"></param><param name="allowFullScreen" value="true"></param><embed src="http://www.youtube.com/v/$1&hl=en&fs=1" type="application/x-shockwave-flash" allowfullscreen="true" width="425" height="344"></embed></object>'
                //'$test->create_serial("$1")',
                //'<br /><span class="quote">$1</span><br />',
                //'<span style="color: $1;">$2</span>'
                );
        
  $text = preg_replace($suche, $code, $text);
  return $text;
}

function MyMcode2txt($text) {
  trace(MYM_PROCESS_TRACE + 1, " MyMcode2txt > text: ". $text);

  $text = br2newline($text);
  $suche = array('/(?<!\\\)\*(.+?)(?<!\\\)\*/i', 
                 '/(?<!\\\)\+(.+?)(?<!\\\)\+/i',
                 "/\\\\\*/i", 
                 "/\\\\\+/i",
                 '/\[h\](.+?)\[\/h\]/i',
                 '/\[h1\](.+?)\[\/h1\]/i',                    
                 '/\[h2\](.+?)\[\/h2\]/i',
                 '/\[b\](.+?)\[\/b\]/i',
                 '/\[i\](.+?)\[\/i\]/i',
                 //'/\[u\](.+?)\[\/u\]/i',
                 '/\[u\](.+?)\[\/u\]/i',
                 '/\[u=\'(.+?)\'\](.+?)\[\/u\]/i',
                 '/\[u="(.+?)"\](.+?)\[\/u\]/i',
                 '/\[u=(.+?)\](.+?)\[\/u\]/i',
                 '/\[url\](.+?)\[\/url\]/i',
                 '/\[url=\'(.+?)\'\](.+?)\[\/url\]/i',
                 '/\[url="(.+?)"\](.+?)\[\/url\]/i',
                 '/\[url=(.+?)\](.+?)\[\/url\]/i',
                 '/\[mail\](.+?)\[\/mail\]/i',
                 '/\[mail=\'(.+?)\'\](.+?)\[\/mail\]/i',
                 '/\[mail="(.+?)"\](.+?)\[\/mail\]/i',
                 '/\[mail=(.+?)\](.+?)\[\/mail\]/i',                 
                 '/\[file(.+?)\|(.+?)\]/i',
                 '/\[c\/\]/i',
                 '/\[c\]/i',
                 '/\[hr\]/i',
                 '/\[hr\/\]/i',                  
                 '/\[img\](.+?)\[\/img\]/i',                 
                 '/\[class=\'(.+?)\'\](.+?)\[\/class\]/i',
                 '/\[class="(.+?)"\](.+?)\[\/class\]/i',
                 '/\[class=(.+?)\](.+?)\[\/class\]/i',  
                 '/\[youtube\](.+?)\[\/youtube\]/i'
                 // '/\[key\](.+?)\[\/key\]/ie',
                 // '/\[quote\](.+?)\[\/quote\]/is',
                 // '/\[color=(.+?)\](.+?)\[\/color\]/i'
                 );

  $code = array('*$1*', 
                '$1',
                '*',
                '+',                
                '*$1*',
                '***** $1 *****',                
                '*** $1 ***',                
                '*$1*',
                '$1',
                //'<u>$1</u>',
                '$1',
                '$2: $1',
                '$2: $1',
                '$2: $1',
                '$1',
                '$2: $1',
                '$2: $1',
                '$2: $1',
                '$1',
                '$1',
                '$1',
                '$1',                
                '$2: $1',
                '',
                '',
                "\n".'-----------------------------------'."\n",
                "\n".'-----------------------------------'."\n", 
                '',
                '$2',         
                '$2',         
                '$2',   
                '$1'
                //'$test->create_serial("$1")',
                //'<br /><div class="quote">$1</div><br />',
                //'<span style="color: $1;">$2</span>'
                );

  $text = preg_replace($suche, $code, $text);
  return $text;
}

function MyMbasicprocess($string) {
  trace(MYM_PROCESS_TRACE + 1, " MyMbasicprocess > string: ", $string);

  $string = MyMcode2HTML($string);

  return $string;
}

function MyMprocess($string, $makelink = "makelink") {

  trace(MYM_PROCESS_TRACE + 3, " MyMprocess > string: ", $string);

  $string = bbcode2HTML($string);

  // Changing for processes without links
  $pattern = "/###(.*)###/U";
  $matches = preg_match_all($pattern, $string, $out);
  $tokens = $out[1];
  $patterns = $out[0];

  trace(MYM_PROCESS_TRACE + 2, " MyMprocess > tokens: ", $tokens);

  $replaces = NULL;

  for ($i = 0; $i<count($tokens); $i++) {

    list($db, $id) = explode("|", $tokens[$i]);
	
    $dbfile = MYM_STRUCTURES_REALPATH."/$db.php";
    $patterns[$i] = "/###$db\|$id###/";    
    
    if (is_file($dbfile)) {
      MyMincludemodule($db);
      $modulename = "mod".capitalize($db);
      if (class_exists($modulename))
        $elem = new $modulename();
      else
        $elem = new $db();
        
	  if ($elem->MyMread($id))
	    $replaces[] = $elem->MyMprocessprint(false);
      else
        $replaces[] = "<p><span class='error'><em>Warning</em> $db n. $id not found. </span></p>";
    }
    else {
      $replaces[] = "<p><span class='error'><em>Warning</em> Database $db not known. </span></p>";    
    }
  }    

  trace_r(MYM_PROCESS_TRACE + 2, " MyMprocess > patterns without links: ", $patterns);
  trace_r(MYM_PROCESS_TRACE + 2, " MyMprocess > replaces without links: ", $replaces);
  
  $string = preg_replace($patterns, $replaces, $string);

  // Changing for processes with link
  $pattern = "/##(.*)##/U";
  $matches = preg_match_all($pattern, $string, $out);
  $tokens = $out[1];
  $patterns = $out[0];

  trace_r(MYM_PROCESS_TRACE + 2, " MyMprocess > tokens: ", $tokens);
  
  $replaces = NULL;
  
  for ($i = 0; $i<count($tokens); $i++) {
	
    list($db, $id) = explode("|", $tokens[$i]);

	$dbfile = MYM_STRUCTURES_REALPATH."/$db.php";
    $patterns[$i] = "/##$db\|$id##/";    
    
    if (is_file($dbfile)) {
      MyMincludemodule($db);
      $modulename = "mod".capitalize($db);
      if (class_exists($modulename))
        $elem = new $modulename();
      else
        $elem = new $db();
        
	  if ($elem->MyMread($id))
	    $replaces[] = $elem->MyMprocessprint(true, $makelink);
      else
        $replaces[] = "<p><span class='error'><em>Warning</em> $db n. $id not found. </span></p>";
    }
    else {
      $replaces[] = "<p><span class='error'><em>Warning</em> Database $db not known. </span></p>";    
    }
  }    

  trace_r(MYM_PROCESS_TRACE + 2, " MyMprocess > patterns with links: ", $patterns);
  trace_r(MYM_PROCESS_TRACE + 2, " MyMprocess > replaces with links: ", $replaces);
  
  $string = preg_replace($patterns, $replaces, $string);
    
  return $string;
}

?>