<?php
namespace component\core;

/**
 * Thumb
 * Simple thumbnail and watermark
 *
 * @throws \InvalidArgumentException
 * @package Moss Core Component
 * @author  Michal Wachowski <wachowski.michal@gmail.com>
 */
class Thumb {
	protected $thumbDir;
	protected $quality;
	protected $noImage;

	protected $treeDir = true;
	protected $noScaleUp = true;

	protected $sharpenMatrix = array(array(-1, -1, -1), array(-1, 16,-1), array(-1, -1, -1));

	protected $watermark;
	protected $watermarkPositionX;
	protected $watermarkPositionY;

	/**
	 * Constructor
	 *
	 * @param string $trgDir path to thumbnail directory
	 * @param int $quality quality of thumbnails
	 * @param string $noImage path to alternative image, used when error occurred
	 * @param null|string $watermark path to watermark image
	 * @param int $watermarkPositionX horizontal watermark position - positive value - from left edge, negative - from right
	 * @param int $watermarkPositionY vertical watermark position - positive value - from top edge, negative - from bottom
	 */
	public function __construct($trgDir, $quality = 90, $noImage = './image/no_image.png', $watermark = null, $watermarkPositionX = 0, $watermarkPositionY = 0) {
		$this->thumbDir = $trgDir;
		$this->quality = (int) $quality;
		$this->noImage = $noImage;

		$this->watermark = $watermark;
		$this->watermarkPositionX = (int) $watermarkPositionX;
		$this->watermarkPositionY = (int) $watermarkPositionY;
	}

	/**
	 * Creates thumbnail
	 * If only one dimension set - image will be scaled to fit into that dimension.
	 * If both dimensions set - image will be scaled to fit into both
	 * If cropped is set to true - image will be scaled, parts not fitting dimension will be cropped
	 *
	 * @param string $srcName path to source image
	 * @param null|int $trgImgWidth maximal thumbnail width
	 * @param null|int $trgImgHeight maximal thumbnail height
	 * @param bool $cropped if true, image will be cropped
	 * @param bool $write if false, image will be send to stdOutput
	 * @return string
	 */
	public function make($srcName, $trgImgWidth = null, $trgImgHeight = null, $cropped = false, $write = true) {
		$fInfo = null;
		$mime = null;
		if(!is_file($srcName) || !($fInfo = new \finfo(FILEINFO_MIME)) || !($mime = $fInfo->file(realpath($srcName)))) {
			$srcName = $this->noImage;
		}

		$preserveTransparency = strpos($mime, 'image/gif') !== false || strpos($mime, 'image/png') !== false;

		$trgName = $this->resolveTargetImage($srcName, $trgImgWidth, $trgImgHeight, $cropped, $preserveTransparency);

		if($write && is_file($trgName)) {
			return $trgName;
		}

		$iInfo = getimagesize($srcName);
		if(!$this->watermark && $iInfo[0] == $trgImgWidth && $iInfo[1] == $trgImgHeight) {
			return $srcName;
		}

		$srcImg = $this->readImage($srcName, $iInfo['mime']);
		$trgImg = $this->resize($srcImg, $trgImgWidth, $trgImgHeight, $cropped);

		if($this->watermark) {
			$trgImg = $this->appendWatermark($trgImg);
		}

		if($write) {
			$preserveTransparency ? imagepng($trgImg, $trgName, $this->quality / 10, PNG_ALL_FILTERS) : imagejpeg($trgImg, $trgName, $this->quality);
		}
		else {
			ob_start();
			$preserveTransparency ? imagepng($trgImg, null) : imagejpeg($trgImg, null);
			return ob_get_clean();
		}

		imagedestroy($trgImg);
		return $trgName;
	}

	/**
	 * Returns header for thumbnails (jpg or png)
	 * Header is based on source image MIME type
	 *
	 * @param $srcName
	 * @return string
	 */
	public function thumbnailHeader($srcName) {
		$fInfo = null;
		$mime = null;
		if(!is_file($srcName) || !($fInfo = new \finfo(FILEINFO_MIME)) || !($mime = $fInfo->file(realpath($srcName)))) {
			$srcName = $this->noImage;
		}

		$preserveTransparency = strpos($mime, 'image/gif') !== false || strpos($mime, 'image/png') !== false;

		return $preserveTransparency ? 'Content-Type: image/png' : 'Content-Type: image/jpg';
	}

	/**
	 * Reads image
	 *
	 * @throws \InvalidArgumentException
	 * @param string $image path to image
	 * @param null|string $mime image MIME
	 * @return \resource
	 */
	protected function readImage($image, $mime = null) {
		if(!$mime) {
			$info = getimagesize($image);
			$mime = $info['mime'];
		}
		switch($mime) {
			case "image/jpeg":
			case "image/jpg":
				$srcImg = imagecreatefromjpeg($image);
			break;
			case "image/gif":
				$srcImg = imagecreatefromgif($image);
			break;
			case "image/png":
				$srcImg = imagecreatefrompng($image);
			break;
		}

		if(!isset($srcImg)) {
			throw new \InvalidArgumentException('Source image could not be set');
		}

		imagealphablending($srcImg, true);

		return $srcImg;
	}

