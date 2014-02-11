<?php 
/***
 * class PHP odt2xhtml
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

/******************************************************************************/
/***   Dont touch at the rest !                                             ***/
/******************************************************************************/
define('CLASS_ODT2XHTML_ROOT', dirname(__FILE__));

define('ODT2XHTML_MAX_TIME', ini_get('max_execution_time') );
define('ODT2XHTML_MEM', ini_get('memory_limit') );
define('ODT2XHTML_MEM_INT', intval( ODT2XHTML_MEM ) ); 

define('ODT2XHTML_VERSION', 20110310);	// version d'ODT2XHTML

if(defined('ODT2XHTML_PHPCLI')) define('CLASS_MSSG_PHPCLI', ODT2XHTML_PHPCLI);

class odt2xhtml {
	
	public $odf=array();
	private $php;
	
	public function __construct() {
		
		$this->get_class_need();
		$this->check_constants();
		$this->set_constants();
		$this->set_variables();
		
	}
	
	public function __destruct() {
		
		$this->delete_tmp();
		
	}
	
	/*** Converter ODT TO XHTML ***/
	public function convert2xhtml() {
		
		/*** making all directories ***/
		$this->mk_dirs_needed();
		
		/*** unzip odf file ***/
		$this->unzip_odf();
		
		/***  make new file xml with ODF files xml ***/
		$this->make_new_xml();
		
		/*** move file img to directory img ***/
		$this->mv_img();

		/*** Create temporary file html by xslt processor ***/
		$this->xslt_convert_odf();
		
		/*** Create real files html, css ***/
		$this->create_files();	

		/*** Move icone ***/
		$this->mv_icon();	
		
	}
	
	/*** transform values cm in px ***/
	protected function analyze_attribute($attribute) {
		try {
			/*
			if(ereg('cm', $attribute)) {
				if(ereg(' ',$attribute)) $xploz = explode(' ', $attribute);
				* */
			if(strpos($attribute, 'cm') !== false) {
				if(strpos($attribute, ' ') !== false) $x = explode(' ', $attribute);
			
				if(!empty($x) && is_array($x)) {
					foreach($x as $k => $v) {
						
						//if(ereg('cm$', $v)) {
						if(preg_match('|cm$|', $v)) {
							$v = round(floatval($v) * 28.6264);
							$x[$k] = $v.'px';
						}

						for($i=0; $i<count($x); $i++) {
							if($i==0) $this->attribute = $x[$i];
							else $this->attribute .= ' '.$x[$i];
						}
						
					}
					unset($k, $v);
				}
				else {
					$this->attribute = round(floatval($attribute) * 28.6264);
					$this->attribute = $this->attribute.'px';
				}
				unset($x);
			}
			else $this->attribute = $attribute;

			$this->attribute = '="'.$this->attribute.'"';
		
			if(!empty($this->attribute)) return $this->attribute;
			
		}
		catch(Exception $e) { die('Error_method: '.__METHOD__.$e->getMessage()); }
	}
	
	protected function build_vars_file_handling() {
		try {
			
			$this->odf['file']['ooo'] = ODT2XHTML_FRONTEND.ODT2XHTML_FILE_ODF;
			
			// obtain dir and file infos,
			$this->file_handling($this->odf['file']['ooo'], 'vars');
			
			$this->odf['dir']['out']['html'] = ODT2XHTML_OUT.'html';		// dir html
			$this->odf['dir']['out']['tmp'] = ODT2XHTML_OUT.'tmp';	// tmp dir 
			
			$this->odf['dir']['out']['img'] = $this->odf['dir']['out']['html'].'/img';		// dir img
			$this->odf['dir']['out']['php'] = $this->odf['dir']['out']['img'].'/php'.$this->php;
			$this->odf['dir']['out']['ext'] = $this->odf['dir']['out']['php'].'/'.$this->odf['file']['ext'];
			$this->odf['dir']['out']['OOo'] = $this->odf['dir']['out']['ext'].'/'.$this->odf['file']['name'];
			
			/*** define dir for attribute img src ***/
			if(defined('IMG_SRC')) $this->odf['dir']['img']['src'] = IMG_SRC.'img';
			else $this->odf['dir']['img']['src'] = 'img';

			$this->odf['file']['dir']['tmp'] = $this->odf['dir']['out']['tmp'].'/'.$this->odf['file']['name'];
			$this->odf['file']['tmp']['html'] = $this->odf['file']['dir']['tmp'].'.html';	// file html tmp
			$this->odf['file']['tmp']['xml'] = $this->odf['file']['dir']['tmp'].'.xml';	// file xml tmp
			
			$this->odf['dir']['out']['pict'] = $this->odf['file']['dir']['tmp'].'/Pictures';
			
		}
		catch(Exception $e) { die('Error_method: '.__METHOD__.$e->getMessage()); }
	}
	
