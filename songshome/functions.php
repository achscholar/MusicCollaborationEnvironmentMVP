<?php
function scan($dir)
{
    $dirArray = scandir($dir);
    $dirArray = array_values(array_diff($dirArray, array('..', '.')));
    $notDirArray = [];
    $i = 0;
    if (!sizeof($dirArray) == 0){
    foreach ($dirArray as $value){
        if (!is_dir($dir."\\".$value)){
            $notDirArray[] = $value;
        }
              $i++;
    }
    }

    if (sizeof($notDirArray) > 0){
    $dirArray = array_values(array_diff($dirArray, $notDirArray));}
    
    return $dirArray;
}

function scanForPictures($dir)
{
    $dirArray = scandir($dir);
    $dirArray = array_values(array_diff($dirArray, array('..', '.')));
    $notDirArray = [];
    $i = 0;
    if (!sizeof($dirArray) == 0){
        foreach ($dirArray as $value){
            $check = mime_content_type($dir."\\".$value);
            if (substr($check, 0, 6) !== "image/"){
                $notDirArray[] = $value;
            }
                $i++;
        }
    }
    // ($check !== "image/jpeg") and ($check !== "image/png")
    if (sizeof($notDirArray) > 0){
    $dirArray = array_values(array_diff($dirArray, $notDirArray));}
    
    return $dirArray;
}


function checkExistDir($dir) { 
    if (is_dir($dir)) {
        return true;
     }
     else{
         return false;
     }
}

function strpos_multi(array $chars, $str) {
    foreach($chars as $char) {
      if (strpos($str, $char) !== false) { return false; }
    }
    return true;
}

function displayVar($varDump){
    print("<pre style=\"color:red;font-size:calc(20px + .2vw);\">");
    var_dump($varDump);
    print("</pre>");
}

function getUploadFileSizeMB($filesArray) {
    $totalUploadMB = 0;
    foreach($filesArray as $files){
        foreach ($files["size"] as $size){
            $totalUploadMB += $size;
        }            
    }
    return $totalUploadMB;
}

function deleteFile($path, $filename) {

    if($filename != "" && file_exists($path . $filename)) {
        print(" ".$path.$filename);
        if(!unlink($path . $filename)) { // Remove file.
            return false; // File deletion failed.
        }  
        else {
            return true;
        }
    }
}

/**
 * Get a substring starting from the last occurrence of a character/string
 *
 * @param  string $str The subject string
 * @param  string $last Search the subject for this string, and start the substring after the last occurrence of it.
 * @return string A substring from the last occurrence of $startAfter, to the end of the subject string.  If $startAfter is not present in the subject, the subject is returned whole.
 */
function substrAfter($str, $last) {
    $startPos = strrpos($str, $last);
    if ($startPos !== false) {
        $startPos++;
        return ($startPos < strlen($str)) ? substr($str, $startPos) : '';
    }
    return $str;
}

// function compress_image($source, $destination, $targetSize) {

//     clearstatcache();
//     $info = getimagesize($source);   
//     $sourceSize = filesize($source);

//     if($sourceSize > $targetSize){

//         if ($info['mime'] == 'image/jpeg') 
//             $image = imagecreatefromjpeg($source);

//         elseif ($info['mime'] == 'image/gif') 
//             $image = imagecreatefromgif($source);

//         elseif ($info['mime'] == 'image/png') 
//             $image = imagecreatefrompng($source);

//         clearstatcache();
//         $destinationSize = filesize($source);

//         if($destinationSize > megabytes_to_bytes(15)){
//             $quality = 60;
//             $inteval = 10;
//         }
//         else{
//             $quality = 90;
//         }
        

//         do
//         {   
//             if($quality >= 0){

//                 print($quality."<br>");
//                 imagejpeg($image, $destination, $quality);
//                 clearstatcache();
//                 $destinationSize = filesize($destination);
//                 $quality = $quality - $inteval;
        
