<?php

// Distributed as part of "MyM - avant CMS"
// -----------------------------------------------------------------
// You should have received a copy of the GNU Lesser General Public License
// along with this program.  If not, see <http://www.gnu.org/licenses/>.
// -----------------------------------------------------------------   
// (c) Giovanni Sileno 2006, 2010 - giovanni.sileno@mexpro.it

define('OVERWRITE', false);
define('MYM_UPLOAD_TRACE', 0);

// -- doUpload
// -- Perform an upload of a given file by type.
// $filedata is an array of the type given by $FILES
// $type is the type of the file (_VIDEO, _AUDIO, _IMAGE) defined in 
// $path is the path where the file has to be uploaded
function doUpload($filedata, $type = UNDEFINED, $path = MYM_UPLOAD_REALPATH) {
  switch ($type) {
    case _VIDEO : return upload($filedata, $path, false, "", true, ".flv");
    case _AUDIO : return upload($filedata, $path, false, "", true, ".mp3");
    case _IMAGE : return upload($filedata, $path, false, "", true, ".jpg, .gif, .png");
    default : return upload($filedata, $path);
  }   
}   

// -- helpUpload
// -- Print a message with the contraints of the upload action
// $limit_size is a boolean that enables the size constraint
// $size_limit is the value of the maximum size allowed
// $limit_ext is a boolean that enables the extension contraint
// $exts are the allowed extensions
function helpupload($limit_size = false, $size_limit = MYM_MAX_UPLOAD_SIZE, $limit_ext = false, $exts = "") {
  $exts = explode(',', preg_replace("/\s+/", "", $exts));
 
  print("<p class='help'>");     
  print("<strong>Warning</strong><br/>");  
  if ($limit_ext == true && $exts != "") {
    print("The file must have an extension: ");
    for($counter=0; $counter<count($exts); $counter++) 
      echo " $exts[$counter]";
    print(", ");
  }
  if ($limit_size == true && $size_limit != "") {
    print("It must not be greater than ".$size_limit." bytes, ");
  }
  print("</p>");
}

function validfilename($filename) {
  $filename = strtolower(str_replace(" ", "", $filename));
  
  return $filename;
}

function uploadedfilename($filedata) {
  return validfilename($filedata['name']);
}

function uploadedfile($filedata, $path = MYM_UPLOAD_REALPATH) {
  return $path."/".validfilename($filedata['name']);
}

// -- upload
// -- Perform the upload of a file
// $filedata is an array of the type given by $FILES
// $path is the path where the file has to be uploaded
// $limit_size is a boolean that enables the size constraint
// $size_limit is the value of the maximum size allowed
// $limit_ext is a boolean that enables the extension contraint
// $exts are the allowed extensions
function upload($filedata, $path = MYM_UPLOAD_REALPATH, $limit_size = true, $size_limit = MYM_MAX_UPLOAD_SIZE, $limit_ext = false, $exts = "") {

  // TO BE CORRECT: if upload path not exist, create it.

  trace(MYM_UPLOAD_TRACE + 3, " > upload > path: $path, limit_size: $limit_size, size_limit: $size_limit, limit_ext: $limit_ext, exts: $exts");
  trace_r(3, " > upload > filedata: ", $filedata);
  
  switch ($filedata['error']) {
    case UPLOAD_ERR_OK: break;
    case UPLOAD_ERR_INI_SIZE: 
      print "<p><span class='error'><strong>Error</strong> The given file is too big for the server settings.</span></p>";
      return false;
    case UPLOAD_ERR_FORM_SIZE: 
      print "<p><span class='error'><strong>Error</strong> The given file is too big for this application.</span></p>";
      return false;
    case UPLOAD_ERR_PARTIAL:
      print "<p><span class='error'><strong>Error</strong> Sorry, the upload has been partial.</span></p>";
      return false;
    case UPLOAD_ERR_NO_FILE:
      print "<p><span class='error'><strong>Error</strong> Sorry, no file specified.</span></p>";
      return false;
    case UPLOAD_ERR_NO_TMP_DIR:
      print "<p><span class='error'><strong>Error</strong> Sorry, there is no temporary directory on the server available for the upload.</span></p>";
      return false;  
  }  
  
  $texts = $exts;
  $exts = explode(',', preg_replace("/\s+/", "", $exts));
  trace_r(2, " upload > exts: ", $exts);
  
  $filesize = $filedata['size'];
  $filetmp = $filedata['tmp_name'];
  
  // TO BE CORRECT: handling of all special characters
  $filename = validfilename($filedata['name']);

  $endresult = "";

  $filenameparts = explode('.', $filename);
  $ext = ".".end($filenameparts); 
  $justname = strstr($filename, $ext, true);
  
  if (is_file($path."/".$filename) && !OVERWRITE) {
    print("<p><span class='warning'><strong>Warning</strong> A file with the same name exists. This new file will renamed.</span></p>");
    for($i = 0; is_file($path."/".$justname.$i.$ext); $i++); 
    $filename = $justname.$i.$ext;
  } 
    
  if (($limit_ext == true) && (!in_array($ext, $exts))) {
    trace(MYM_UPLOAD_TRACE + 2, " upload > ext: ". $ext);
    $endresult = "The file $filename has not the good extension ($texts)..";
  } else if (($limit_size == "yes") && ($size_limit < $filesize)) {
    $endresult = "The file $filename is too large.";
  } else if (!copy($filetmp, $path."/".$filename)) { // move_uploaded_file($filetmp, $path."/".$filename)) // copy($filetmp, $path."/".$filename)
    $endresult = "An error occurred during the uploading ($filetmp ->".$path."/".$filename.").";
  }

  if ($endresult != "") {
    print("<p><span class='error'><strong>Error</strong> $endresult </span></p>"); 
    return false;
  }

  print("<p>The file $filename has been uploaded.</p>");
  return $filename;
}

