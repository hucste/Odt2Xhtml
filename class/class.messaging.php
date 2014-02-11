<?php
/***
 * class PHP display_message : to display message in class odt2xhtml
 * Copyright (C) 2011  Stephane HUC
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or (at your option) any later version.
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
/***
 * 
 * Use this class: 
 * 
 * $value = 'ok'; 	// value is 'ok', or 'ko'
 * $index = 'index_key_mssg'; 	// index is a key in files mssg 
 * $info = ''; 	// all information that you desire to displaying
 * 
 * $this->mssg = new messaging();
 * 
 * $this->mssg->display($value, $index, '');
 * 
 * */

define('CLASS_MSSG_ROOT', dirname(__FILE__));
define('CLASS_MSSG_LANG', 'en');
define('CLASS_MSSG_FILES_LANG', CLASS_MSSG_ROOT.'/lang/'.CLASS_MSSG_LANG.'/');

if(!defined('CLASS_MSSG_PHPCLI')) define('CLASS_MSSG_PHPCLI', 0);


class messaging {
	
	private $content;
	private $ko;
	private $ok;
	private $mssg;

	public function __construct() {
		
		$this->file_mssg = array (
			'ko.cfg',
			'ok.cfg',
		);
		
		$this->get_files_need();
		$this->set_variables();

	}

	public function __destruct() {
		unset($this->mssg);
	}
	
	/***
		$value 
		$index
		$var : is variable to obtain in method
	***/
	public function display($value, $index, $info='') {
		try {
			
			if(CLASS_MSSG_PHPCLI == true) {
				$color = array (
					'blue' => "\033[34m",
					'green' => "\033[32m",
					'red' => "\033[31m",
				);
			}
			
			switch($value) {
				case 'ko' :
					$this->mssg = '<p>';
					
					if(!empty($info)) {
						$this->mssg .= $this->ko[$index];
						
						if(CLASS_MSSG_PHPCLI == true) $this->mssg .= ' '.$color['blue'].$info."\033[37m ";
						else $this->mssg .= ' (<strong>'.$info.'</strong>)';
					}
					else $this->mssg .= $this->ko[$index];
					
					if(CLASS_MSSG_PHPCLI == true) $this->mssg .= ' '.$color['red'].'KO'."\033[37m ";
					else $this->mssg .= ' <strong style="color:red;">KO</strong>!</p>';
				break;
				
				case 'ok' :
					$this->mssg = '<p>';
					
					if(!empty($info)) {
						$this->mssg .= $this->ok[$index];
						
						if(CLASS_MSSG_PHPCLI == true) $this->mssg .= ' '.$color['blue'].$info."\033[37m ";
						else $this->mssg .= ' (<strong>'.$info.'</strong>)';
					}
					else $this->mssg .= $this->ok[$index];
					
					if(CLASS_MSSG_PHPCLI == true) $this->mssg .= ' '.$color['green'].'OK'."\033[37m ";
					else $this->mssg .= ' <strong style="color:green;">OK</strong>!</p>';
				break;
			}
		
			if(CLASS_MSSG_PHPCLI == TRUE) return strip_tags($this->mssg)."\n";
			else return $this->mssg."\n";
			
		}
		catch(Exception $e) { die('Error_method: '.__METHOD__.$e->getMessage()); }
	}
		
	private function get_files_need() {
		try {
			
			require_once(CLASS_MSSG_ROOT.'/class.file_handling.php');
			
			if(!empty($this->file_mssg) && is_array($this->file_mssg)) {
				foreach($this->file_mssg as $key => $value) {

					$var = substr($value, 0, 2); 

					$this->file_name = CLASS_MSSG_FILES_LANG.$value; 
					
					$this->file = new file_handling(); 
					if($this->file->verif_file($this->file_name)) {
						$this->content[$var] = $this->file->file_object($this->file_name, 'fgets'); 	
					}
					
				}
				unset($key, $value); 				
			}
			
		}
		catch(Exception $e) { die('Error_method: '.__METHOD__.$e->getMessage()); }
	}
	
	private function set_variables() {
		try {
			
			if(!empty($this->content)) {
				foreach($this->file_mssg as $key => $value) {
					$var = substr($value, 0, 2);
					
					foreach($this->content[$var] as $key1 => $value1) {
					
						if(!empty($value1)) {
							if(strpos($value1, '#') === false) {
								unset($this->content[$var][$key1]);
						
								$x = explode('=', $value1);
						
								if(is_array($x)) {
									$k = trim($x[0]);
									$v = trim($x[1]);
									$v = htmlentities($v, ENT_QUOTES);
									$this->content[$var][$k] = $v;
								}
								unset($x, $k, $v);
							}
							else unset($this->content[$var][$key1]);
						}
						else unset($this->content[$var][$key1]);
						
						if(!empty($this->content[$var])) {
							$$var = $this->content[$var];
						}
						
					}
					unset($key1, $value1); 
				}
				
				if(!empty($ok)) $this->ok = $ok;
				if(!empty($ko)) $this->ko = $ko;
				
			}
			
		}
		catch(Exception $e) { die('Error_method: '.__METHOD__.$e->getMessage()); }
	}
		
}
?>