	protected function build_xml($action) {
		try {
			
			$this->mode = 'ab';
			
			if($action != 'closer' || $action != 'opener') { 
				if(!empty($this->odf['file']['xml'])) $this->file_content($this->odf['file']['xml']); 
				
				if(!empty($this->content)) {
					$this->content = str_replace($this->xml_version, '', $this->content); 
				}
			}
			
			if($action == 'meta' || $action == 'styles') {
				switch($this->odf['file']['ext']) {
					
					case 'sxw' :
					case 'stw' :
						if(!empty($this->content)) {
							$this->content = str_replace($this->doctype, '', $this->content); 
						}
					break;
					
				}
			}
						
			switch($action) {
				
				case 'content':
					/*** try to recuperate text:h level 1 to include in element html title : *** EXPERIMENTAL *** ***/
					$pattern = '/<text:h text:style-name="(.*)" text:outline-level="1">(.*?)<\/text:h>/';
					if(preg_match_all($pattern, $this->content, $this->match)) {
						$this->html['title'] = strip_tags($this->match[2][0]);
					}
			
					/*** add header and footer page ***/
					switch($this->odf['file']['ext']) {
					
						case 'odt' :
						case 'ott' :
							# add header page
							if(!empty($this->header)) $this->content = $this->replace_content('header');
							# modify src img
							$this->content = $this->replace_content('img_odt');
							# add footer page
							if(!empty($this->footer)) {
								$search = '</office:text>';
								$replace = $this->footer.'</office:text>';
								$this->content = str_replace($search, $replace, $this->content); 
								unset($search, $replace);
							}
						break;
					
						case 'sxw' :
						case 'stw' :
							$this->content = str_replace($this->doctype, '', $this->content);
							# add header page
							if(!empty($this->header)) $this->content = $this->replace_content('header');
							# modify src img
							$this->content = $this->replace_content('img_sxw');
							# add footer page
							if(!empty($this->footer)) {
								$search = '</office:body>';
								$replace = $this->footer.'</office:body>';
								$this->content = str_replace($search, $replace ,$this->content); 
								unset($search, $replace);
							}
						break;
					
					}
			
					# rebuild text:reference-mark-* in text:reference-mark syntax xml correct : manage element html abbr 
					$this->content = $this->replace_content('reference_mark');

					# analyze attribute to transform style's value cm in px
					$this->content = $this->replace_content('analyze_attribute');
			
					# search text in position indice or exposant to transform it correctly
					$this->rewrite_position();
				break;
				
				case 'closer':
					$this->content = "\n".'</office:document>'."\n";
				break;
				
				case 'styles':
					# analyze attribute to transform style's value cm in px
					$this->content = $this->replace_content('analyze_attribute');
			
					if(preg_match_all('/<style:header>(.*)<\/style:header>/Us', $this->content, $this->match)) {
						$this->header = str_replace('style:header','text:header', $this->match[0][0]); 
					}
				
					if(preg_match_all('/<style:footer>(.*)<\/style:footer>/Us', $this->content, $this->match)) {
						$this->footer = str_replace('style:footer','text:footer', $this->match[0][0]);
					}
				break;
				
				case 'opener':
					$this->content = $this->xml_version."\n";
		
					switch($this->odf['file']['ext']) {
				
						case 'odt' :
						case 'ott' :
							$this->content .= $this->replace_content('open_element_xml4odt');
						break;
				
						case 'sxw' :
						case 'stt' :
							$this->content .= $this->replace_content('open_element_xml4sxw');
						break;
				
					}
		
					$this->mode = 'wb';
				break;
				
			}
			
			if(is_array($this->content) && is_string($this->content[1])) {
				$this->write_file($this->odf['file']['tmp']['xml'], $this->mode, $this->content[1]); 
			}
			else $this->write_file($this->odf['file']['tmp']['xml'], $this->mode, $this->content); 
			
			if(ODT2XHTML_DEBUG == true) { 
				if(is_array($this->content)) echo $this->message->display('ok', 'making_'.$action, serialize($this->content));
				else echo $this->message->display('ok', 'making_'.$action, $this->content); 
			}
			
		}
		catch(Exception $e) { die('Error_method: '.__METHOD__.$e->getMessage()); }
	}
	
	/*** create file css ***/
	protected function create_file_css() { 
		try {
			
			if( !empty($this->file['tmp']) && !empty($this->odf['file']['css']) ) {
				
				$pattern = '/<style type="text\/css">(.*)<\/style>/es';
				if(preg_match_all($pattern, $this->file['tmp'], $this->match))	{
					$this->buffer = trim($this->match[1][0]); 
				
					$this->write_file($this->odf['dir']['out']['html'].'/'.$this->odf['file']['css'], 'w', $this->buffer);
				
					unset($this->buffer);
				}
				unset($pattern);
			
				$this->file['tmp'] = $this->replace_content('link_css');

				if(ODT2XHTML_DEBUG == true && !empty($this->file['tmp']) ) { 
					echo $this->message->display('ok','creating_file_css', $this->odf['file']['css']); 
				}
				
			}
			
		}
		catch(Exception $e) { die('Error_method: '.__METHOD__.$e->getMessage()); }
	}
	
