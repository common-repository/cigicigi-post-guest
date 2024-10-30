<?php
// CigiCigi Post Guest
// Author: CigiCigi Online iLk3r
/*  Copyright 2011  CigiCigi Online  ( info@cigicigi.co )

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/
//Pack for CigiCigi Post Guest
error_reporting(NULL);

session_start();

function imagelinethick($image, $x1, $y1, $x2, $y2, $color, $thick = 1)
{
    if ($thick == 1) {
        return imageline($image, $x1, $y1, $x2, $y2, $color);
    }
    $t = $thick / 2 - 0.5;
    if ($x1 == $x2 || $y1 == $y2) {
        return imagefilledrectangle($image, round(min($x1, $x2) - $t),
                   round(min($y1, $y2) - $t), round(max($x1, $x2) + $t),
                   round(max($y1, $y2) + $t), $color);
    }
    $k = ($y2 - $y1) / ($x2 - $x1); //y = kx + q
    $a = $t / sqrt(1 + pow($k, 2));
    $points = array(
        round($x1 - (1+$k)*$a), round($y1 + (1-$k)*$a),
        round($x1 - (1-$k)*$a), round($y1 - (1+$k)*$a),
        round($x2 + (1+$k)*$a), round($y2 - (1-$k)*$a),
        round($x2 + (1-$k)*$a), round($y2 + (1+$k)*$a),
    );
    imagefilledpolygon($image, $points, 4, $color);
    return imagepolygon($image, $points, 4, $color);
}

header("Content-type: image/png");


$font = dirname(__FILE__)."/images/DejaVuSans-Bold.ttf";

if(empty($_SESSION['cigicigi_captcha'])){
	exit;
}


$en		= 200;
$boy	= 60;
$im 	= imagecreatetruecolor($en, $boy);
//imageantialias($im, true);
 

$beyaz		= imagecolorallocate($im, 255, 255, 255);
$gri		= imagecolorallocate($im, 80, 80, 80);
$siyah		= imagecolorallocate($im, 0, 0, 0);
$kirmizi	= imagecolorallocatealpha($im, 240, 0, 0, 70);


imagefilledrectangle($im, 0, 0, $en, $boy, $beyaz);


for($i=0;$i<strlen($_SESSION['cigicigi_captcha']);$i++)
{

	$a			= rand(-40,40);
	$uzaklýk	= 30 * $i;
	if($i == 0){
		$uzaklýk_10	= 20;
	}else{
		$uzaklýk_10	= $uzaklýk + 5;
	}
	$tx = rand($uzaklýk,$uzaklýk_10);
	$sx = $tx+1;
	$y	= rand(25,50);
	$text	= substr($_SESSION['cigicigi_captcha'], $i, 1);
	imagettftext($im, 22, $a, $sx, $y+2, $gri, $font, $text);
	imagettftext($im, 22, $a, $tx, $y, $siyah, $font, $text);
}
$cigicigi_post_guest_cpt_bg	= base64_decode($_GET['bg']);
if(empty($cigicigi_post_guest_cpt_bg))
{
	$arkaplan = imagecreatefrompng(dirname(__FILE__).'/images/arkaplan.png');
}else{
	if (file_exists(dirname(__FILE__).$cigicigi_post_guest_cpt_bg))
	{
		$arkaplan = imagecreatefrompng(dirname(__FILE__).$cigicigi_post_guest_cpt_bg);
	}else{
		$arkaplan = imagecreatefrompng(dirname(__FILE__).'/images/arkaplan.png');
	}
}


imagecopymerge($im, $arkaplan, 0, 0, 0, 0, 200, 60, 45);

imagelinethick($im, rand(0,10), rand(5,55), rand(10,20), rand(0,60), $kirmizi, 5);
imagelinethick($im, rand(20,30), rand(5,55), rand(30,40), rand(0,60), $kirmizi, 4);
imagelinethick($im, rand(40,50), rand(10,50), rand(515,45), rand(5,55), $kirmizi, 3);
imagelinethick($im, rand(60,70), rand(10,50), rand(70,80), rand(5,55), $kirmizi, 2);
imagelinethick($im, rand(80,90), rand(10,50), rand(90,100), rand(5,55), $kirmizi, 1);
imagelinethick($im, rand(100,110), rand(15,45), rand(110,120), rand(10,50), $kirmizi, 1);
imagelinethick($im, rand(120,130), rand(15,45), rand(130,140), rand(10,50), $kirmizi, 2);
imagelinethick($im, rand(140,150), rand(15,45), rand(150,160), rand(10,50), $kirmizi, 3);
imagelinethick($im, rand(160,170), rand(15,45), rand(170,180), rand(10,50), $kirmizi, 4);
imagelinethick($im, rand(180,190), rand(5,55), rand(190,200), rand(0,60), $kirmizi, 5);


imagepng($im);
 

imagedestroy($im);
?>