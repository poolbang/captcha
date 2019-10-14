<?php


namespace Swoft\Captcha;

use Intervention\Image\Image;

/**
 * Class CaptchaImage
 */
class CaptchaImage
{
    /**
     * @var string
     */
    private $key;

    /**
     * @var Image
     */
    private $image;

    public function __construct(string $key, Image $image )
    {
        $this->key = $key;
        $this->image= $image;
    }

    /**
     * @return string
     */
    public function getKey(): string
    {
        return $this->key;
    }

    /**
     * @param string $key
     */
    public function setKey(string $key)
    {
        $this->key = $key;
    }

    /**
     * @return Image
     */
    public function getImage(): Image
    {
        return $this->image;
    }

    /**
     * @param Image $image
     */
    public function setImage(Image $image)
    {
        $this->image = $image;
    }

}