	/*** create ultimate html and css (if necessary) files ***/
	protected function create_files() {
		try {
		
			if(!empty($this->file['tmp'])) {
				/*** modify title in html flux ***/
				$this->file['tmp'] = $this->replace_content('title');
		
				/*** manage to create file css ***/
				if(ODT2XHTML_FILE_CSS == true) $this->create_file_css();

				/*** Create real file html ***/
				$this->write_file($this->odf['dir']['out']['html'].'/'.$this->odf['file']['html'], 'w', $this->file['tmp']);
		
				if(ODT2XHTML_DEBUG == true) { 
					echo $this->message->display('ok', 'creating_file_html', $this->odf['file']['html']); 
				}
			}
			
		}
		catch(Exception $e) { die('Error_method: '.__METHOD__.$e->getMessage()); }
	}
	
	protected function check_constants() {
		try {
			
			if(!defined('ODT2XHTML_ROOT')) die($this->message->display('ko','error_define', 'ODT2XHTML_ROOT'));
			if(!defined('ODT2XHTML_FILE_ODF')) die($this->message->display('ko','error_define', 'ODT2XHTML_FILE_ODF'));
			if(!defined('ODT2XHTML_FRONTEND')) die($this->message->display('ko','error_define', 'ODT2XHTML_FRONTEND'));
						
		}
		catch(Exception $e) { die('Error_method: '.__METHOD__.$e->getMessage()); }
	}
	
	/*** choose better file xsl segun file's extension ***/
	protected function choose_valid_xsl() {
		try {
			
			switch($this->odf['file']['ext']) {
				
				case 'odt' :
				case 'ott' :
					$this->odf['file']['xsl'] = ODT2XHTML_XSL_ROOT.'/odt2xhtml.xsl';
				break;
				
				case 'sxw' :
				case 'stw' :
					$this->odf['file']['xsl'] = ODT2XHTML_XSL_ROOT.'/sxw2xhtml.xsl';
				break;
				
			}
		
			if(ODT2XHTML_DEBUG == true) { echo $this->message->display('ok', 'valid_xsl', $this->odf['file']['xsl']); }
			
		}
		catch(Exception $e) { die('Error_method: '.__METHOD__.$e->getMessage()); }
	}
	
	/*** delete directory temporary ***/
	protected function delete_tmp($path='') {	
		try {
			
			if(ODT2XHTML_MEM_INT <= 24) ini_set('memory_limit', '24M');
			ini_set('max_execution_time', 0);
			
			if(empty($path)) $path = $this->odf['dir']['out']['tmp'];
			
			if(file_exists($path) && is_dir($path)) {
				chmod($path, 0777);
				$dir = opendir($path);
		
				/*** tant que la condition est juste en type alors on lit ***/
				while(false !== ($file = readdir($dir))) {
					if($file != '.' && $file != '..') {
						$path_tmp = $path.'/'.$file;
				
						if(!empty($path_tmp) && is_file($path_tmp)) {
							unlink($path_tmp);
						}
						else {
							$this->delete_tmp($path_tmp);
						}
					}
				}
		
				closedir($dir);
				rmdir($path);
		
				if(ODT2XHTML_DEBUG == true) { 
					echo $this->message->display('ok', 'dir_deleted', $path);
				}
			}
		
			if(ini_get('memory_limit') != ODT2XHTML_MEM) ini_set('memory_limit', ODT2XHTML_MEM);
			ini_set('max_execution_time', ODT2XHTML_MAX_TIME);
			
		}
		catch(Exception $e) { die('Error_method: '.__METHOD__.$e->getMessage()); }
	}
	
	/*** read file_content ***/
	protected function file_content($file) {
		try {
			
			$this->file_handling($file, 'file_get_contents');
			
		}
		catch(Exception $e) { die('Error_method: '.__METHOD__.$e->getMessage()); }
	}
	
