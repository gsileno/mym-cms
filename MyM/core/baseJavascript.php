<?php
/*
   File: baseJavascript.php | (c) Giovanni Sileno 2006, 2007
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
   This file contains some Javascript functions used in MyM.
*/

function ArrayInit() {
?>
<script language="javascript" type="text/javascript">
<!--
// ArrayAddbySelect: 
// listName is a string id identifying the select object
// selectedArray is a javascript array where record the selected options
function ArrayAddbySelect(listName, nameArray, selectedArray) {
  
  optionsList = document.getElementById(listName).options;
  textbox = document.getElementById(listName + 'Box');

  for (x = 0; x < optionsList.length; x++) {
    if (optionsList[x].selected) {
      found = false;
      first = true;
      for (y in selectedArray) {
        first = false;
        if (selectedArray[y] == x) {
          found = true;
          break;
        }
      }
      if (!found) {
        selectedArray.push(x);  
        if (!first)
          textbox.innerHTML += ', ';
        else
          textbox.innerHTML = '';
        textbox.innerHTML += '<a href="javascript:void(0)" onclick="javascript:ArrayDeletebyValue(\''+listName+'\',\''+nameArray+'\','+nameArray+','+x+')">'+ optionsList[x].text + '</a>';
      }
    }
  }
} 

// ArrayDeletebySelect: 
// listName is a string id identifying the select object
// selectedArray is a javascript array where record the selected options
function ArrayDeletebySelect(listName, nameArray, selectedArray) {
  
  optionsList = document.getElementById(listName).options;
  textbox = document.getElementById(listName + 'Box');

  found = false;
  for (x = 0; x < optionsList.length; x++) {
    if (optionsList[x].selected) {
      for (y in selectedArray) {
        if (selectedArray[y] == x) {
          found = true;
          selectedArray.splice(y,1);
          break;
        }
      }
    }
  }
  
  if (found) {
    textbox.innerHTML = '';
    for (y in selectedArray) {
      if (y>0) 
        textbox.innerHTML += ', ';  
      x = selectedArray[y];
      textbox.innerHTML += '<a href="javascript:void(0)" onclick="javascript:ArrayDeletebyValue(\''+listName+'\',\''+nameArray+'\','+nameArray+','+x+')">'+ optionsList[x].text + '</a>';
    }
  }  
} 

// ArrayDeletebyValue: 
// listName is a string id identifying the select object
// Value is the value to be deleted
// selectedArray is an array where the selected options are recorded
function ArrayDeletebyValue(listName, nameArray, selectedArray, value) {

  optionsList = document.getElementById(listName).options;
  textbox = document.getElementById(listName + 'Box');

  found = false;

  for (y in selectedArray) {
    if (selectedArray[y] == value) {
      found = true;
      selectedArray.splice(y,1);
      break;
    }
  }

  if (found) {
    textbox.innerHTML = '';
    for (y in selectedArray) {
      if (y>0) 
        textbox.innerHTML += ', ';  
      x = selectedArray[y];
      textbox.innerHTML += '<a href="javascript:void(0)" onclick="javascript:ArrayDeletebyValue(\''+listName+'\',\''+nameArray+'\','+nameArray+','+x+')">'+optionsList[x].text + '</a>';
    }
  }
} 
-->
</script>
<?php
}

function SwapInit() {
?>
<script language="javascript" type="text/javascript">
<!-- Swapping Layer Management

function MM_findObj(n, d) { //v4.0
  var p,i,x;  if(!d) d=document; if((p=n.indexOf("?"))>0&&parent.frames.length) {
    d=parent.frames[n.substring(p+1)].document; n=n.substring(0,p);}
  if(!(x=d[n])&&d.all) x=d.all[n]; for (i=0;!x&&i<d.forms.length;i++) x=d.forms[i][n];
  for(i=0;!x&&d.layers&&i<d.layers.length;i++) x=MM_findObj(n,d.layers[i].document);
  if(!x && document.getElementById) x=document.getElementById(n); return x;
}

var vis = new Array();

function swap_Layer(n) {
	if (!(layer = MM_findObj('swap' + n))) return;
	if (vis[n] == 'hide') {
		layer.style.display = 'block';
		vis[n] = 'show';
	} else {
		layer.style.display = 'none';
		vis[n] = 'hide';
	}
}
-->
</script>
<?php
}

