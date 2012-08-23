<?php

// this code's first version is by Dave Child, and it has been downloaded from
// http://www.ilovejackdaniels.com/php/caching-output-in-php/

// --------- cacheOn
// cachedir : Directory to cache files in (keep outside web root)
// cachetime : Seconds to cache files for
// cacheext : Extension to give cached files (usually cache, htm, txt)

function cacheOn($cachedir = './cache', $cachetime = 600, $cacheext = 'cache') {

  // Ignore List
  $ignore_list = array(
     //'rss.php'
  );

  // Script
  $page = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']; // Requested page
  $cachefile = $cachedir.'/'.md5($page).'.'.$cacheext;                 // Cache file to either load or create

  $ignore_page = false;
  for ($i = 0; $i < count($ignore_list); $i++) {
    $ignore_page = (strpos($page, $ignore_list[$i]) !== false) ? true : $ignore_page;
  }

  $cachefile_created = ((@file_exists($cachefile)) and ($ignore_page === false)) ? @filemtime($cachefile) : 0;
  @clearstatcache();

  // Show file from cache if still valid
  if (time() - $cachetime < $cachefile_created) {
    @readfile($cachefile);
    exit();
  }

  // If we're still here, we need to generate a cache file
  ob_start();

}

function cacheOff($cachedir = './cache', $cacheext = 'cache') {

  // Script
  $page = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']; // Requested page
  $cachefile = $cachedir.'/'.md5($page).'.'.$cacheext;                 // Cache file to either load or create

  // Now the script has run, generate a new cache file
  $fp = @fopen($cachefile, 'w');

  // save the contents of output buffer to the file
  @fwrite($fp, ob_get_contents());
  @fclose($fp);

  ob_end_flush(); 
  
}

function deleteCache($cachedir = './cache') {

  if ($handle = @opendir($cachedir)) {
    while (false !== ($file = @readdir($handle))) {
      if ($file != '.' and $file != '..') {
        echo $file . ' deleted.<br>';
        @unlink($cachedir . '/' . $file);
      }
    }
    @closedir($handle);
  }
}

?>