	protected function file_handling($file, $info='', $mode='', $resource='') {
		try {
			
			if(require_once(CLASS_ODT2XHTML_ROOT.'/class.file_handling.php')) {
				
				$this->handling = new file_handling(); 
				if($this->handling->verif_file($file)) {
					
					switch($info) {
						
						case 'fgets':
							$this->content = $this->handling->file_object($file, $info); 
						break;
						
						case 'file_get_contents':
							$this->content = $this->handling->file_infos($file, $info);
						break;
						
						case 'pathinfo':
							return $this->handling->file_infos($file, $info, $mode);
						break;
						
						case 'vars': 
							$this->file_obj = $this->handling->file_object($file); 
							
							if(method_exists($this->file_obj, 'getExtension')) {
								// PHP >= 5.3.2
								$this->odf['file']['ext'] = $this->file_obj->getExtension();	
							}
							else $this->odf['file']['ext'] = $this->handling->file_infos($file, 'pathinfo', 'ext');
							
							if(!empty($this->odf['file']['ext'])) {
								$this->valid_ext();
								
								$this->odf['file']['mime'] = $this->handling->file_infos($file, 'finfo', 'mime_type');
							
								if(!empty($this->odf['file']['mime'])) {
									$this->verif_mime_type($file);
								}
							}
							else die($this->message->display('ko', 'file_ext'));
							
							if(method_exists($this->file_obj, 'getBasename')) {
								// PHP >= 5.1.2
								$this->odf['file']['basename'] = $this->file_obj->getBasename();
								$this->odf['file']['name'] = $this->file_obj->getBasename('.'.$this->odf['file']['ext']); 	
							}
							else {
								$this->odf['file']['basename'] = $this->handling->file_infos($file, 'pathinfo', 'base');
								$this->odf['file']['name'] = $this->handling->file_infos($file, 'pathinfo', 'name');
							}
							
							if(!empty($this->odf['file']['name'])) {
								$this->odf['file']['html'] = $this->odf['file']['name'].'.html';
								$this->odf['file']['css'] = $this->odf['file']['name'].'.css'; 
							}
							else die($this->message->display('ko', 'file_name'));
											
						break;
						
						case 'verif':
							return TRUE;
						break;
						
						case 'write': 
							$this->handling->file_object($file, $info, $mode, $resource);
						break;
						
					}
					
				}
				elseif($info == 'write') { 
					$this->handling->file_infos($file, $info, $mode, $resource);
				}
				
			}
			
		}
		catch(Exception $e) { die('Error_method: '.__METHOD__.$e->getMessage()); }
	}
		
	protected function get_class_need() {
		try {
			
			require_once(CLASS_ODT2XHTML_ROOT.'/class.messaging.php');
			
			$this->message = new messaging();
			
		}
		catch(Exception $e) { die('Error_method: '.__METHOD__.$e->getMessage()); }
	}
	
	/*** make new file xml with ODT2XHTML files xml ***/
	private function make_new_xml() {
		try {
			
			$this->doctype = '<!DOCTYPE office:document-meta PUBLIC "-//OpenOffice.org//DTD OfficeDocument 1.0//EN" "office.dtd">';
			
			$this->xml_version = '<?xml version="1.0" encoding="UTF-8"?>';
			
			/*** open file xml ***/
			$this->build_xml('opener');
			
			/*** build corpus xml: meta, styles, content ***/
			if(!empty($this->odf['xml']['corpus']) && is_array($this->odf['xml']['corpus'])) {
				
				foreach($this->odf['xml']['corpus'] as $value) {
					
					$this->odf['file']['xml'] =  $this->odf['file']['dir']['tmp'].'/'.$value.'.xml';
					
					/*** modify the content ***/
					$this->build_xml($value); 
					
				}
				unset($value);
				
			}
			
			// close file xml
			$this->build_xml('closer');
			
			if(ODT2XHTML_DEBUG == true) { echo $this->message->display('ok', 'file_xml'); }
			
		}
		catch(Exception $e) { die('Error_method: '.__METHOD__.$e->getMessage()); }
	}
	
	/*** 
	 * 
	 * this function is run by method rewrite_position(), 
	 * to create an array $this->position
	 * 
	 ***/
	protected function make_position($match) {
		try {
			
			if(!empty($match) && is_array($match)) {
				foreach($match[1] as $key => $value) {
					
					$this->position['name'][$key] = $value;
					$this->position['string'][$key] = substr($match[2][$key], 0, 3);
					
				}
				unset($key, $value);
			}
			
		}
		catch(Exception $e) { die('Error_method: '.__METHOD__.$e->getMessage()); }
	}
	
	private function mk_dir_html() {
		try {
			
			$this->mk_dir_need($this->odf['dir']['out']['html'], 'html');
			
		}
		catch(Exception $e) { die('Error_method: '.__METHOD__.$e->getMessage()); }
	}
	
	private function mk_dir_img() {
		try {
			
			/*** making dir img path ***/
			$this->mk_dir_need($this->odf['dir']['out']['img'], 'img_path');
			
			/*** making dir img path php ***/
			$this->mk_dir_need($this->odf['dir']['out']['php'], 'img_path_php');
			
			/*** making dir img path php ext ***/
			$this->mk_dir_need($this->odf['dir']['out']['ext'], 'img_path_php_ext');
			
			/*** making dir img path php ext ooo ***/
			$this->mk_dir_need($this->odf['dir']['out']['OOo'], 'img_path_php_ext_ooo');
			
		}
		catch(Exception $e) { die('Error_method: '.__METHOD__.$e->getMessage()); }
	}
	
