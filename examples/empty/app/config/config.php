<?php
/*
   File: config.php | (c) Giovanni Sileno 2006, 2011
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
   This files contains general settings to customize MyM. 
*/

  // time zone requirements // obligatory since PHP 5.3
  date_default_timezone_set('Europe/Rome');
  
  // MyM configuration

  // directories definition
  
  // root is the directory where is located the index.php served by the URI
  // app is the directory containing the structures/modules/definition + txtdb
  // mym is the directory containing mym :)
  
  define('ROOT_RELATIVE_PATH', '../examples/empty'); // from mym to root dir
  define('APP_RELATIVE_PATH', './app'); // from root to app dir
  if (!defined('MYM_RELATIVE_PATH')) define('MYM_RELATIVE_PATH', '../../MyM'); // from root to mym dir  
    
  // app URI
  define("ROOT_URI", "http://localhost/test/empty");
  // app admin email (for errors/warnings)
  define("MYM_ADMIN_EMAIL", "moi@mym.org");  
  // messages default language
  define("MYM_DEFAULT_LANGUAGE", "en");

  // Multilanguage
  // multilanguage - If it is disactivated, it must be commented
  define("MYM_LANGUAGES", "1");
  // mym multilanguage static words path (relative to app directory)
  define("MYM_LANGUAGES_PATH", "./lng");

  // mym structures definition path (relative to app directory)
  define("MYM_STRUCTURES_PATH", "./structures/sources");  
  // mym modules definition path (relative to app directory)
  define("MYM_MODULES_PATH", "./structures/modules"); 
  // mym plugins definition path (relative to app directory)
  // define("MYM_PLUGINS_PATH", "../plugins");    

  // Extentions to MyM
  // mym extension definition path (relative to mym directory)
  define("MYM_EXT_PATH", "./ext");  
  
  // Debug
  // level of debugging traces to be shown
  // 4 session, get, post, files variables
  // 3 start function - Input variables, return variables if possible
  // 2 operating variables
  // 1 very annoying messages :p
  define("MYM_DEBUG_TRACE", "9");      
  define("MYM_BUILD_TRACE", true);      

  ///////////////////////////////////////// CACHE
  // Cache
  // if cache is disactivated, it must be commented
  define("MYM_CACHE", "1");
  // seconds to be past before deleting the cache
  define("MYM_CACHE_EXPIRE_TIME", "2");
  // mym cache path (relative to root directory)
  define("MYM_CACHE_PATH", "./cache");

  // Label caching
  // if label caching is disactivated, it must be commented
  // define("MYM_LABELS", "1");
  // mym label caching path (relative to app directory)
  // define("MYM_LABELS_PATH", "./data/labels");

  ///////////////////////////////////////// UPLOAD
  // mym upload path (relative to root directory)
  define("MYM_UPLOAD_PATH", "./data/upload");
  // default max upload size for files, in bytes
  define("MYM_MAX_UPLOAD_SIZE", "30000");

  // default width and height (in px) for reducing uploaded images
  define("DEFAULT_MAX_WIDTH", "200");
  define("DEFAULT_MAX_HEIGHT", "300");
  define("DEFAULT_THUMB_SIZE", "150");

  ///////////////////////////////////////// DB
  // for Plain Text File Database
  // mym text database path (relative to root directory)
  define("MYM_TXTDB_PATH", "./data/txtdb");

  // MySQL Database
  // If MySQL is not used, this must be commented
  // define("MYM_MYSQL", "1");
  // MySQL server address
  define("MYM_MYSQL_SERVER", "localhost");
  // MySQL server username
  define("MYM_MYSQL_USER", "root");
  // MySQL server password
  define("MYM_MYSQL_PASSWORD", "");
  // MySQL database
  define("MYM_MYSQL_DB", "MyM");

  ///////////////////////////////////////// User Management
  // MyM Users' DB
  define("MYM_USER_DB", "user");
  // Privilege for a not logged User
  define("MYM_NOT_LOGGED_USER_PRIV", "0");
  // Privilege for a standard new User
  define("MYM_NEW_USER_PRIV", "1");
  // Privilege for the Admin
  define("MYM_ADMIN_PRIV", "15");
  // If validation is activated, it must be <> ""
  define("MYM_VALIDATION", "");

  // Session
  // minutes to be past before a session is considered as expired
  define("MYM_SESSION_EXPIRE_TIME", "30");
