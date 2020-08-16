<?php
namespace Lib;

class Image{
    var $image;
    var $image_type;

    function load($filename) {

        $image_info = getimagesize($filename);
        $this->image_type = $image_info[2];
        if( $this->image_type == IMAGETYPE_JPEG ) {

            $this->image = imagecreatefromjpeg($filename);
        } elseif( $this->image_type == IMAGETYPE_GIF ) {

            $this->image = imagecreatefromgif($filename);
        } elseif( $this->image_type == IMAGETYPE_PNG ) {

            $this->image = imagecreatefrompng($filename);
        }
    }
    function save($filename, $image_type=IMAGETYPE_JPEG, $compression=75, $permissions=null) {

        if( $image_type == IMAGETYPE_JPEG ) {
            imagejpeg($this->image,$filename,$compression);
        } elseif( $image_type == IMAGETYPE_GIF ) {

            imagegif($this->image,$filename);
        } elseif( $image_type == IMAGETYPE_PNG ) {

            imagepng($this->image,$filename);
        }
        if( $permissions != null) {

            chmod($filename,$permissions);
        }
    }
    function output($image_type=IMAGETYPE_JPEG) {

        if( $image_type == IMAGETYPE_JPEG ) {
            imagejpeg($this->image);
        } elseif( $image_type == IMAGETYPE_GIF ) {

            imagegif($this->image);
        } elseif( $image_type == IMAGETYPE_PNG ) {

            imagepng($this->image);
        }
    }
    function getWidth() {
        return imagesx($this->image);
    }
    function getHeight() {

        return imagesy($this->image);
    }
    function resizeToHeight($height) {

        $ratio = $height / $this->getHeight();
        $width = $this->getWidth() * $ratio;
        $this->resize($width,$height);
    }

    function resizeToWidth($width) {
        $ratio = $width / $this->getWidth();
        $height = $this->getheight() * $ratio;
        $this->resize($width,$height);
    }

    function scale($scale) {
        $width = $this->getWidth() * $scale/100;
        $height = $this->getheight() * $scale/100;
        $this->resize($width,$height);
    }

    function resizeA($width,$height) {
        $new_image = imagecreatetruecolor($width, $height);
        imagecopyresampled($new_image, $this->image, 0, 0, 0, 0, $width, $height, $this->getWidth(), $this->getHeight());
        $this->image = $new_image;
    }
    function resizeN($width,$height) {
        $new_image = imagecreatetruecolor($width, $height);
        $white = imagecolorallocate($new_image, 170, 170, 170);
        imagefill($new_image, 0, 0, $white);
        if($this->getWidth()>$this->getHeight()){
            $this->resizeToWidth($width);
        }
        else if($this->getWidth()<=$this->getHeight()){
            $this->resizeToHeight($height);
        }
        imagecopy($new_image, $this->image, ($width-$this->getWidth())/2, ($height-$this->getHeight())/2, 0, 0, $this->getWidth(), $this->getHeight());
        //imagecopyresampled($new_image, $this->image, 0, 0, 0, 0, $width, $height, $this->getWidth(), $this->getHeight());
        $this->image = $new_image;
    }
    function rotate($degrees = 90){
        $degrees = $degrees*-1;
        $this->image = imagerotate($this->image, $degrees, 0);
    }
    function addWatermarkText($text){
        $image = $this->image;

        $stamp2 = imagecreatefrompng('resources/images/watermark2.png');
        $color = imagecolorallocate($image, 246, 246, 246);
        $string = $text;
        $fontSize = 3;
        for($i=0;$i<$this->getHeight()/75;$i++){
            $xAdd = 0;
            if($i%2==1)$xAdd = 150;
            for($a=0;$a<$this->getWidth()/250;$a++){
                imagecopy($image, $stamp2, (($a*250)+$xAdd), $i*75, 0, 0, imagesx($stamp2), imagesy($stamp2));
                //imagestring($image, $fontSize, (($a*250)+$xAdd), $i*75, $string, $color);
            }
        }
        $this->image = $image;

        $stamp = imagecreatefrompng('resources/images/watermark.png');

        // Set the margins for the stamp and get the height/width of the stamp image
        $marge_right = 10;
        $marge_bottom = 10;
        $sx = imagesx($stamp);
        $sy = imagesy($stamp);

        // Copy the stamp image onto our photo using the margin offsets and the photo
        // width to calculate positioning of the stamp.
        imagecopy($image, $stamp, imagesx($image) - $sx - $marge_right, imagesy($image) - $sy - $marge_bottom, 0, 0, imagesx($stamp), imagesy($stamp));
        $this->image = $image;
    }

    public static function resize($file, $width, $height, $newfile = '', $quality = 80){
        if(!is_file($file))
            return false;

        if(trim($newfile) == '')
            $newfile = $file;

        try{
            $thumb = new \Imagick();
            $thumb->readImage($file);
            $thumb->setCompression(\Imagick::COMPRESSION_JPEG);
            $thumb->setCompressionQuality($quality);
            $thumb->cropThumbnailImage($width, $height);
            $thumb->writeImage($newfile);
            $thumb->clear();
            $thumb->destroy();
            chmod($newfile, 0777);
        }
        catch(\ImagickException $ex) {
            return false;
        }
        return is_file($newfile)?
            $newfile
            : false;

    }
}
?>