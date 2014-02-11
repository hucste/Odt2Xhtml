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
 * Use the class: 
 * 
 * $this->file = new file_handling();
 * 
 * if($this->file->verif_file($file)) $this->file->file_object($file);
 * 
***/ 
class file_handling {
	
	private $extensions=array();
	
	public function __construct() {
	}
	
	public function __destruct() {		
	}
	
	public function dir_object($path) {
		try {
			
			$this->it = new DirectoryIterator($path);
			
			return $this->it;
			
		}
		catch(Exception $e) { die('Error_method: '.__METHOD__.' '.$e->getMessage()); }
	}
	
	/*** obtain file info ***/
	public function file_infos($file, $info, $mode='', $resource='') {
		try {
			
			if(!empty($info) && is_string($info)) {
				switch($info) {
				
					case 'file_get_contents':
						return file_get_contents($file);
					break;
				
					case 'finfo':
						if(!empty($mode) && is_string($mode)) {
							switch($mode) {
						
								case 'mime':
									$this->finfo = finfo_open(FILEINFO_MIME);
								break;
						
								case 'mime_encoding':
									$this->finfo = finfo_open(FILEINFO_MIME_ENCODING);
								break;
								
								case 'mime_type':
									$this->finfo = finfo_open(FILEINFO_MIME_TYPE);
								break;
								
							}
							
							if(!empty($this->finfo)) {
								$this->mime = finfo_file($this->finfo, $file);
								finfo_close($this->finfo);
							}
							
							if(!empty($this->mime)) return $this->mime;
						}
					break;
				
					case 'pathinfo':
						$this->pathinfo = pathinfo($file);
					
						if(!empty($mode) && is_string($mode)) {
							switch($mode) {
				
								case 'base': 	return $this->pathinfo['basename']; 	break;
							
								case 'dir': 	return $this->pathinfo['dirname']; 	break;
						
								case 'ext': 	return $this->pathinfo['extension']; 	break;
						
								case 'name': 	return $this->pathinfo['filename']; 	break;
						
								default: 	return $this->pathinfo; 	break;
				
							}
						}
					break;
				
					case 'write':
						if( !$this->file->isFile() ||
							$this->file->isFile() && $this->file->isWritable() ) {
							
							$handle = $this->file->openFile($mode); 	
							$handle->fwrite($resource);
							//fclose($handle);

						}
					break;
				
				}
			
				unset($file);
			}
			
		}
		catch(Exception $e) { die('Error_method: '.__METHOD__.' '.$e->getMessage()); }
	}
	
	/*** obtain file content ***/
	public function file_object($file, $info='', $mode='', $resource='') {
		try {
			
			$this->file = new SplFileObject($file);
				
			switch($info) {
				
				case 'fgets':
					while(!$this->file->eof()) { $lines[] = trim($this->file->fgets()); }
					$this->lines = $lines; 
					return $this->lines;
				break;
				
				case 'write': 
					if( $this->file->isFile() && $this->file->isWritable() ) { 
						$handle = $this->file->openFile($mode); 	
						$handle->fwrite($resource);
					}
				break;
				
				default:
					return $this->file;
				break;
			
			}
			
			unset($file);
			
		}
		catch(Exception $e) { die('Error_method: '.__METHOD__.' '.$e->getMessage()); }
	}
	
	private function set_variables() {
		try {
			
			
			
		}
		catch(Exception $e) { die('Error_method: '.__METHOD__.' '.$e->getMessage()); }
	}
		
	/*** verif file ***/
	public function verif_dir($file) {
		try {
			
			$this->file = new SplFileInfo($file);
			if( !$this->file->isDir() 
				|| ( $this->file->isDir() && !$this->file->isReadable() )
				|| ( $this->file->getOwner() != fileowner(__FILE__) ) ) 
			{
				return FALSE;
			}
			else return TRUE;
			
		}
		catch(Exception $e) { die('Error_method: '.__METHOD__.$e->getMessage()); }
	}
	
	/*** verif file ***/
	public function verif_file($file) {
		try {
			
			$this->file = new SplFileInfo($file);
			if( !$this->file->isFile() 
				|| ( $this->file->isFile() && !$this->file->isReadable() )
				|| ( $this->file->getOwner() != fileowner(__FILE__) ) ) 
			{
				return FALSE;
			}
			else return TRUE;
			
		}
		catch(Exception $e) { die('Error_method: '.__METHOD__.$e->getMessage()); }
	}
	
 }
?>