//             }
//             else
//             {
//                 break;
//             }
//         }
//         while ($destinationSize > $targetSize);
//         print("#1");
//         return $destination;
//     }
//     else
//     {
//         print("#2");
//         return $source;
//     }
    
// }

// Code To Create Image Thumbnail
// Link image type to correct image loader and saver
// - makes it easier to add additional types later on
// - makes the function easier to read
const IMAGE_HANDLERS = [
    IMAGETYPE_JPEG => [
        'load' => 'imagecreatefromjpeg',
        'save' => 'imagejpeg',
        'quality' => 99
    ],
    IMAGETYPE_PNG => [
        'load' => 'imagecreatefrompng',
        'save' => 'imagepng',
        'quality' => 6
    ],
    IMAGETYPE_GIF => [
        'load' => 'imagecreatefromgif',
        'save' => 'imagegif'
    ]
];

/**
 * $src - a valid file location
 * $dest - a valid file target
 * $targetWidth - desired output width
 * $targetHeight - desired output height or null
 */
function createThumbnail($src, $dest, $targetWidth, $targetHeight = null) {

    // if((memory_get_usage() / 1024 /1024) < 19){
        // displayVar($src);
        // displayVar($dest);
        // displayVar($targetWidth);
        // displayVar($targetHeight);
        // 1. Load the image from the given $src
        // - see if the file actually exists
        // - check if it's of a valid image type
        // - load the image resource
        if (!file_exists($src)){
            print("File Not Found. ");
            return null;     
        }
        // get the type of the image
        // we need the type to determine the correct loader
        $type = exif_imagetype($src);

        // if no valid type or no handler found -> exit
        if (!$type || !IMAGE_HANDLERS[$type]) {
            return null;
        }

        // load the image with the correct loader
        $image = call_user_func(IMAGE_HANDLERS[$type]['load'], $src);

        // no image found at supplied location -> exit
        if (!$image) {
            return null;
        }

        // 2. Create a thumbnail and resize the loaded $image
        // - get the image dimensions
        // - define the output size appropriately
        // - create a thumbnail based on that size
        // - set alpha transparency for GIFs and PNGs
        // - draw the final thumbnail

        // get original image width and height
        $width = imagesx($image);
        $height = imagesy($image);

        //maintain aspect ratio when no height set
        if ($targetHeight == null) {

            // get width to height ratio
            $ratio = $width / $height;

            // if is portrait
            // use ratio to scale height to fit in square
            if ($width > $height) {
                $targetHeight = floor($targetWidth / $ratio);
            }
            // if is landscape
            // use ratio to scale width to fit in square
            else {
                $targetHeight = $targetWidth;
                $targetWidth = floor($targetWidth * $ratio);
            }
        }

        // create duplicate image based on calculated target size
        $thumbnail = imagecreatetruecolor($targetWidth, $targetHeight);

        // set transparency options for GIFs and PNGs
        if ($type == IMAGETYPE_GIF || $type == IMAGETYPE_PNG) {

            // make image transparent
            imagecolortransparent(
                $thumbnail,
                imagecolorallocate($thumbnail, 0, 0, 0)
            );

            // additional settings for PNGs
            if ($type == IMAGETYPE_PNG) {
                imagealphablending($thumbnail, false);
                imagesavealpha($thumbnail, true);
            }
        }

        // copy entire source image to duplicate image and resize

        imagecopyresampled(
            $thumbnail,
            $image,
            0, 0, 0, 0,
            $targetWidth, $targetHeight,
            $width, $height
        );  


        $srcImage = imagecreatefromstring(file_get_contents($src));
        $exif = exif_read_data($src);
        if(!empty($exif['Orientation'])) {
            switch($exif['Orientation']) {
                case 8:
                    $srcImage = imagerotate($srcImage,90,0);
                    break;
                case 3:
                    $srcImage = imagerotate($srcImage,180,0);
                    break;
                case 6:
                    $srcImage = imagerotate($srcImage,-90,0);
                    break;
            }
            
            $srcWidth = ImageSX($srcImage);
            $srcHeight = ImageSY($srcImage);

            if ($srcHeight > $srcWidth){
                
                $thumbnail = imagerotate($thumbnail, -90, 0);
            } 
        }
        else{
            // print("EXIF EMPTY");
        }

        

        // 3. Save the $thumbnail to disk
        // - call the correct save method
        // - set the correct quality level

        // save the duplicate version of the image to disk
        if($type != IMAGETYPE_GIF){
            return call_user_func(
                IMAGE_HANDLERS[$type]['save'],
                $thumbnail,
                $dest,
                IMAGE_HANDLERS[$type]['quality']
            );
        }
        else
        {
            return call_user_func(
                IMAGE_HANDLERS[$type]['save'],
                $thumbnail,
                $dest
            );
        }

        
        
    // }
    // else {
    //     print("Memory Usage Exceeded!");
    //     return false;
    // }
    
}

