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
session_start();

if(empty($_GET['do']))
{
	if(($_SESSION['cigicigi_upload'] == 'yes' | $_SESSION['cigicigi_upload'] == 'hata') and ($_SESSION['cigicigi_upload_count'] == 0))
	{
		unset($_SESSION['cigicigi_upload_file_name']);
		if ($_FILES["cigicigi_post_guest_file"]["error"] == UPLOAD_ERR_OK) {
			if($_FILES["cigicigi_post_guest_file"]["type"] == 'image/gif')
			{
				$ext	= '.gif';
				$err	= 'no';
			}elseif(strtolower($_FILES["cigicigi_post_guest_file"]["type"]) == 'image/jpeg')
			{
				$ext	= '.jpeg';
				$err	= 'no';
			}elseif(strtolower($_FILES["cigicigi_post_guest_file"]["type"]) == 'image/jpg')
			{
				$ext	= '.jpg';
				$err	= 'no';
			}elseif(strtolower($_FILES["cigicigi_post_guest_file"]["type"]) == 'image/png')
			{
				$ext	= '.png';
				$err	= 'no';
			}else{
				$err	= 'extension';
			}
			if (!getimagesize($_FILES["cigicigi_post_guest_file"]["tmp_name"]))
			{
				$err	= 'notimage';
			}
			if($err == 'no')
			{
				$image_width = getimagesize($_FILES["cigicigi_post_guest_file"]["tmp_name"]);
				if($image_width[0] > 600)
				{
					$_SESSION['cigicigi_upload_image_width'] = 600;
				}else{
					$_SESSION['cigicigi_upload_image_width'] = $image_width[0];
				}
				$cigicigi_post_guest_media_dir	= __DIR__ . '/../../uploads/cigicigi_post_guest_media/temp';
				$tmp_name = $_FILES["cigicigi_post_guest_file"]["tmp_name"];
				$name = time().'-'.md5($_FILES["cigicigi_post_guest_file"]["name"]).$ext;
				move_uploaded_file($tmp_name, $cigicigi_post_guest_media_dir.'/'.$name);
			}
		}else{
			$err	= $_FILES["cigicigi_post_guest_file"]["error"];
		}

		if($err == 'no')
		{
			echo 'Upload Complete: File,'.$name;
			$_SESSION['cigicigi_upload'] = 'no';
			$_SESSION['cigicigi_upload_count'] = 1;
			$_SESSION['cigicigi_upload_file_name'] = $name;
		}elseif($err == 'extension')
		{
			echo 'Upload Failed: You can only jpg png gif files upload';
			$_SESSION['cigicigi_upload'] = 'hata';
			$_SESSION['cigicigi_upload_count'] = 0;
		}elseif($err == 'notimage')
		{
			echo 'Upload Failed: This file does not image';
			$_SESSION['cigicigi_upload'] = 'hata';
			$_SESSION['cigicigi_upload_count'] = 0;
		}else{
			echo 'Upload Failed: '.$err;
			$_SESSION['cigicigi_upload'] = 'hata';
			$_SESSION['cigicigi_upload_count'] = 0;
		}
	}
}else{
	if(isset($_SESSION['cigicigi_upload_file_name']))
	{
		echo $_SESSION['cigicigi_upload'].','.$_SESSION['cigicigi_upload_count'].','.$_SESSION['cigicigi_upload_file_name'].','.$_SESSION['cigicigi_upload_image_width'];
	}else{
		echo $_SESSION['cigicigi_upload'].','.$_SESSION['cigicigi_upload_count'].',';	
	}
}
?>