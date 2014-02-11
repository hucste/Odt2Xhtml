<?php
/***
 * class PHP odt2xhtml : file index ... necessary to include !
 * Copyright (C) 2011  Stephane HUC
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 3 of the License, or (at your option) any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301  USA
 *
 * Contact information:
 *   Stephane HUC
 *   <devs@stephane-huc.net>
 *
 ***/
 
define('ODT2XHTML_DEBUG', 0);
/*** to use with PHP CLI *** USE WITH PRECAUTION, @ YOURS RISKS ***/
define('ODT2XHTML_PHPCLI', 0);	
/*** get owner ***/
define('ODT2XHTML_OWNER', fileowner(__FILE__) );

/*** NOT TOUCH ***/
$hosted = array (
	'odt2xhtml.dev.stephane-huc.net',
	'odt2xhtml.eu.org',
);

$included = array (
	'config.php',
);

if(!empty($_SERVER['TERM']) && $_SERVER['TERM'] == 'xterm') {
	// if this script is execute on terminal (PHP CLI)
	error_reporting(E_ALL);
}
elseif(!empty($_SERVER['HTTP_HOST']) && in_array($_SERVER['HTTP_HOST'], $hosted)) {
	error_reporting(E_ALL);
}
else error_reporting(0);

if(class_exists('XSLTProcessor')) {
	
	foreach($included as $v) {
		$file = dirname(__FILE__).'/'.$v;
		
		$file = new SplFileInfo($file);
		if($file->isFile() && $file->isReadable() && ($file->getOwner() == ODT2XHTML_OWNER) ) {
			require_once($file);
			
			if(ODT2XHTML_DEBUG == true) { 
				$mssg = 'File included: ';
				
				if(ODT2XHTML_PHPCLI == true) {
					echo $mssg." \033[34m".$file."\033[37m \033[32m".'OK'."\033[37m\r\n";
				}
				else echo '<p style="color: green; font-weight: bold;">'.$mssg.'<strong>'.$file.'</strong> !</p>';
			}
		}
		else {
			$mssg = 'Failed to require file include needed: ';
			
			if(ODT2XHTML_PHPCLI == true) {
				die($mssg." \033[34m".$file."\033[37m \033[31m".'KO'."\033[37m\r\n");
			}
			else die('<p style="color:red; font-weight: bold; font-size: 2em">'.$mssg.$file.' !</p>');
		}
		
		unset($file); 
	}
	unset($v);	
}
else {
	$mssg = 'Your WebHosting not support correctly the XSL Transformation in PHP5! Needed PHP-XSL Library ...';
	
	if(ODT2XHTML_PHPCLI == true) {
		die("\033[31m".$mssg."\033[37m ");
	}
	else die('<p style="color:red;">'.$mssg.'</p>');
}
?>