function createImages($target_file, $thumbnail_target_file, $target_thumbnail_width, $imageFileType, $uploadOk, $thumbnailOnly) {

    clearstatcache();
                                           
    if (($imageFileType == "image/jpeg") || 
    ($imageFileType == "image/png") || 
    ($imageFileType == "image/pjpeg")) {

        $imgSize = filesize($target_file);

        //Fixes issue reading height and width when image is tall.
        $image = imagecreatefromstring(file_get_contents($target_file));
        $exif = exif_read_data($target_file);
        if(!empty($exif['Orientation'])) {
            switch($exif['Orientation']) {
                case 8:
                    $image = imagerotate($image,90,0);
                    break;
                case 3:
                    $image = imagerotate($image,180,0);
                    break;
                case 6:
                    $image = imagerotate($image,-90,0);
                    break;
            }
        }

        $imgWidth = ImageSX($image);
        $imgHeight = ImageSY($image);

        //A thumbnail is created before compression.
        if(createThumbnail($target_file, $thumbnail_target_file, $target_thumbnail_width)){                                              
            clearstatcache();
            // $imgSize= filesize($target_file);
            print(" Image thumbnail created! ");

        }
        else{
            print(" Image thumbnail could not be created! ");
            unlink($target_file);
            if(file_exists($thumbnail_target_file)) { unlink($thumbnail_target_file); }
            $uploadOk = 0;
        }

        if ($thumbnailOnly != true) {

            if ($imgSize > megabytes_to_bytes(2.5) && $uploadOk == 1) {
                while($imgSize > megabytes_to_bytes(1.8)){

                    if ($imgWidth > 1920){
                        $targetImagePixelWidth = 1920;
                    }
                    else if ($imgWidth <= 1920) { 
                        if ($imgSize > megabytes_to_bytes(2)) {
                            $targetImagePixelWidth = ceil($imgWidth * .5);
                        }            
                        else{
                            $targetImagePixelWidth = ceil($imgWidth * .7);
                        }                                                           
                    }

                    if(createThumbnail($target_file, $target_file, $targetImagePixelWidth)){                                              
                        clearstatcache();
                        $imgSize= filesize($target_file);

                        //Fixes issue reading height and width when image is tall.
                        $image = imagecreatefromstring(file_get_contents($target_file));
                        $exif = exif_read_data($target_file);
                        if(!empty($exif['Orientation'])) {
                            switch($exif['Orientation']) {
                                case 8:
                                    $image = imagerotate($image,90,0);
                                    break;
                                case 3:
                                    $image = imagerotate($image,180,0);
                                    break;
                                case 6:
                                    $image = imagerotate($image,-90,0);
                                    break;
                            }
                        }

                        $imgWidth = ImageSX($image);
                        $imgHeight = ImageSY($image);
                    }
                    else{
                        print(" Image could not be compressed! ");
                        unlink($target_file);
                        $uploadOk = 0;
                        break;
                    }
                }
            }                                    
            
            if (filesize($target_file) > megabytes_to_bytes(2.5)){
                print("The image was not successfully compressed and could not be uploaded.");
                unlink($target_file);
                $uploadOk = 0;
            }     

        }
        else
        {
            unlink($target_file);
        }
        
    }
    else if($imageFileType == "image/tiff" || $imageFileType == "image/tif" || $imageFileType == "image/svg+xml" || $imageFileType == "image/svgz+xml"){
        print("This filetype is not supported.");
        unlink($target_file);
        $uploadOk = 0;
    }
    else if (filesize($target_file) > megabytes_to_bytes(2)) {

        print("The ".$imageFileType." filetype needs to be less than 2MB in order to be uploaded or it is not supported.");
        unlink($target_file);
        $uploadOk = 0;
        
    } 
    else {
        //Copies 
        copy($target_file, $thumbnail_target_file);
        if ($thumbnailOnly == true) { 
            unlink($target_file);
        }
    }

    return $uploadOk;
}