function SwapHidden($n) {
  $javascript = "vis['$n'] = 'hide';\n"
               ."document.write('<li id=\"swap$n\" style=\"display: none;\">');\n"; 
  return $javascript;
}

function SwapShow($n) {
  $javascript = "vis['$n'] = 'show';\n"
               ."document.write('<li id=\"swap$n\" style=\"display: block;\">');\n"; 
  return $javascript;
}

function SwapNoScript($n) {
  $javascript = "<li id='swap$n' style='display: block;'>\n";
  return $javascript;  
}

// Javascript String check functions
function CheckEmailFunction() {
  $javascript = "function checkemail(string) {\n"
               ."  var EmailAt = false;\n"
               ."  var EmailPeriod = false;\n"
               ."  for (i = 0;  (!EmailAt || !EmailPeriod) && i < string.length;  i++) {\n"
               ."    ch = string.charAt(i);\n"
               ."    if (ch == '@')\n"
               ."      EmailAt = true;\n"      
               ."    if (ch == '.')\n"
               ."      EmailPeriod = true;\n"
               ."  }\n"
               ."  return !(EmailAt && EmailPeriod);\n"
               ."}\n";
  return $javascript;  
}

function CheckNumberFunction() {
  $javascript =  "function checknumber(string) {\n"
                 ."return !isNaN(string);\n"
                /* ."  var checkOK = '0123456789.';\n"
                ."  j = 0;\n"
                ."  for (i = 0; (j < checkOK.length) && (i < string.length);  i++) {\n"
                ."    ch = string.charAt(i);\n"
                ."    for (j = 0; (ch != checkOK.charAt(j)) && j < checkOK.length;  j++);\n"
                ."  }\n"
                ."  if (j == checkOK.length) return true;\n"
                ."  else return false;\n" */
                ."}\n";
               
  return $javascript;  
}

// Javascript Check inline functions
// $field, $field1, $field2 are the name of the input form, 
// $errormessage is the message shown if the condition is not satisfied
// $n is the number of the form in the document

function InputNotEmpty($field, $errormessage = UNDEFINED, $n = 0) {
  if ($errormessage == UNDEFINED)
    $errormessage = "[".txt($field, 'utf8')."]: ".txt('jsempty', 'utf8') ;
  $javascript  =  "  if (document.forms[".$n."].".$field.".value.length == 0) {\n"
                 .'    $error += "'.$errormessage.'"+"\n";'."\n"
                 ."  }\n";
  return $javascript;  
}

function InputChecked($field, $errormessage = UNDEFINED, $n = 0) {
  if ($errormessage == UNDEFINED)
    $errormessage = "[".txt($field, 'utf8')."]: ".txt('jsnotchecked', 'utf8') ;
  $javascript  =  "  if (!document.forms[".$n."].".$field.".checked) {\n"
                 .'    $error += "'.$errormessage.'"+"\n";'."\n"
                 ."  }\n";
  return $javascript;
}

function InputRadioButtonNotEmpty($field, $errormessage = UNDEFINED, $n = 0) {
  if ($errormessage == UNDEFINED)
    $errormessage = "[".txt($field, 'utf8')."]: ".txt('jsradiobuttonempty', 'utf8') ;
  $javascript  = '  var found = false;'."\n"
                .'  var radioObj = document.forms['.$n.'].'.$field.';'."\n"
                .'  var radioLength = radioObj.length;'."\n"
                .'  if(radioLength == undefined) {'."\n"
                .'    if (radioObj.checked)'."\n"
  		.'      found = true;'."\n"
                .'  }'."\n"
	        .'  for(var i = 0; i < radioLength; i++) {'."\n"
		.'    if(radioObj[i].checked) '."\n"
		.' 	found = true;'."\n"
                .'  }'."\n"
                .'  if (!found) {'."\n"
                .'    $error += "'.$errormessage.'"+"\n";'."\n"
                ."  }\n";
  return $javascript;
}

