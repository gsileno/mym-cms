<?php
/*
   File: MyMrss.php | (c) Giovanni Sileno 2006, 2007
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
   This file contains the RSS handling functions.
*/

// this code's first version has been downloaded from
// http://forum.html.it/forum/showthread.php

  header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");              // Data passata
  header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT"); // sempre modificato
  header("Cache-Control: no-store, no-cache, must-revalidate");  // HTTP/1.1
  header("Cache-Control: post-check=0, pre-check=0", false);
  header("Pragma: no-cache");                          // HTTP/1.0 

  include('../ext/makeRSS/makeRSS.php'); 
  
  require_once("./admin/config.php");
  
  require_once("../MyM/core/baseMyM.php"); // include MyM 
  MyMboot("../MyM");
  
  function xmlentities($string, $quote_style=ENT_COMPAT)
  {
    $trans = get_html_translation_table(HTML_ENTITIES, $quote_style);

    foreach ($trans as $key => $value)
      $trans[$key] = '&#'.ord($key).';';

    return strtr($string, $trans);
  } 

  $r = new MakeRSS('Leitmotiv', 'http://www.leitmotivonline.net', 'News');
  
  require_once(MYM_PATH_MODULES."/news.php");
  $news = new modNews();  
  
  list($listid, $n, $tot) = $news->MyMadvlist("", "date desc", $limit);
  while ($listid != NULL) {
    $id = array_shift($listid);
    $news->MyMread($id);
    
    $titolo = $news->title;
    $titolo = str_replace("\"", "''", $titolo);
    $abs = $news->content;
    $abs = str_replace("\"","''",$abs);

    //$titolo = xmlentities($titolo);
    //$abs = xmlentities($abs);

	$r->AddArticle("$titolo", "index.php?s=news&amp;id=$id", $abs, "leitmotivonline.net");
  }

  $r->Output();
?>