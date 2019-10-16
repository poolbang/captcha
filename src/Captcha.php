<?php

namespace Poolbang\Captcha;


/**
 * Class Captcha
 */
class Captcha
{
    /**
     * @param string $config
     * @return CaptchaImage
     */
    public static function create($config = 'default')
    {
        return \Swoft::getBean(CaptchaBuilder::class)->create($config);
    }
}