	protected function mk_dir_need($dir, $info) {
		try {
			
			// Use DirectoryIterator :p!
			if(!file_exists($dir) || ( file_exists($dir) && !is_dir($dir) ) ) {
				
				if(ODT2XHTML_PHPCLI != true) { 
					$umask = umask(); 
					umask(0000);
				}				
				
				if(!mkdir($dir, 0777)) {
					die($this->message->display('ko','making_dir_'.$info, $dir));
				}
				
				if(ODT2XHTML_PHPCLI != true && !empty($umask)) { umask($umask); }
			}
			else {
				chmod($dir, 0775);
				
				if(ODT2XHTML_DEBUG == true) { 
					echo $this->message->display('ok','making_dir_'.$info, $dir); 
				}
			}
			
		}
		catch(Exception $e) { die('Error_method: '.__METHOD__.$e->getMessage()); }
	}
	
	private function mk_dir_tmp() {
		try {
			
			$this->mk_dir_need($this->odf['dir']['out']['tmp'], 'odf_out_tmp');
			
		}
		catch(Exception $e) { die('Error_method: '.__METHOD__.$e->getMessage()); }
	}
	
	private function mk_dirs_needed() {
		try {
			
			/*** making temporary dir ***/
			$this->mk_dir_tmp();
			
			/*** making dir to receive file html ***/
			$this->mk_dir_html();
			
			/*** making directory to receive file img ***/
			$this->mk_dir_img();
					
			if(ODT2XHTML_DEBUG == true) { echo $this->message->display('ok','making_all_dir'); }
		
		}
		catch(Exception $e) { die('Error_method: '.__METHOD__.$e->getMessage()); }
	}
	
	/*** make code html to xlink:href ***/
	private function mk_xlink_href($name) {
		try {
			
			$this->code = 'xlink:href="'.$this->odf['dir']['img']['src'].'/php'.$this->php.'/'.$this->odf['file']['ext'].'/'.$this->odf['file']['name'].'/'.$name.'"';			
			
			if(!empty($this->code)) return $this->code;
			
		}
		catch(Exception $e) { die('Error_method: '.__METHOD__.$e->getMessage()); }
	}
	
	/*** modify appel css ***/
	private function modify_css() {
		try {
		
			if(!empty($this->odf['file']['css'])) $this->link_css = '<link rel="stylesheet" href="'.$this->odf['file']['css'].'" type="text/css" media="screen" title="Default" />';
			
			if(!empty($this->link_css)) return $this->link_css;
			
		}
		catch(Exception $e) { die('Error_method: '.__METHOD__.$e->getMessage()); }
	}
	
	/*** modify title code html ***/
	private function modify_title() {
		try {
			
			$this->title = '<head>'."\n\t";
			$this->title .= '<title>&quot;';
			
			if( (ODT2XHTML_TITLE == 'element_title') && !empty($this->html['title']) ) {
				$this->title .= $this->html['title'];
			}
			else $this->title .= $this->odf['file']['basename'];
			
			$this->title .= '&quot;';
			
			if(ODT2XHTML_PUB == true) $this->title .= ODT2XHTML_PUB;
			
			$this->title .= '</title>';
			
			if(!empty($this->title)) return $this->title;
			
		}
		catch(Exception $e) { die('Error_method: '.__METHOD__.$e->getMessage()); }
	}
	
	/*** move icon ***/
	private function mv_icon() {
		try {
			
			foreach($this->odf['icons'] as $v) {
				if(ODT2XHTML_PHPCLI == true) $this->source = ODT2XHTML_ROOT.$v;
				else $this->source = ODT2XHTML_ROOT.'/'.$v;
				
				if(!copy($this->source, $this->odf['dir']['out']['img'].'/'.$v)) {
					echo $this->message->display('ko', 'icon_copy', $v);
				}
				else {
					if(ODT2XHTML_DEBUG == true) {
						echo $this->message->display('ok', 'icon_copy', $v);
					}
				}
				
				unset($this->source);
			}
			unset($v);
			
		}
		catch(Exception $e) { die('Error_method: '.__METHOD__.$e->getMessage()); }
	}
	
	/*** make directory image and moving images ***/
	private function mv_img() {		
		try {
			// Use directoryIterator :p
			/*** move img ***/
			if(file_exists($this->odf['dir']['out']['pict']) 
				&& $this->handle = opendir($this->odf['dir']['out']['pict']))
			{			
				while( false !== $this->file['img'] = readdir($this->handle) )
				{
					if($this->verif_file($this->odf['dir']['out']['pict'].'/'.$this->file['img']))
					{					
						/*** move img at temp directory to img directory ***/
						if(rename($this->odf['dir']['out']['pict'].'/'.$this->file['img'], $this->odf['dir']['out']['OOo'].'/'.$this->file['img'])) {
							chmod($this->odf['dir']['out']['OOo'].'/'.$this->file['img'], 0644);
						
							if(ODT2XHTML_DEBUG == true) { echo $this->message->display('ok', 'moving_img', $this->file['img']); }
						}
						else die($this->message->display('ko','moving_img',$this->file['img']));
					}
				}
				closedir($this->handle);
			}
			
		}
		catch(Exception $e) { die('Error_method: '.__METHOD__.$e->getMessage()); }
	}
	