	/**
	 * Resizes image
	 *
	 * @throws \InvalidArgumentException
	 * @param string $srcImg path to image
	 * @param int $trgImgWidth maximal width
	 * @param int $trgImgHeight maximal height
	 * @param bool $cropped
	 * @return \resource
	 */
	protected function resize($srcImg, $trgImgWidth, $trgImgHeight, $cropped) {
		$srcImgWidth = imagesx($srcImg);
		$srcImgHeight = imagesy($srcImg);

		if($cropped && $trgImgWidth && $trgImgHeight) {
			$ratio = max($trgImgWidth / $srcImgWidth, $trgImgHeight / $srcImgHeight);
		}
		elseif(!$cropped && $trgImgWidth && $trgImgHeight) {
			$ratio = min($trgImgWidth / $srcImgWidth, $trgImgHeight / $srcImgHeight);
		}
		elseif($trgImgWidth && !$trgImgHeight) {
			$ratio = $trgImgWidth / $srcImgWidth;
			$trgImgHeight = $srcImgHeight * $ratio;
		}
		elseif(!$trgImgWidth && $trgImgHeight) {
			$ratio = $trgImgHeight / $srcImgHeight;
			$trgImgWidth = $srcImgWidth * $ratio;
		}
		else {
			throw new \InvalidArgumentException('Invalid scale?');
		}

		if(!$cropped && $this->noScaleUp && $ratio > 1) {
			$ratio = 1;
		}

		$resImgWidth = ceil($srcImgWidth * $ratio);
		$resImgHeight = ceil($srcImgHeight * $ratio);

		$resImgX = 0;
		$resImgY = 0;

		if($cropped) {
			$resImgX = ($trgImgWidth - $resImgWidth) / 2;
			$resImgY = ($trgImgHeight - $resImgHeight) / 2;
		}
		else {
			$trgImgWidth = $resImgWidth;
			$trgImgHeight = $resImgHeight;
		}

		$trgImg = imagecreatetruecolor($trgImgWidth, $trgImgHeight);

		$transparency = imagecolorallocate($trgImg, 255, 255, 255);
		imagecolortransparent($trgImg, $transparency);
		imagefill($trgImg, 0, 0, $transparency);

		imagecopyresampled($trgImg, $srcImg, $resImgX, $resImgY, 0, 0, $resImgWidth, $resImgHeight, $srcImgWidth, $srcImgHeight);

		imagedestroy($srcImg);

		if($ratio < 0.75) {
			imageconvolution($trgImg, $this->sharpenMatrix, 8, 0);
		}

		return $trgImg;
	}

	/**
	 * Appends watermark on image
	 *
	 * @param \resource $image
	 * @return \resource
	 */
	protected function appendWatermark($image) {
		try {
			$watermark = $this->readImage($this->watermark);
		}
		catch(\InvalidArgumentException $e) {
			return $image;
		}

		$x = $this->watermarkPositionX < 0 ? imagesx($image) - imagesx($watermark) + $this->watermarkPositionX : $this->watermarkPositionX;
		$y = $this->watermarkPositionY < 0 ? imagesy($image) - imagesy($watermark) + $this->watermarkPositionY : $this->watermarkPositionY;

		imagecopyresampled($image, $watermark, $x, $y, 0, 0, imagesx($watermark), imagesy($watermark), imagesx($watermark), imagesy($watermark));

		return $image;
	}

	/**
	 * Resolves thumbnail name
	 *
	 * @param string $src
	 * @param null|int $width
	 * @param null|int $height
	 * @param bool $cropped
	 * @param bool $transparency
	 * @return string
	 */
	protected function resolveTargetImage($src, $width = null, $height = null, $cropped = false, $transparency = false) {
		if(!$width) {
			$width = 'auto';
		}

		if(!$height) {
			$height = 'auto';
		}

		$dir = explode('/', $this->thumbDir);
		$path = str_replace($dir, null, dirname($src));
		$path = trim($path, '\\/');

		$path = preg_replace('#[^\d\w]#', '_', dirname($path));

		$name = explode('.', basename($src));
		array_pop($name);
		$name = implode('', $name);
		$name = $this->strip($name);

		$name = sprintf('%s_%s_%sx%s_%s.%s', $path, $name, $width, $height, $cropped ? 'c' : 'n', $transparency ? 'png' : 'jpg');
		$name = str_replace('__', '_', $name);
		if(strpos($name, '_') === 0) {
			$name = substr($name, 1);
		}

		$path = $this->thumbDir;

		if($this->treeDir) {
			$path .= $name[0].'/'.(isset($name[1]) ? $name[1].'/' : null);
		}

		$this->makePath($path);

		$name = str_replace('//', '/', $path.$name);

		return $name;
	}

	/**
	 * Strips string from non ASCII chars
	 *
	 * @param string $string string to strip
	 * @param string $separator char replacing non ASCII chars
	 * @return string
	 */
	protected function strip($string, $separator = '-') {
		$string = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $string);
		$string = strtolower($string);
		$string = preg_replace('#[^\w\. \-]+#i', null, $string);
		$string = preg_replace('/[ -]+/', $separator, $string);
		$string = trim($string, '-.');

		return $string;
	}

	/**
	 * Creates defined directory if does not exists
	 *
	 * @param string $path path to directory
	 * @return mixed
	 * @throws \RuntimeException
	 */
	protected function makePath($path) {
		if(is_dir($path)) {
			return;
		}

		if(!mkdir($path, 0644, true)) {
			throw new \RuntimeException(sprintf('Unable to create cache dir %s', $path));
		}
	}
}