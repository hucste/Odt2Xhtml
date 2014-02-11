#!/usr/bin/php
<?php
/***
* Just to test-it !
*
* Think to modify value ODT2XHTML_PHPCLI at 1 in file index.php *
* Think to chmod +x test_cli.php
** To try it : ./test_cli.php /directory_in/file.odt /directory_out **
***/

require_once('index.php');

if(ODT2XHTML_PHPCLI == TRUE) {
	$mssg = "Specify argument as: 'php test_cli.php (/dir_in/)file.odt /dir_out'";
	switch($argc) {
		case 0:
		case 1: die("\033[31m".'*** STOP, here. No Argument specified! '.$mssg.' ***'."\033[37m\r\n");
		break;
		case 2: die("\033[31m".'*** Fault one argument; '.$mssg.' ***'."\033[37m\r\n");
		break;
	}
	if($argc>3) die("\033[31m".'*** Too much arguments: '.$mssg.'! ***'."\033[37m\r\n");

	if(!empty($argv[1])) {
		$x = array_reverse(explode('/', $argv[1]));
		
		# name file with extension .odt, .ott or .sxw, .stw!
		if(!empty($x[0])) $file = $x[0];
		
		if(file_exists($file) && is_file($file)) {
			
			# directory where file odt is 
			$root = str_replace($file,'',$argv[1]);
			
			if(empty($root)) $root = getcwd().'/';
			
			define('ODT2XHTML_ROOT', $root);
			# directory where file odt is out
			$frontend = $argv[2];
			
			# if ultimate caracter of $frontend isn't an '/'
			if(file_exists($frontend) && is_dir($frontend)) {
				if(substr($frontend, -1, 1) != '/') define('ODT2XHTML_FRONTEND', $frontend.'/');
				else define('ODT2XHTML_FRONTEND', $frontend);
			}
			
			define('ODT2XHTML_FILE_ODF', $file);

			$name_html = substr($file, 0, strrpos($file, ".")).'.html';
			$file_html = ODT2XHTML_FRONTEND.'html/'.$name_html; 

			if(file_exists($file_html) && is_file($file_html)) unlink($file_html);
	
			require_once(ODT2XHTML_ROOT.'class/class.odt2xhtml.php');
			$class = new odt2xhtml();
			$class->convert2xhtml();

			if(file_exists($file_html) && is_file($file_html) && filesize($file_html) != 0) {
				echo "\033[32m".'Good test!'."\033[37m".' file is on: '."\033[34m".$file_html."\033[37m\r\n";
			
				/*** if you desire to include code ODF2html in your file HTML ***/
				//$class->get_containers_html();
				## For use the method display_elements_html()
				# you can use 'meta','css','body','title' 
				#  and :
				#   0 - to obtain with the elements containers
				#	1 - to obtain without the elements containers
				//echo '<p>Here is a test to display elements css :</p>';
				//$class->display_container_html('css', 1); 
			}
			else echo "\033[31m".'*** Converse fault!***'."\033[37m\r\n";
			
			unset($file_html, $name_html, $frontend, $file, $class);
		}
	}
}
else die('<p style="color:red;">Modify the file index to set the variable ODT2XHTML_PHPCLI to "1"!</p>');
?>