// -- resizeImage
// -- Perform the resize of an Image
function resizeImage($filename, $path = MYM_UPLOAD_REALPATH, $max_width = UNDEFINED, $max_height = UNDEFINED, $thumbsize = UNDEFINED, $squaredthumb = true, $min_width = UNDEFINED, $min_height = UNDEFINED) {

  ini_set("memory_limit","128M");

  trace(MYM_UPLOAD_TRACE + 3, " > resizeImage (file = $filename, path = $path, max_width = $max_width, max_height = $max_height, thumbsize = $thumbsize, squaredthumb = ".(($squaredthumb)?"true":"false").", min_width = $min_width, min_height = $min_height) ");

  if ($filename == '' || $filename == UNDEFINED)
    return false;

  if ($max_width == UNDEFINED && $max_height == UNDEFINED) {
    $max_width = DEFAULT_MAX_WIDTH;
    $max_height = DEFAULT_MAX_HEIGHT;	
  }
  
  if ($min_width == UNDEFINED) $min_width = 0;
  if ($min_height == UNDEFINED) $min_height = 0;
  
  if ($max_width == UNDEFINED) $max_width = 0;
  if ($max_height == UNDEFINED) $max_height = 0;
  
  if ($thumbsize == UNDEFINED)
    $thumbsize = DEFAULT_THUMB_SIZE;

  if ($squaredthumb == UNDEFINED)
    $squaredthumb = true;

  $filenameparts = explode('.', $filename);
  $ext = ".".end($filenameparts);   
    
  if (strtolower($ext) == '.jpg' || strtolower($ext) == '.jpeg')
    $newext = '.jpg';
  else if (strtolower($ext) == '.gif')
    $newext = '.gif';  
  else if (strtolower($ext) == '.png')
    $newext = '.png'; 
  else  
    return false;
  
  $filetmp = $path."/".$filename;
  $fileresized = $path."/resized_".$filename;
  $filethumb = $path."/thumb_".$filename;
  
  if (!is_file($filetmp))
    return false;
   
  list($width, $height) = getimagesize($filetmp);
  if (($width <= $max_width || $max_width == 0) && 
      ($height <= $max_height || $max_height == 0 ) && 
      ($width >= $min_width && $height >= $min_height))
    $resizing = false;
  else
    $resizing = true;    

  if ($width < $thumbsize && $height < $thumbsize) 
    $thumbnailing = false;
  else
    $thumbnailing = true;    

  if (($resizing && is_file($fileresized)) && (filemtime($fileresized) > filemtime($filetmp))) {
    $info = getimagesize($fileresized); // [0] => 910 [1] => 682 [2] => 2 [3] => width="910" height="682" [bits] => 8 [channels] => 3 [mime] => image/jpeg
    $width = $info[0]; $height = $info[1];
    print("<p>Resizing: $width / $height [$min_width-$max_width"."x"."$min_height-$max_height]? ");
    if ((($width == $max_width) && 
          ($max_height == 0 || $height <= $max_height) && 
	  ($min_height == 0 || $height >= $min_height) && 
	  ($min_width == 0 || $width >= $min_width)) ||
	  (($height == $max_height) && 
	  ($max_width == 0 || $width <= $max_width) && 
	  ($min_height == 0 || $height >= $min_height) && 
	  ($min_width == 0 || $width >= $min_width))) {
      print("No. The image $filename has been already resized.</p>");
      $resizing = false;
    } else {
      print("Yes.</p>");
    }
  }

  if (($thumbnailing && is_file($filethumb)) && (filemtime($filethumb) > filemtime($filetmp))) {
    $info = getimagesize($filethumb); 
    $width = $info[0]; $height = $info[1];    
    print("<p>Thumbnailing: $width / $height [$thumbsize, $squaredthumb]? ");
    if (($width == $thumbsize && $squaredthumb == false) ||
        ($width == $thumbsize && $height == $thumbsize)) {
      print("No. The image $filename has been already resized to thumbnail.</p>");
      $thumbnailing = false;
    } else
      print("Yes.</p>");
  }
  
  if ($resizing && is_file($filetmp)) {
    trace(MYM_UPLOAD_TRACE + 1, " > upload > file source: $filetmp");
    $info = getimagesize($filetmp); 
    $width = $info[0]; $height = $info[1];
    trace(MYM_UPLOAD_TRACE + 1, " > upload > width: $width, height: $height");
    trace(MYM_UPLOAD_TRACE + 1, " > upload > maxwidth: $max_width, maxheight: $max_height");
    trace(MYM_UPLOAD_TRACE + 1, " > upload > minwidth: $min_width, minheight: $min_height");
    
    if (($width > $max_width) || ($height > $max_height)) {
      $old_width = $width;
      $old_height = $height; 
    
      while (($max_width != 0 && $width > $max_width) || ($max_height != 0 && $height > $max_height)) {
        trace(MYM_UPLOAD_TRACE + 1, " > upload > width: $width, height: $height");
  	
  	    if ($max_width != 0 && $width > $max_width) {  // max width exceeded
          $new_width = ($width * $max_width) / $width;
          $new_height = ($height * $max_width) / $width;
        } 
        else if ($max_height != 0 && $height > $max_height) {  // max heigth exceeded
          $new_width = ($width * $max_height) / $height;
          $new_height = ($height * $max_height) / $height;    
        } 
  
        $width = $new_width;
        $height = $new_height; 
      } 

      trace(MYM_UPLOAD_TRACE + 1, " > upload > new_width: $width, new_height: $height");
     
      $cropping = false;
      if ($width < $min_width) {    // cropping enabled
        $new_width = $min_width;
        $percent = ($new_width * 100) / $width;    
        $height = ($height * $percent) / 100;
        $width = $new_width;
        $cropping = true;
      } else if ($height < $min_height){
        $new_height = $min_height;
        $percent = ($new_height * 100) / $height;    
        $width = ($width * $percent) / 100;
        $height = $new_height;
        $cropping = true;
      }     
      
      trace(MYM_UPLOAD_TRACE + 1, " > upload > cropping resizing > new_width: $width, new_height: $height");
      
      // Resample
      
      // take some memory more  
      $image_p = imagecreatetruecolor($width, $height);
      if ($newext == '.jpg') $image = imagecreatefromjpeg($filetmp);
      else if ($newext == '.gif') $image = imagecreatefromgif($filetmp);
      else if ($newext == '.png') $image = imagecreatefrompng($filetmp);
      imagecopyresampled($image_p, $image, 0, 0, 0, 0, $width, $height, $old_width, $old_height);
      
      trace(MYM_UPLOAD_TRACE + 1, " > upload > file target: ".$path."/resized_".$filename);
      
      if (!$cropping) {
        // Output
        if ($newext == '.jpg') imagejpeg($image_p, $path."/resized_".$filename, 90); 
        else if ($newext == '.gif') imagegif($image_p, $path."/resized_".$filename);    
        else if ($newext == '.png') imagepng($image_p, $path."/resized_".$filename, 2);      
        imagedestroy($image_p);imagedestroy($image);            
        echo "<p>The image $filename has been resized.</p>";      
      
      } else {         
        
        if ($max_width != 0 && $width > $max_width) 
          $width = $max_width;        
        if ($max_height != 0 && $height > $max_height) 
          $height = $max_height;
          
        trace(MYM_UPLOAD_TRACE + 1, " > upload > cropped > width: $width, height: $height");
        
        $image_n = imagecreatetruecolor($width, $height);
        imagecopy($image_n, $image_p, 0, 0, 0, 0, $width, $height);        
        
        // Output
        if ($newext == '.jpg') imagejpeg($image_n, $path."/resized_".$filename, 90); 
        else if ($newext == '.gif') imagegif($image_n, $path."/resized_".$filename);    
        else if ($newext == '.png') imagepng($image_n, $path."/resized_".$filename, 2);      
        	
        imagedestroy($image_n); imagedestroy($image_p);imagedestroy($image);            
        echo "<p>The image $filename has been resized and cropped.</p>";              
      }      
    }
  }
      
  if ($thumbnailing && is_file($filetmp)) {
    // Create Thumbnails
    $info = getimagesize($filetmp); 
    $width = $info[0]; $height = $info[1];
  
    trace(MYM_UPLOAD_TRACE + 1, " upload > file source thumb: ".$filetmp);
    trace(MYM_UPLOAD_TRACE + 1, " upload > width: $width, height: $height");
    trace(MYM_UPLOAD_TRACE + 1, " upload > thumbsize: $thumbsize, squaredthumb: $squaredthumb ");
    
    // Resample
    if ($newext == '.jpg') $image = imagecreatefromjpeg($filetmp);
    else if ($newext == '.gif') $image = imagecreatefromgif($filetmp);
    else if ($newext == '.png') $image = imagecreatefrompng($filetmp);
    
    // for squared thumbnails
    if ($squaredthumb) {
      if ($width < $height) {    // if cropping enabled, it should be <
        $side = $width;
        $percent = $thumbsize * 100 / $side;
        $new_width = $thumbsize;
        $new_height = ($height * $percent) / 100;
      }
      else {
        $side = $height;
        $percent = $thumbsize * 100 / $side;    
        $new_width = ($width * $percent) / 100;
        $new_height = $thumbsize;
      }
      
      trace(MYM_UPLOAD_TRACE + 1, " upload > percent: ".$percent);    
      $image_p = imagecreatetruecolor($thumbsize, $thumbsize);
      imagecopyresampled($image_p, $image, 0, 0, 0, 0, $thumbsize, $thumbsize, $side, $side);
      echo "<p>A squared thumbnail for image $filename has been created.</p>";
    }
    
    // for resized thumbnails, resize width to thumbsize
    else {
      $side = $width;
      $percent = $thumbsize * 100 / $side;
      
      $new_width = $thumbsize;
      $new_height = ($height * $percent) / 100;
      $image_p = imagecreatetruecolor($new_width, $new_height);
      imagecopyresampled($image_p, $image, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
      echo "<p>A thumbnail for the image $filename has been created.</p>";    
    }
  
    // Output 
    if ($newext == '.jpg') imagejpeg($image_p, $path."/thumb_".$filename, 90); 
    else if ($newext == '.gif') imagegif($image_p, $path."/thumb_".$filename);    
    else if ($newext == '.png') imagepng($image_p, $path."/thumb_".$filename, 2);      
    imagedestroy($image_p); imagedestroy($image);  
    trace(MYM_UPLOAD_TRACE + 1, " upload > file target: ".$path."/thumb_".$filename);
  }
  
  return true;
}