	/*** replace content ***/
	private function replace_content($info) {
		try {
			$this->exec = true;
			
			switch($info) {
				
				case 'analyze_attribute' :
					$this->search = '/="(.*?)"/es';
					$this->replace = "odt2xhtml::analyze_attribute('$1')";
					$this->subject = $this->content;
				break;
				
				case 'header' :
					$this->search = '!<office:forms(.*?)/>!';
					$this->replace = '<office:forms$1/>'.$this->header;
					$this->subject = $this->content;
				break;
				
				case 'img_odt' :
					$this->search = '#xlink:href="Pictures/([.a-zA-Z_0-9]*)"#es';
					$this->replace = "odt2xhtml::mk_xlink_href('$1')";
					$this->subject = $this->content;
				break;
				
				case 'img_sxw' :
					$this->search = '!xlink:href="\#Pictures/([.a-zA-Z_0-9]*)"!es';
					$this->replace = "odt2xhtml::mk_xlink_href('$1')";
					$this->subject = $this->content;
				break;
				
				case 'link_css' :
					$this->search = '/<style type="text\/css">(.*)<\/style>/es';
					$this->replace = "odt2xhtml::modify_css()";
					$this->subject = $this->file['tmp'];
				break;
				
				case 'open_element_xml4odt' :
					$this->exec = false;
					$this->buffer = '<office:document xmlns:office="urn:oasis:names:tc:opendocument:xmlns:office:1.0">';
				break;
				
				case 'open_element_xml4sxw' :
					$this->exec = false;
					$this->buffer = '<office:document xmlns:office="http://openoffice.org/2000/office">';
				break;
				
				case 'reference_mark' :
					$this->search = '/<text:reference-mark-start text:name="(.*)"\/>(.*)<text:reference-mark-end text:name="(.*)"\/>/SU';
					$this->replace = '<text:reference-mark text:name="$1">$2</text:reference-mark>';
					$this->subject = $this->content;
				break;
				
				case 'title' :
					$this->search = '/<head>/es';
					$this->replace = "odt2xhtml::modify_title()";
					$this->subject = $this->file['tmp'];
				break;
				
			}
		
			if($this->exec == true) $this->buffer = preg_replace($this->search, $this->replace, $this->subject);
			
			if(ODT2XHTML_DEBUG == true) { 
				if(is_array($this->buffer)) echo $this->message->display('ok', 'preg_replace', serialize($this->buffer));
				else echo $this->message->display('ok', 'preg_replace', $info); 
			}
			
			if(!empty($this->buffer)) return $this->buffer;
		
			unset($this->buffer, $this->exec);
			
		}
		catch(Exception $e) { die('Error_method: '.__METHOD__.$e->getMessage()); }
	}
	
	/*** search text in position indice or exposant and transform it ***/
	private function rewrite_position() {
		try {
			# search styles text-position
			switch($this->odf['file']['ext']) {
				
				case 'odt' :
				case 'ott' :
					$pattern = '`<style:style style:name="T([0-9]+)" style:family="text"><style:text-properties style:text-position="(.*?)"/></style:style>`es';
					if(preg_match_all($pattern, $this->content, $this->match)) {
						$this->make_position($this->match);
					}
				break;
				
				case 'sxw' :
				case 'stw' :
					$pattern = '`<style:style style:name="T([0-9]+)" style:family="text"><style:properties style:text-position="(.*?)"/></style:style>`es';
					if(preg_match_all($pattern, $this->content, $this->match)) {
						$this->make_position($this->match);
					}
				break;
				
			}
			unset($this->match);
		
			# search text relative to style text-position
			$pattern = '`<text:span text:style-name="T([0-9]+)">(.*?)</text:span>`es';
			if(!empty($this->position) && preg_match_all($pattern, $this->content, $this->match)) {
				foreach($this->match[1] as $key => $value) {
					
					if(in_array($value, $this->position['name'])) {
						foreach($this->position['name'] as $key2 => $value2) {
							
							if($value2 == $value) {
								# build search text:span
								$this->position['search'][$key2] = '<text:span text:style-name="T'.$this->position['name'][$key2].'">';
								$this->position['search'][$key2] .= $this->match[2][$key];
								$this->position['search'][$key2] .= '</text:span>';
								
								# build replace text:
								$this->position['replace'][$key2] = '<text:'.$this->position['string'][$key2].' text:style-name="T'.$this->position['name'][$key2].'">';
								$this->position['replace'][$key2] .= $this->match[2][$key];
								$this->position['replace'][$key2] .= '</text:'.$this->position['string'][$key2].'>';
							}
							
						}
						unset($key2, $value2);
					}
					
				}
				unset($key, $value);
			}
			unset($this->match);
		
			# replace search text position par replace text position
			if(!empty($this->position['search']) && is_array($this->position['search'])) {
				foreach($this->position['search'] as $key => $value) {
					$this->content = str_replace($value, $this->position['replace'][$key], $this->content);
				}
				unset($key, $value);
			}
			unset($this->position);
			
		}
		catch(Exception $e) { die('Error_method: '.__METHOD__.$e->getMessage()); }
	}
	
