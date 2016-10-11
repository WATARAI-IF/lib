<?php
/**
 * User: n-sato
 * Date: 14/05/19
 */

class GDManager{
	protected $img;
	protected $width;
	protected $height;
	protected $img_type;
	protected $file_extension;

	function __construct($src_path) {
		list($width, $height, $image_type) = getImageSize($src_path);

		switch ($image_type){
			case IMAGETYPE_JPEG:
				$this->file_extension = "jpeg";
				$this->img = imagecreatefromjpeg($src_path);
				break;
			case IMAGETYPE_PNG:
				$this->file_extension = "png";
				$this->img = imagecreatefrompng($src_path);
				break;
			case IMAGETYPE_GIF:
				$this->file_extension = "gif";
				$this->img = imagecreatefromgif($src_path);
				break;
			default:
				return false;
		}

		$this->width = $width;
		$this->height = $height;
		$this->img_type = $image_type;
	}

	function __destruct(){
		imagedestroy($this->img);
	}

	/**
	 * @param $thumb_width
	 * @param $thumb_height
	 */
	public function createThumb($thumb_width, $thumb_height){

		$src_rate = $this->height / $this->width;
		$thumb_rate = $thumb_height /$thumb_width;

		if($src_rate > $thumb_rate){
			$thumb_width = $thumb_height / $src_rate;
		}else if($src_rate < $thumb_rate){
			$thumb_height = $thumb_width * $src_rate;
		}

		$thumb_image = imagecreatetruecolor($thumb_width, $thumb_height);
		imagecopyresampled($thumb_image, $this->img, 0, 0, 0, 0, $thumb_width, $thumb_height, $this->width, $this->height);

		imagedestroy($this->img);
		$this->img = $thumb_image;
	}

	/**
	 * @param $text
	 * @param $pos_x
	 * @param $pos_y
	 * @param $size
	 * @param array $color
	 * @param $font
	 */
	public function addText($text, $pos_x, $pos_y, $size, array $color, $font){
		$color_red = isset($color['red']) ? $color['red'] : 0;
		$color_green = isset($color['green']) ? $color['green'] : 0;
		$color_blue = isset($color['blue']) ? $color['blue'] : 0;

		$font_color = ImageColorAllocate($this->img, $color_red, $color_green, $color_blue);
		ImageTTFText($this->img, $size, 0, $pos_x, $pos_y, $font_color, $font, $text);
	}

    /**
     * @param $text
     * @param $size
     * @param $font
     * @return mixed
     */
    public function getTextWidth($text, $size, $font){
        $pos = imageftbbox($size, 0, $font, $text);
        return $pos[4] - $pos[6];
    }


    /**
     * @param $img_src
     * @param $pos_x
     * @param $pos_y
     * @return bool
     */
    public function addImage($img_src, $pos_x, $pos_y){
        list($width, $height, $image_type) = getImageSize($img_src);

        switch ($image_type){
            case IMAGETYPE_JPEG:
                $img = imagecreatefromjpeg($img_src);
                break;
            case IMAGETYPE_PNG:
                $img = imagecreatefrompng($img_src);
                break;
            case IMAGETYPE_GIF:
                $img = imagecreatefromgif($img_src);
                break;
            default:
                return false;
        }

        return imagecopy($this->img, $img, $pos_x, $pos_y, 0, 0, $width, $height);

    }

	/**
	 *
	 */
	public function showImage($filename = ''){
		$show_image = "image{$this->file_extension}";

		header('Content-Type: image/'.$this->file_extension);
        if($filename){
            header('Content-Disposition: inline; filename=' . $filename);
        }

		$show_image($this->img);
	}

	/**
	 *
	 */
	public function showHeader($filename = ''){
		$show_image = "image{$this->file_extension}";

		header('Content-Type: image/'.$this->file_extension);
        if($filename){
            header('Content-Disposition: attachment; filename=' . $filename);
        }
	}

	/**
	 * @return string
	 */
	public function getBase64Image(){
		$show_image = "image{$this->file_extension}";
		ob_start();
		$show_image($this->img);
		return base64_encode(ob_get_clean());
	}

	/**
	 * @param $dest_path
	 */
	public function saveImage($dest_path){
		$save_image = "image{$this->file_extension}";
		$save_image($this->img, $dest_path);
	}

	/**
	 * @return string
	 */
	public function getFileExtension() {
		return $this->file_extension;
	}

	/**
	 * @return mixed
	 */
	public function getHeight() {
		return $this->height;
	}

	/**
	 * @return mixed
	 */
	public function getImgType() {
		return $this->img_type;
	}

	/**
	 * @return mixed
	 */
	public function getWidth() {
		return $this->width;
	}

}