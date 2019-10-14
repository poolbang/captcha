<?php

namespace Swoft\Captcha;

use Swoft\Bean\Annotation\Mapping\Bean;
use Intervention\Image\Image;
use Intervention\Image\ImageManager;
use Intervention\Image\Gd\Font;
use Exception;
use Swoft\Stdlib\Helper\DirectoryHelper;
use Swoft\Stdlib\Helper\Str;

/**
 * Class Captcha
 * @Bean()
 */
class Captcha
{

    /**
     * @var ImageManager
     */
    protected $imageManager;

    /**
     * @var ImageManager->canvas
     */
    protected $canvas;
    /**
     * @var Image
     */
    protected $image;
    /**
     * @var array
     */
    protected $backgrounds = [];
    /**
     * @var array
     */
    protected $fonts = [];
    /**
     * @var array
     */
    protected $fontColors = [];
    /**
     * @var int
     */
    protected $length = 5;
    /**
     * @var int
     */
    protected $width = 120;
    /**
     * @var int
     */
    protected $height = 36;
    /**
     * @var int
     */
    protected $angle = 15;
    /**
     * @var int
     */
    protected $lines = 3;
    /**
     * @var string
     */
    protected $characters;
    /**
     * @var array
     */
    protected $text;
    /**
     * @var int
     */
    protected $contrast = 0;
    /**
     * @var int
     */
    protected $quality = 90;
    /**
     * @var int
     */
    protected $sharpen = 0;
    /**
     * @var int
     */
    protected $blur = 0;
    /**
     * @var bool
     */
    protected $bgImage = true;
    /**
     * @var string
     */
    protected $bgColor = '#ffffff';
    /**
     * @var bool
     */
    protected $invert = false;
    /**
     * @var bool
     */
    protected $sensitive = false;
    /**
     * @var bool
     */
    protected $math = false;
    /**
     * @var int
     */
    protected $textLeftPadding = 4;
    /**
     * @var string
     */
    protected $fontsDirectory;

    /**
     * Constructor
     *
     * @internal param Validator $validator
     */
    public function init()
    {
        $this->imageManager   = new ImageManager(['driver' => 'imagick']);
        $this->characters     = config('captcha.characters', ['1', '2', '3', '4', '6', '7', '8', '9']);
        $this->fontsDirectory = config('captcha.fontsDirectory', __DIR__ . '/../assets/fonts');
    }

    /**
     * @param string $config
     * @return void
     */
    protected function configure($config)
    {
        if (config('captcha.' . $config)) {
            foreach (config('captcha.' . $config) as $key => $val) {
                $this->{$key} = $val;
            }
        }
    }

    /**
     * @param string $config
     * @param string $key
     * @return CaptchaImage
     */
    public function create(string $config = 'default', string $key = '')
    {

        $this->backgrounds = $this->iteratorFile(__DIR__ . '/../assets/backgrounds');
        $this->fonts       = $this->iteratorFile($this->fontsDirectory);

        $this->configure($config);
        $generator    = empty($key) ? $this->generate() : $this->generateKey($key);
        $this->text   = $generator['value'];
        $this->canvas = $this->imageManager->canvas(
            $this->width,
            $this->height,
            $this->bgColor
        );
        if ($this->bgImage) {
            $this->image = $this->imageManager->make($this->background())->resize(
                $this->width,
                $this->height
            );
            $this->canvas->insert($this->image);
        } else {
            $this->image = $this->canvas;
        }
        if ($this->contrast != 0) {
            $this->image->contrast($this->contrast);
        }
        $this->text();
        $this->lines();
        if ($this->sharpen) {
            $this->image->sharpen($this->sharpen);
        }
        if ($this->invert) {
            $this->image->invert();
        }
        if ($this->blur) {
            $this->image->blur($this->blur);
        }
        return new CaptchaImage($generator['key'], $this->image);
    }

    /**
     * Image backgrounds
     *
     * @return string
     */
    protected function background(): string
    {
        return $this->backgrounds[rand(0, count($this->backgrounds) - 1)];
    }

    protected function generateKey(string $key): array
    {
        $bag = explode(' ', $key);
        return [
            'value' => $bag,
            'key'   => $key,
        ];
    }

    /**
     * Generate captcha text
     *
     * @return array
     * @throws Exception
     */
    protected function generate(): array
    {
        $characters = is_string($this->characters) ? str_split($this->characters) : $this->characters;
        $bag        = [];
        if ($this->math) {
            $x   = random_int(10, 30);
            $y   = random_int(1, 9);
            $bag = "$x + $y = ";
            $key = $x + $y;
            $key .= '';
        } else {
            for ($i = 0; $i < $this->length; $i++) {
                $char  = $characters[rand(0, count($characters) - 1)];
                $bag[] = $this->sensitive ? $char : Str::lower($char);
            }
            $key = implode('', $bag);
        }
        return [
            'value' => $bag,
            'key'   => $key,
        ];
    }

    /**
     * Writing captcha text
     *
     * @return void
     */
    protected function text(): void
    {
        $marginTop = $this->image->height() / $this->length;
        $text      = $this->text;
        if (is_string($text)) {
            $text = str_split($text);
        }
        foreach ($text as $key => $char) {
            $marginLeft = $this->textLeftPadding + ($key * ($this->image->width() - $this->textLeftPadding) / $this->length);
            $this->image->text($char, $marginLeft, $marginTop, function ($font) {
                /* @var Font $font */
                $font->file($this->font());
                $font->size($this->fontSize());
                $font->color($this->fontColor());
                $font->align('left');
                $font->valign('top');
                $font->angle($this->angle());
            });
        }
    }

    /**
     * Image fonts
     *
     * @return string
     */
    protected function font(): string
    {
        return $this->fonts[rand(0, count($this->fonts) - 1)];
    }

    /**
     * Random font size
     *
     * @return int
     */
    protected function fontSize(): int
    {
        return rand($this->image->height() - 10, $this->image->height());
    }

    /**
     * Random font color
     *
     * @return string
     */
    protected function fontColor(): string
    {
        if (!empty($this->fontColors)) {
            $color = $this->fontColors[rand(0, count($this->fontColors) - 1)];
        } else {
            $color = '#' . str_pad(dechex(mt_rand(0, 0xFFFFFF)), 6, '0', STR_PAD_LEFT);
        }
        return $color;
    }

    /**
     * Angle
     *
     * @return int
     */
    protected function angle(): int
    {
        return rand((-1 * $this->angle), $this->angle);
    }

    /**
     * Random image lines
     *
     * @return Image|ImageManager
     */
    protected function lines()
    {
        for ($i = 0; $i <= $this->lines; $i++) {
            $this->image->line(
                rand(0, $this->image->width()) + $i * rand(0, $this->image->height()),
                rand(0, $this->image->height()),
                rand(0, $this->image->width()),
                rand(0, $this->image->height()),
                function ($draw) {
                    /* @var Font $draw */
                    $draw->color($this->fontColor());
                }
            );
        }
        return $this->image;
    }

    /**
     * Searching for resource files
     * @param string $path
     * @return array
     */
    public function iteratorFile(string $path)
    {

        $arr    = [];
        $filter = function (\SplFileInfo $f): bool {
            $name = $f->getFilename();
            // Skip hidden files and directories.
            // Goon read sub-dir
            if (strpos($name, '.') === 0 || $f->isDir()) {
                return false;
            }
            // Only find php file
            return $f->isFile();
        };
        $colors = DirectoryHelper::filterIterator($path, $filter);
        $colors->rewind();

        while ($colors->valid()) {

            $arr[] = $colors->key();
            $colors->next();
        }
        return $arr;
    }
}