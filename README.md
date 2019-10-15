# Captcha for Swoft

## Installation

```
composer require poolbang/captcha
```

## Configuration

To use your own settings, create captcha config.

`config/captcha.php`

```php
return [
    'default'   => [
        'length'    => 5,
        'width'     => 120,
        'height'    => 36,
        'quality'   => 90,
        'math'      => true, //Enable Math Captcha
    ],
    // ...
];
```

## Example Usage
```php

    /**
     * @RequestMapping(route="captcha")
     * @return \Swoft\Http\Message\Response|static
     */
    public function image(){
       $captcha =  Captcha::create();
       echo $captcha->getKey().PHP_EOL; // generate captcha code
        return context()->getResponse()->withContent($captcha->getImage()->encode($captcha->getImage()->extension,
            90)->encoded)->withContentType($captcha->getImage()->mime()); // show captcha image
    }
```

Based on [Intervention Image](https://github.com/Intervention/image)