	/*** create constants ***/
	protected function set_constants() {
		try {
			
			if(ODT2XHTML_PHPCLI == true) {
				define('ODT2XHTML_OUT', ODT2XHTML_FRONTEND);	
			}
			else define('ODT2XHTML_OUT', ODT2XHTML_FRONTEND);
			
			define('ODT2XHTML_XSL_ROOT', CLASS_ODT2XHTML_ROOT.'/xsl');
			
			if(ODT2XHTML_DEBUG == true) echo $this->message->display('ok', 'odf_out', ODT2XHTML_OUT);
			
		}
		catch(Exception $e) { die('Error_method: '.__METHOD__.$e->getMessage()); }
	}
	
	protected function set_variables() {
		try {
			
			// obtain 1rst string php version
			$this->php = substr(PHP_VERSION, 0, 1);	
			
			$this->odf['ext'] = array (
				/*** OpenDocument extension ***/
				'odb', 'odc', 'odf', 'odg', 'odi', 'odp', 'ods', 'odt',
				'odm', 'otg', 'oth', 'otp', 'ots', 'ott',
				/*** StarOffice extension ***/
				'stc', 'std', 'sti', 'stw',
				'sxc', 'sxd', 'sxg', 'sxi', 'sxm', 'sxw',
			);
			$this->odf['mime'] = array (
			
				/*** OpenDocument Format ***/
				'odb' => 'application/vnd.oasis.opendocument.database',
				'odc' => 'application/vnd.oasis.opendocument.chart',
				'odf' => 'application/vnd.oasis.opendocument.formula',
				'odg' => 'application/vnd.oasis.opendocument.graphics',				
				'odi' => 'application/vnd.oasis.opendocument.image',
				'odp' => 'application/vnd.oasis.opendocument.presentation',				
				'ods' => 'application/vnd.oasis.opendocument.spreadsheet',
				'odt' => 'application/vnd.oasis.opendocument.text',
				
				/*** OpenDocument Template ***/			
				'odm' => 'application/vnd.oasis.opendocument.text-master',
				'otg' => 'application/vnd.oasis.opendocument.graphics-template',
				'oth' => 'application/vnd.oasis.opendocument.text-web',
				'otp' => 'application/vnd.oasis.opendocument.presentation-template',
				'ots' => 'application/vnd.oasis.opendocument.spreadsheet-template',
				'ott' => 'application/vnd.oasis.opendocument.text-template',
				
				/*** StarOffice Template ***/
				'stc' => 'application/vnd.sun.xml.calc.template',
				'std' => 'application/vnd.sun.xml.draw.template',
				'sti' => 'application/vnd.sun.xml.impress.template',	
				'stw' => 'application/vnd.sun.xml.writer.template',		
				
				/*** StarOffice Format ***/
				'sxc' => 'application/vnd.sun.xml.calc',
				'sxd' => 'application/vnd.sun.xml.draw',
				'sxg' => 'application/vnd.sun.xml.writer.global',
				'sxi' => 'application/vnd.sun.xml.impress',
				'sxm' => 'application/vnd.sun.xml.math',
				'sxw' => 'application/vnd.sun.xml.writer',
				
			);
			
			// extension valid
			//$this->odf['ext']['valid'] = array('odt', 'ott', 'stw', 'sxw');
			// icons valid
			$this->odf['icons'] = array ('favicon.ico','icone.png');
			// mime type valid
			/*
			$this->odf['mime']['valid'] = array (
				'application/vnd.oasis.opendocument.text',
				'application/vnd.oasis.opendocument.text-template',
				'application/vnd.sun.xml.writer.template',
				'application/vnd.sun.xml.writer',
			);
			* */
			// xml corpus
			$this->odf['xml']['corpus'] = array('meta', 'styles', 'content');
			
			// set variables files an dir
			$this->build_vars_file_handling(); 
			
			// set file xsl
			$this->choose_valid_xsl();
			
			if(ODT2XHTML_DEBUG == true) { var_dump($this->odf); }
			
		}
		catch(Exception $e) { die('Error_method: '.__METHOD__.$e->getMessage()); }
	} 
	
