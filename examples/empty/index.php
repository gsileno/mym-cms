<?php  

  // load configurations
  require_once('./app/config/config.php');

  // link MyM
  require_once(MYM_RELATIVE_PATH.'/mym.php'); // include MyM

  // boot MyM
  MyMboot(APP_RELATIVE_PATH);

  // start building xHTML page
  htmlhead();
  title(text('MyM test page'));
  css('./test.css');
  
  htmlbody();

  print("<h1>MyM</h1>\n");
  print("<p>:: avant CMS ::<br/><br/>\n");
  print(text('developed by')." <strong>giovanni sileno</strong><br/>");
  print("giovanni |at| mexpro |dot| it</p>\n");
  
  print("<p>".text("Please check the")." <a href='admin'>".text('control panel')."</a>. [login: <em>admin</em> / password: <em>admin</em>] </p>\n");
  
  print("<table>");
  print("<tr><td class='left'>".text('site URI')."</td><td class='right'>".ROOT_URI."</td></tr>\n");
  print("<tr><td class='left'>".text('administrator email')."</td><td class='right'>".MYM_ADMIN_EMAIL."</td></tr>\n");
  print("<tr><td class='left'>".text('MyM Version')."</td><td class='right'>".MYM_VERSION."</td></tr>\n");
  print("<tr><td class='left'>".text('absolute path to MyM')."</td><td class='right'>".MYM_PATH."</td></tr>\n");
  print("<tr><td class='left'>".text('relative path to MyM')."</td><td class='right'>".MYM_RELATIVE_PATH."</td></tr>\n");
  print("<tr><td class='left'>".text('path to structures')."</td><td class='right'>".MYM_STRUCTURES_REALPATH."</td></tr>\n");
  print("<tr><td class='left'>".text('path to modules')."</td><td class='right'>".MYM_MODULES_REALPATH."</td></tr>\n");
  print("<tr><td class='left'>".text('users database')."</td><td class='right'>".MYM_USER_DB."</td></tr>\n");
  print("<tr><td class='left'>".text('not logged user priv')."</td><td class='right'>".MYM_NOT_LOGGED_USER_PRIV."</td></tr>\n");
  print("<tr><td class='left'>".text('standard new user priv')."</td><td class='right'>".MYM_NEW_USER_PRIV."</td></tr>\n");
  print("<tr><td class='left'>".text('admin priv')."</td><td class='right'>".MYM_ADMIN_PRIV."</td></tr>\n");
  
  print("<tr><td class='left'>".text('cache')."</td><td class='right'>");
  if (defined('MYM_CACHE')) print(text("activated"));
  else print(text("not activated"));
  print("</td></tr>\n");  
  if (defined('MYM_CACHE')) {
    print("<tr><td class='left'>".text('cache expires in (sec)')."</td><td class='right'>".MYM_CACHE_EXPIRE_TIME."</td></tr>\n");
    print("<tr><td class='left'>".text('cache path')."</td><td class='right'>".MYM_CACHE_REALPATH."</td></tr>\n");
  }
  
  print("<tr><td class='left'>".text('session expires in (min)')."</td><td class='right'>".MYM_SESSION_EXPIRE_TIME."</td></tr>\n");

  $structures = listfiles(MYM_STRUCTURES_REALPATH);
  print("<tr><td class='left'>".text('structures')."</td>");
    print("<td class='right'>");
  for ($i = 0; $i < count($structures); $i++) {
    print($structures[$i].' ');
  }       
  print("</td></tr>\n");
  
  print("<tr><td class='left'>".text('MySQL')."</td><td class='right'>");
  if (defined('MYM_MYSQL')) print(text('activated'));
  else print(text("not activated"));
  print("</td></tr>\n");  
  if (defined('MYM_MYSQL')) {
    print("<tr><td class='left'>".text('MySQL server')."</td><td class='right'>".MYM_MYSQL_SERVER."</td></tr>\n");
    print("<tr><td class='left'>".text('database')."</td><td class='right'><strong>".MYM_MYSQL_DB. "</strong> (connection test");
    require_once(MYM_PATH."/core/baseMySQL.php");
    if (!testconnect()) print(" <em>".text('failed')."</em>");
    else print(" <em>".text('OK')."</em>");
    print(")</td></tr>\n");
  }
  else
    print("<tr><td class='left'>".text('txtDB path')."</td><td class='right'>".MYM_TXTDB_REALPATH."</td></tr>\n");

  print("<tr><td class='left'>".text('upload path')."</td><td class='right'>".MYM_UPLOAD_REALPATH."</td></tr>\n");

  print("<tr><td class='left'>".text('multilanguage')."</td><td class='right'>");
  if (defined('MYM_LANGUAGES')) print(text("activated"));
  else print(text("not activated"));
  print("</td></tr>\n");
  if (defined('MYM_LANGUAGES')) {
    print("<tr><td class='left'>".text('languages path')."</td><td class='right'>".MYM_LANGUAGES_REALPATH."</td></tr>\n");
    print("<tr><td class='left'>".text('languages')."</td><td class='right'>");
    for ($i=0; $i<count($set_lngcode); $i++) {
      print("  <a href='index.php?lng=".$set_lngcode[$i] ."'");
      if(session('lng') == $set_lngcode[$i]) print(" class='current'");
      print("><img src='".MYM_RELATIVE_PATH."/img/ext_".$set_lngcode[$i].".png' alt='".$set_lng[$i]."' /></a>");
    }
    print("</td></tr>\n");
  }
  
  print("</table>");      
    
  htmlend();

  //////// Functions

  function text($code) {
    if (defined('MYM_LANGUAGES')) {
        return txt($code);
    }
    else return $code;
  }