function InputDefined($field, $errormessage = UNDEFINED, $n = 0) {  
  if ($errormessage == UNDEFINED)
    $errormessage = "[".txt($field, 'utf8')."]: ".txt('jsvalidvalue', 'utf8');
  $javascript  =  "  if (document.forms[".$n."].".$field.".value == ".UNDEFINED.") {\n"
                 .'    $error += "'.$errormessage.'"+"\n";'."\n"
                 ."  }\n";
  return $javascript;  
}

function InputMinLength($min, $field, $errormessage = UNDEFINED, $n = 0) {
  if ($errormessage == UNDEFINED)
    $errormessage = "[".txt($field, 'utf8')."]: Sorry, the field must be long $min characters at least.";
  $javascript  =  "  if (document.forms[".$n."].".$field.".value.length < ".$min.") {\n"
                 .'    $error += "'.$errormessage.'"+"\n";'."\n"
                 ."  }\n";
  return $javascript;  
}

function InputMaxLength($max, $field, $errormessage = UNDEFINED, $n = 0) {
  if ($errormessage == UNDEFINED)
    $errormessage = "[".txt($field, 'utf8')."]: Sorry, the field must be long $max characters at most.";
  $javascript  =  "  if (document.forms[".$n."].".$field.".value.length > ".$max.") {\n"
                 .'    $error += "'.$errormessage.'"+"\n";'."\n"
                 ."  }\n";
  return $javascript;  
}

function InputEquals($field1, $field2, $errormessage = UNDEFINED, $n = 0) {
  if ($errormessage == UNDEFINED)
    $errormessage = "[".txt($field1, 'utf8')."], [".txt($field2)."]: ".txt('jsequal', 'utf8');
  $javascript  =  "  if (document.forms[".$n."].".$field1.".value != document.forms[".$n."].".$field2.".value) {\n"
                 .'    $error += "'.$errormessage.'"+"\n";'."\n"
                 ."  }\n";
  return $javascript;  
}

function InputTreeNotEmpty($field, $errormessage = UNDEFINED, $n = 0) {
  if ($errormessage == UNDEFINED)
    $errormessage = "Sorry, the field $field cannot be empty.";    

  $javascript  = "  if (document.forms[".$n."].".$field.".options.length == 0) {\n"
                 .'    $error += "'.$errormessage.'"+"\n";'."\n"
                 ."  }\n";
  return $javascript;  
}

function ConvertTree($field, $n = 0) {

  $javascript  =  "  createstringfromoptions('$field');\n";
  return $javascript;  
}

function InputEmail($field, $errormessage = UNDEFINED, $n = 0) {
  if ($errormessage == UNDEFINED)
    $errormessage = "[".txt($field, 'utf8')."]: ".txt('jsemail', 'utf8');
  $javascript  =  "  if (document.forms[".$n."].".$field.".value.length > 0 && checkemail(document.forms[".$n."].".$field.".value)) {\n"
                 .'    $error += "'.$errormessage.'"+"\n";'."\n"
                 ."  }\n";
  return $javascript;  
}

function InputNumber($field, $errormessage = UNDEFINED, $n = 0) {
  if ($errormessage == UNDEFINED)
    $errormessage = "[".txt($field, 'utf8')."]: ".txt('jsnumber', 'utf8');
  $javascript  =  "  if (document.forms[".$n."].".$field.".value.length > 0 && checknumber(document.forms[".$n."].".$field.".value)) {\n"
                 .'    $error += "'.$errormessage.'"+"\n";'."\n"
                 ."  }\n";
  return $javascript;  
}

function CharsInTextArea($maxchar) {
?>
<script language="javascript" type="text/javascript">
<!-- Limited Number of Characters in TextArea Management
var maxChars = <?php print($maxchar); ?>
var StrLen;
var Content;

function CharsCount(Target, counter) {
    
    Content = Target.value;
    StrLen = Content.length;
	if (StrLen > maxChars ) {
	  Content.substring(0, maxChars)
      Target.value = Content;
      StrLen = Content.length;
	}
	counter.value = maxChars-StrLen;
}
-->
</script>
<?php
}

function PageRestart() {
?>
<script language="javascript" type="text/javascript">
<!-- Page Restart Management
function go()
{
  form = document.forms[0].;
  rubriqueid = formulario.options[formulario.selectedIndex].value;
  if (rubriqueid) location.href = 'index.php?a=reply&rubriqueid=' + rubriqueid;
}
-->
</script>
<?php
}
