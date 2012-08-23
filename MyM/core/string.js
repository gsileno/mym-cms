function checkemail(string) {
  var EmailAt = false;
  var EmailPeriod = false; 
  for (i = 0;  (!EmailAt || !EmailPeriod) && i < string.length;  i++) {  
    ch = string.charAt(i);  
    if (ch == '@')  
      EmailAt = true;        
    if (ch == '.')  
      EmailPeriod = true;  
    }  
  return !(EmailAt && EmailPeriod);  
}  

function checkfloat(string) {  
  var checkOK = '0123456789.';  
  j = 0;  
  for (i = 0; (j < checkOK.length) && (i < string.length);  i++) {  
    ch = string.charAt(i);  
    for (j = 0; (ch != checkOK.charAt(j)) && j < checkOK.length;) j++;
  }  
  if (j == checkOK.length) return true;  
  else return false;  
}

function checkinteger(string) {  
  var checkOK = '0123456789';  
  j = 0;  
  for (i = 0; (j < checkOK.length) && (i < string.length);  i++) {  
    ch = string.charAt(i);  
    for (j = 0; (ch != checkOK.charAt(j)) && j < checkOK.length;) j++;
  }  
  if (j == checkOK.length) return true;  
  else return false;  
}

function checkpercent(string) {  
  var checkOK = '0123456789%';  
  j = 0;  
  for (i = 0; (j < checkOK.length) && (i < string.length);  i++) {  
    ch = string.charAt(i);  
    for (j = 0; (ch != checkOK.charAt(j)) && j < checkOK.length;) j++;
  }  
  if (j == checkOK.length) return true;  
  else return false;  
}

function selectedvalue(listName) {  
  optionsList = document.getElementById(listName).options;
  return optionsList[optionsList.selectedIndex].value;
}