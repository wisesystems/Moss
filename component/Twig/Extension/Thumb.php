<?php
class Twig_Extension_Thumb extends \Twig_Extension {

	/**
	 * @var \component\core;
	 */
	protected $Thumb;

	/**
	 * @param \component\core $Thumb
	 */
	public function __construct(\component\core\Thumb $Thumb) {
		$this->Thumb = &$Thumb;
	}

	/**
	 * @return array
	 */
	public function getFunctions() {
		return array(
			'Thumb' => new \Twig_Function_Method($this, 'Thumb')
		);
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
	 * @return string
	 */
	public function Thumb($srcName, $trgImgWidth = null, $trgImgHeight = null, $cropped = false) {
		return $this->Thumb->make($srcName, $trgImgWidth, $trgImgHeight, $cropped);
	}

	/**
	 * @return string
	 */
	public function getName() {
		return 'Thumb';
	}
}