<?php
/***
 Just to test-it !

* Verify value ODT2XHTML_PHPCLI is egual O in file config *
***/

require_once('index.php');

if(ODT2XHTML_PHPCLI !== true) {
	
	define('ODT2XHTML_ROOT', dirname(__FILE__) );
	define('ODT2XHTML_FILE_ODF', 'odt2xhtml.sxw');	// name file with extension .odt, .ott or .sxw, .stw!
	define('ODT2XHTML_FRONTEND', dirname(__FILE__).'/');	// directory where file odt to converse
	
	$name_html = substr(ODT2XHTML_FILE_ODF, 0, -4).'.html';
	$file = ODT2XHTML_FRONTEND.'html/'.$name_html;
	$file_html = 'html/'.$name_html;

	if(file_exists($file) && is_file($file)) unlink($file);

	require_once(ODT2XHTML_ROOT.'/class/class.odt2xhtml.php');
	$class = new odt2xhtml(); 	// ODT2XHTML_ROOT,ODT2XHTML_FRONTEND,ODT2XHTML_FILE_ODT
	$class->convert2xhtml();

	if(file_exists($file) && is_file($file) && filesize($file) != 0) {
		echo '<p>Good test: <a href="'.$file_html.'">'.$name_html.'</a></p>';
	
		/*** if you desire to include code ODF2html in your file HTML ***/
		//$class->get_containers_html();
		## For use the method display_container_html()
		# you can use 'meta','css','body','title' 
		#  and :
		#   0 - to obtain elements with the containers
		#	1 - to obtain elements without the containers 
		#		(for all containers, but not 'title'), 
		#		or obtain class name (only for the container 'title')
		#	2 - to obtain elements without the container
		#		(only for the container 'title')
		//echo '<p>Here is a test to display container body :</p>';
		//$class->display_container_html('body', 1); 
		//echo '<p>Here is a test to display container css :</p>';
		//$class->display_container_html('css', 1); 
		//echo '<p>Here is a test to display container meta :</p>';
		//$class->display_container_html('meta', 1); 
		//echo '<p>Here is a test to display container body :</p>';
		//$class->display_container_html('body', 1); 
	}
	else echo '<p style="color:red; font-weight: bold; font-size: 2em;">Converse ODT to XHTML: fault!</p>';

	unset($file_html,$name_html,$class);
}
else die('<p style="color:red; font-weight: bold; font-size: 2em;">Modify the config file to set the variable ODT2XHTML_PHPCLI to "0"!</p>');
?>