		/*** Unzip file ODT ***/
	private function unzip_odf() {
		try {
			
			if(class_exists('ZipArchive')) {	
				// Run with PHP Zip !
				if(ODT2XHTML_PHPCLI == true) {
					$this->zip = ODT2XHTML_OUT.$this->odf['file']['basename'];
				}
				else $this->zip = ODT2XHTML_OUT.$this->odf['file']['basename'];

				if(!empty($this->zip)) {
					$this->archive = new ZipArchive();

					if($this->archive->open($this->zip) !== TRUE) die($this->message->display('ko','zip_open',$this->zip));
			
					if( !$this->archive->extractTo($this->odf['file']['dir']['tmp']) ) {
						die($this->message->display('ko','zip_extract',$this->odf['file']['dir']['tmp'])); 
					}
					
					$this->archive->close();
				
					if(ODT2XHTML_DEBUG == true) { echo $this->message->display('ok','unzip', $this->zip); }
				}
			}
			else { // Run with PCLZIP !
				if(require_once(CLASS_ODT2XHTML_ROOT.'/pclzip/pclzip.lib.php') === false) {
					die($this->message->display('ko','pclzip_require'));
				}
				
				if(class_exists('PclZip')) {
					if(ODT2XHTML_PHPCLI == true) {
						$this->pclzip = ODT2XHTML_OUT.'/'.$this->odf['file']['basename'];
						
					}
					else $this->pclzip = ODT2XHTML_OUT.$this->odf['file']['basename'];
					
					if(!empty($this->pclzip)) {
						$this->archive = new PclZip($this->pclzip);
					
						if($this->archive->extract(PCLZIP_OPT_PATH, $this->odf['file']['dir']['tmp']) == 0) {
							die($this->message->display('ko','zip_extract',$this->odf['file']['dir']['tmp']).$this->archive->errorInfo(true));
						}
		
						if(ODT2XHTML_DEBUG == true) { echo $this->message->display('ok','unzip'); }
					}
				}
				else die($this->message->displaying('ko','zip_disabled'));
			}
			
		}
		catch(Exception $e) { die('Error_method: '.__METHOD__.$e->getMessage()); }
	}
	
	/*** Verify if extension is really odt ***/
	protected function valid_ext() {
		try {
				
			if(!in_array($this->odf['file']['ext'], $this->odf['ext'])) {
				die($this->message->display('ko','extension', $this->odf['file']['ext']));
			}
		
			if(ODT2XHTML_DEBUG == true) { echo $this->message->display('ok','extension', $this->odf['file']['ext']); }
			
		}
		catch(Exception $e) { die('Error_method: '.__METHOD__.$e->getMessage()); }
	}
	
	
	protected function verif_file($file) {
		try {
			
			if($this->file_handling($file, 'verif')) return TRUE;
			else return FALSE;
			
		}
		catch(Exception $e) { die('Error_method: '.__METHOD__.$e->getMessage()); }
	}
	
	protected function verif_mime_type($file) {
		try {
							
			if( in_array($this->odf['file']['ext'], $this->odf['ext']) ) {
				$this->mime = $this->odf['mime'][$this->odf['file']['ext']];
			}
			
			/***
			 * 
			 * if mime == odf mime type... or odf file mime == octet-stream
			 * 
			 * */
			if( ( !empty($this->mime) && (strcmp($this->mime, $this->odf['file']['mime']) == 0) ) 
				|| ( !empty($this->mime) && $this->odf['file']['mime'] == 'application/octet-stream') )
			{
				$this->odf['file']['mime'] = $this->mime;
				
				if(ODT2XHTML_DEBUG == true) {
					echo $this->message->display('ok', 'mime_type', $this->odf['file']['mime']);
				}
			}
			else die($this->message->display('ko', 'mime_type', $this->odf['file']['mime']));
			
			unset($this->ext, $this->mime);
			
		}
		catch(Exception $e) { die('Error_method: '.__METHOD__.' '.$e->getMessage()); }
	}
	
	protected function write_file($filename, $mode, $resource) {
		try {
			
			$this->file_handling($filename, 'write', $mode, $resource); 
		
		}
		catch(Exception $e) { die('Error_method: '.__METHOD__.$e->getMessage()); }
	}
	
	/*** PHP Convert XML ***/
	protected function xslt_convert_odf() {
		try {
			
			if($this->verif_file($this->odf['file']['xsl'])) {
				$dom = new DOMDocument();
				$dom->load($this->odf['file']['xsl']);	
		
				$xslt = new XSLTProcessor();
				$xslt->importStylesheet($dom);
				
				if($this->verif_file($this->odf['file']['tmp']['xml'])) {
					$dom = new DOMDocument();
					$dom->load($this->odf['file']['tmp']['xml']);
					
					$this->file['tmp'] = html_entity_decode($xslt->transformToXML($dom));
					
					if(ODT2XHTML_DEBUG == true && !empty($this->file['tmp']) ) { 
						echo $this->message->display('ok','convert_odf'); 
					}
				}
			} 
			
		}
		catch(Exception $e) { die('Error_method: '.__METHOD__.$e->getMessage()); }
	}
	
}
?>
