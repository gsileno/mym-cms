<?php
/*
Instructions:
the form does not have a parameter in the action attribute, 
this means that once the send form button is clicked the same form page is reloaded
But before the form page is reloaded, the captchaCheck.php script is run where
a check is made to see if the correct captcha code was inserted by the user.
If correct, then the field contents which was stored in the result.php document - is sent off.
If NOT correct, the user is re-presented with the reloaded form page
to be able to try again
by Antonio Palermi 2007 www.captcha.biz

if(isset($_POST['scratch_submit']) && isset($_SESSION['pass'])) {
	if(isset($_POST['captcha_input'])){
		if($_SESSION['pass']==$_POST['captcha_input']) {
          unset($_SESSION['pass']);
		} else die;
	} else die;
} 

*/

function captchaCheck() {

  if (isset($_POST['scratch_submit']) && isset($_SESSION['pass'])) {
 	if(isset($_POST['captcha_input'])){
	  if(strtolower($_SESSION['pass']) == strtolower($_POST['captcha_input'])) 
        return true;
      else
        return false;
	} else return false;
  } 
  
  if (!isset($_POST['scratch_submit']))
    if (session('user_id') != UNDEFINED) 
      return true;
    else
      return false;
} 

?>