function round_up ($value, $places=0) {
    if ($places < 0) { $places = 0; }
    $mult = pow(10, $places);
    return ceil($value * $mult) / $mult;
  }

function gigabytes_to_megabytes($int){
    return $int * 1024;
}

function megabytes_to_gigabytes($int) {
    $int = $int / 1024;
    $int = ceil($int * 100) / 100;
    return $int;
}

function megabytes_to_bytes($int){
    return $int * 1048576;
}

function bytes_to_megabytes($int){
    $int = $int / 1048576;
    $int = ceil($int * 100) / 100;
    return $int;
}

function checkForInt($value){
    $array = array_map('intval', str_split($value));
    $check = true;
    foreach ($array as $int){
      if ($int == 6){
          $check = false;
      }
    }
    
    return $check;
  }

function getRandNumber($min, $max) {
    $fail = true;
        
    while($fail){
        $rand = mt_rand($min, $max);
        
        if (checkForInt($rand)){
            $fail = false;
        }
        else{
            $fail = true;
        }
    }

    return $rand;

}

function returnChar($index){
    $charArray = array('q', 'w', 'r', 't', 'p', 's', 'd', 'f', 'g', 'h', 'j', 'k', 'l', 'z', 'x', 'c', 'v', 'b', 'n', 'm');
    $upper = mt_rand(0, 1);
    return $charArray[$index];
}

function getRandKey($length) {
    //DO NOT ALLOW CHAR: |. Will break part of editgroupproccess
  $randomKey = [0];
  for($loop = 0; $loop < $length; $loop++){

      $i = mt_rand(0, 1);
      switch ($i) {
          case 0:
                $charIndex = mt_rand(0, 19);
              $randChar = returnChar($charIndex);
              $randomKey[$loop] = $randChar;
           
          break;
          case 1:
              $fail = true;
              while($fail){
                  $randInt = mt_rand(1, 9);

                  if (checkForInt($randInt)){
                      $fail = false;
                  }
                  else{
                      $fail = true;
                  }
              }
              
              $randomKey[$loop] = $randInt;
                      
          break;

      }  
  }

  $randomKey = implode('', $randomKey);

  return $randomKey;

}

function twod_array_sort(&$arr, $column, $direction = SORT_ASC) {
    $sort_column = array();
    foreach ($arr as $key => $row) {
        $sort_column[$key] = $row[$column];
    }

    array_multisort($sort_column, $direction, $arr);
}

?>

<script>

var popupState = 0;
function showPopup(id) {

    var popup = document.getElementById(id);
    
    if (popupState === 0){
        popup.classList.toggle("show");
    }

    if (popupState === 0){
    popupState = 1;
    setTimeout(function(e){     
        popup.classList.remove('show');
        popupState = 0; 
        popup.classList.toggle("hide"); 
        }, 2000); 
    }
  
}

function folder() {
    
    var loc = window.location.pathname;
    var dir = loc.substring(0, loc.lastIndexOf('/'));
    alert(dir);
    
}

</script>

