<?php namespace App\libraries;
use DB;
/**
* Filename :  Resizeimage.php
* Purpose  :  This is for resize images, create thumbnail image, add image over another image (only for GIF[image type 1], JPG[image type 2], PNG[image type 3] format) 
* author   : Rasel Ahmed < raselsub2001@gmail.com > Cell : 08801670992598
*/

class Resizeimage
{
   public $name;
   public $imagePath;
   public $source;
   public $success;
   public $width;
   public $height;
   public $type;
   public $thumb;
   public $thumb_width;
   public $thumb_height;

   function Resizeimage($original=false)
   {
      $this->imagePath = $original;
   }


   /* function copyImage
   *
   *  @params imagePath
   *  @return 
   */
   function copyImage($name)
   {
      // reverse the Dir
      $name = strrev($name); 

      // Find the position of first '/'
      $slashPos = strpos($name, '/img/' );
        
      // Find the Substring of $name after first '/'
      $fileDir = substr($name, $slashPos);
      
	  // reverse the substring to check directory
      $fileDir = strrev($fileDir);

      // Again reverse the string
      $name = strrev($name);

      if(is_dir($fileDir))
      {
         try
         {
         	  
            copy($this->imagePath, $name);
         }
         catch(Exception $e)
         {
            echo 'Cannot copy image '. $e->getMessage(). "\n";
         }
      }
   }


   /* function delete
   *
   *  @param   none
   *  @return  none
   */
   function delete()
   {
      try
      {
         unlink($this->imagepath);
		 return 1;
      }
      catch(Exception $e)
      {
         echo 'Cannot delete image'. $e->getMessage(). "\n";
      }
   }

   /*
   * funcion createthumbNail
   *
   * @param  $name      - filename
   * @param  $maxWidth - Maximum height of the width image
   * @param  $maxHeight - Maximum height of the resized image
   * @param  $proportion - check if aspect ratio should maintain; boolean true for check , false otherwise
   * @param  $logo_file - Additional image to add over resized image
   * @param  $dest_x (x coordinate)  - postion of the additional image over resized image
   * @param  $dest_y (y coordinate)  - postion of the additional image over resized image
   * @return  
   */
   
   Public function doResize($name, $maxWidth, $maxHeight, $proportion = true, $logo_file=false, $dest_x=0, $dest_y=0)
   {
	  
  //  var_dump($name);exit;

    $this->name = $name;
      
      list($this->width, $this->height, $this->type) = getimagesize($this->imagePath);
      
	  $new_width  = $maxWidth;
      
      $new_height = $maxHeight;
			 
	  if($proportion){	 
		  
		  if ($this->width > $maxWidth || $this->height >$maxHeight){
			
			$proportions = $this->width/$this->height;			
			
			if($proportions > 1.25 || $proportions < 0.8){
					if($this->width >$this->height){
						$new_width = $maxWidth;
						$new_height = round($maxWidth/$proportions);
					}else{
						$new_height = $maxHeight;
						$new_width = round($maxHeight*$proportions);
					}
				}
			}else{
				$new_width  = $this->width;
      			$new_height = $this->height;
			}
	  }
	
	   switch($this->type)
	   {
         case 1:
                        //Create a new image from file, GIF Format.
                        $this->source = @ imagecreatefromgif($this->imagePath);
                        break;
         case 2:
                        //Create a new image from file, JPEG Format.
                        $this->source = imagecreatefromjpeg($this->imagePath);
                        break;
         case 3:
                        //Create a new image from file, PNG Format.
                        $this->source = imagecreatefrompng($this->imagePath);
                        break;
         default:
                        $this->source = NULL;

      }

      if($this->source)
      {
         $this->thumb_width  = $new_width;
         $this->thumb_height = $new_height;
       
         //create an image identifier representing a black image of size x_size by y_size.
         $this->thumb = imagecreatetruecolor($this->thumb_width, $this->thumb_height);
         
         // Resize the image
         imagecopyresized($this->thumb, $this->source, 0, 0, 0, 0, $this->thumb_width, $this->thumb_height, $this->width, $this->height);
		 
		 // Add image over resized image
		 if($logo_file){
			   
			 $logoImage = imagecreatefromgif($logo_file); // Get logo information
			 $logoW = imagesx($logoImage);
			 $logoH = imagesy($logoImage);
			 			 		   
			 imagecopy($this->thumb, $logoImage, $dest_x, $dest_y, 0, 0, $logoW, $logoH);
		 }
		
		// Save the image 
        switch($this->type) 
         {
            case 1:
                           if (function_exists('imagegif')) 
                           {
                              $this->success = imagegif($this->thumb, $this->name);
                              break;
                           }
                           $this->success = imagejpeg($this->thumb, $this->name, 50);
                           break;
            case 2:
                           $this->success = imagejpeg($this->thumb, $this->name, 100);
                           break;
            case 3:
                           $this->success = imagepng($this->thumb, $this->name);
                           break;
         }  
      }
	  
	  // Copy the image
      $this->copyImage($this->name);
   }
   
   /**
   * Purpose: check if the the uploaded file is of supported type
   *  
   * @access  public
   * @param   string  $type   -type of the uploaded file
   * @return  boolean $status -true if valid ,false otherwise
   */
  public static function checkTypeValidity($type)
  {
     switch($type)
     {
     	  
        case 'image/pjpeg' : $status = true;  break;
        case 'image/jpeg'  : $status = true;  break;
    		case 'image/jpg'   : $status = true;  break;
    		case 'image/JPG'   : $status = true;  break;
    		case 'image/JPEG'  : $status = true;  break;
        case 'image/png'   : $status = true;  break;
        case 'image/x-png' : $status = true;  break;
        case 'image/gif'   : $status = true;  break;
        default            : $status = false; break;   
     
	    }    

     return $status; 
  }

  public static function makeFileName($extension, $start_string = '')
  {
    $file_name = '';
    if($extension[0] != '.') $extension = '.'.$extension;
    while (1) {
      $file_name = $start_string.rand(111, 999).time().$extension;
      $exist = DB::table('files')->where('filename', '=', $file_name)->get();
      if(count($exist) == 0) break;
    }

    return $file_name;
  }
     
}
?>