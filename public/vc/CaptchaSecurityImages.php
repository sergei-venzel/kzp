<?php
class CaptchaSecurityImages
{
    var $font_dir = 'fonts/';

    public function __construct($width = '120', $height = '85', $characters = '5', &$code)
    {
        $use_font = $this->getFont();

        $code = $this->generateCode($characters);
        /* font size will be 75% of the image height #8080FF#8080FF */
        $font_size = $height * 0.4;
        $image = @imagecreate($width, $height) or die('Cannot initialize new GD image stream');
        /* set the colours */
        $background_color = imagecolorallocate($image, 246, 246, 246);
        $text_color       = imagecolorallocate($image, 111, 140, 113);
        $noise_color      = imagecolorallocate($image, 182, 196, 183);
        /* generate random dots in background */
        for ($i = 0; $i < ($width * $height) / 3; $i ++) {
            imagefilledellipse($image, mt_rand(0, $width), mt_rand(0, $height), 1, 1, $noise_color);
        }
        /* generate random lines in background */
        for ($i = 0; $i < ($width * $height) / 500; $i ++) {
            imageline($image, mt_rand(0, $width), mt_rand(0, $height), mt_rand(0, $width), mt_rand(0, $height), $noise_color);
        }
        /* create textbox and add text */
        $textbox = imagettfbbox($font_size, 0, $use_font, $code) or die('Error in imagettfbbox function');
        $x = ($width - $textbox[4]) / 2;
        $y = ($height - $textbox[5]) / 2;
        imagettftext($image, $font_size, 0, $x, $y, $text_color, $use_font, $code) or die('Error in imagettftext function');
        /* output captcha image to browser */
        header('Content-Type: image/jpeg');
        imagejpeg($image);
        imagedestroy($image);
    }

    public function generateCode($characters)
    {
        /* list all possible characters, similar looking characters and vowels have been removed */
        $possible = '23456789bcdfghjkmnpqrstvwxyzBCDFGHJKMNPQRSTVWXYZ';
        $code     = '';
        $i        = 0;
        while ($i < $characters) {
            $code .= substr($possible, mt_rand(0, strlen($possible) - 1), 1);
            $i ++;
        }

        return $code;
    }


    public function getFont()
    {
        $fonts_array = array();
        //$font = false;
        if ($handle = opendir($this->font_dir)) {
            while (false !== ($file = readdir($handle))) {
                if ($file != '.' && $file != '..') {

                    if (stristr($file, '.') == '.ttf') {
                        $fonts_array[] = $this->font_dir . $file;
                    }
                }
            }
            closedir($handle);
        }

        if ( ! empty($fonts_array)) {
            return $fonts_array[rand(0, (count($fonts_array) - 1))];
        }

        return false;
    }
}

$width      = isset($_GET['width']) ? $_GET['width'] : '550';
$height     = isset($_GET['height']) ? $_GET['height'] : '80';
$characters = isset($_GET['characters']) && $_GET['characters'] > 1 ? $_GET['characters'] : '5';

$code = '';
$captcha = new CaptchaSecurityImages($width, $height, $characters, $code);

session_id();
session_start();
$_SESSION['security_code'] = $code;
session_write_close();
?>