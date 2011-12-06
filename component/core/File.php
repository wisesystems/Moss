<?php
namespace component\core;

/**
 * File as object representation
 * Extends SplFileObject
 *
 * @throws \InvalidArgumentException|\RangeException
 * @package Moss Core Component
 * @author  Michal Wachowski <wachowski.michal@gmail.com>
 */
class File extends \SplFileObject {

	protected $file;
	protected $realpath;

	/**
	 * Creates file object instance
	 *
	 * @param string $file path to file
	 * @param string $open_mode mode in which to open the file
	 */
	public function __construct($file, $open_mode = 'r') {
		parent::__construct(realpath($file), $open_mode);
		$this->file = $file;
		$this->realpath();
	}

	/**
	 * Retrieves entity data as associative array
	 * Only filename is retrieved
	 *
	 * @abstract
	 * @return array
	 */
	public function retrieve() {
		return array('file' => $this->file);
	}

	/**
	 * Reads file content
	 *
	 * @throws \RangeException
	 * @return bool|string
	 */
	public function read() {
		if(!$data = file_get_contents($this->file)) {
			throw new \RangeException('File '.$this->file.' can not be read');
		}

		return $data;
	}

	/**
	 * Writes file content
	 *
	 * @throws \RangeException
	 * @param string $data data to be written
	 * @return File
	 */
	public function write($data) {
		if(!is_dir(dirname($this->file)) && mkdir(dirname($this->file), null, true)) {
			throw new \RangeException('Target directory '.dirname($this->file).' can not be created');
		}

		if(!file_put_contents($this->file, $data)) {
			throw new \RangeException('File '.$this->file.' can not be written');
		}

		return $this;
	}

	/**
	 * Deletes file
	 *
	 * @throws \RangeException
	 * @return File
	 */
	public function delete() {
		if(!is_file($this->file)) {
			throw new \RangeException('File '.$this->file.' not found');
		}

		if(!unlink($this->file)) {
			throw new \RangeException('File '.$this->file.' can not be deleted');
		}

		return $this;
	}

	/**
	 * Retrieves file MIMe type
	 * 
	 * @return string
	 */
	public function mime() {
		$fp = new \finfo(FILEINFO_MIME);
		return $fp->file($this->realpath);
	}

	public function header() {
		$fp = new \finfo(FILEINFO_MIME);
		return 'Content-type: '.$fp->file($this->realpath);
	}

	/**
	 * Retrieves file real path to file
	 *
	 * @return string
	 */
	public function realpath() {
		if(!$this->realpath) {
			$this->realpath = str_replace(array('\\', '/'), '/', realpath($this->file));
		}

		return $this->realpath;
	}

	/**
	 * Uploads file and reassigns association and returns its name
	 *
	 * @throws \InvalidArgumentException
	 * @param string $field $_FILES field name
	 * @param null|string $path target path
	 * @return null|string
	 */
	public function upload($field, $path = null) {
		if(!isset($_FILES[$field]) || $_FILES[$field]['error']['file'] || !$_FILES[$field]['size']['file']) {
			return null;
		}

		$trgName = str_replace('//', '/', $path.'/'.$this->getFileName($_FILES[$field]['name']['file']));

		if(!move_uploaded_file($_FILES[$field]['tmp_name']['file'], $trgName)) {
			throw new \InvalidArgumentException('Error occurred during upload');
		}

		return new File($trgName);
	}

	/**
	 * Returns file name
	 * If files already exists modifies its name, adds numeric suffix
	 *
	 * @param string $file original filename
	 * @return string
	 */
	public function makeFileName($file) {
		$tArr = explode('.', basename($file));
		$fileExtension = array_pop($tArr);
		$fileName = implode('.', $tArr);
		unset($tArr);

		$dirContent = array();
		foreach(new \DirectoryIterator(dirname($file)) as $item) {
			if($item->isDot()) {
				continue;
			}

			$dirContent[] = (string) $item;
		}

		$postfix = 0;
		$newName = $this->joinFileName($fileName, $fileExtension, $postfix);
		while(in_array($newName, $dirContent)) {
			$newName = $this->joinFileName($fileName, $fileExtension, $postfix++);
		}

		var_dump($newName);
		return $newName;
	}

	/**
	 * Joins file name into string including postfix
	 * If postfix is greater than 0, will be inserted before extension, otherwise is ignored
	 *
	 * @param string $name file name
	 * @param string $extension file extension
	 * @param int $postfix decimal postfix
	 * @return string
	 */
	private function joinFileName($name, $extension, $postfix = 0) {
		return $name.($postfix ? '_'.$postfix : null).'.'.$extension;
	}

	/**
	 * Strips non ASCII data from filename
	 *
	 * @param string $filename original filename
	 * @param string $separator replaces spaces
	 * @return string
	 */
	protected function strip($filename, $separator = '-') {
		$filename = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $filename);
		$filename = strtolower($filename);
		$filename = preg_replace('#[^\w\. \-]+#i', null, $filename);
		$filename = preg_replace('/[ -]+/', $separator, $filename);
		$filename = trim($filename, '-');

		return $filename;
	}
}
