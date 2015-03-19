# phpstegano

A php library to encrypt and decrypt text or file into an image.

This library allows to encrypt and decrypt a string into an image (GD) resource. 
All methods (open, write, or send an image) are included into this project.

### Update 2015, a complete rewrite of this library is available throught [the project Morpheus](https://github.com/pyrou/morpheus)





## Disclaimer

The library doesn't receive any update since its first release in 2007. Yes, before github exists itself !

This repository was automatically exported from http://code.google.com/p/phpstegano and is brought to you for archive purposes only.


## How to use it ?

```php
// Read disclaimer first :)

// Write data

$stegano = new Stegano();
$stegano -> setData($data);
$stegano -> setOrigin("origin.jpg");
$stegano -> draw("encoded.png");

/* Note : output format could only be PNG, because it's a lossless format. */

// read data

$stegano = new Stegano();
echo '<pre>'.$stegano -> decodeImageFile("encoded.png").'</pre>';
```

## How it works ?

Data are encoded in the color of each pixel (3 bits of data on each pixel, encoded in RGB value). (http://en.wikipedia.org/wiki/Steganography#Example_from_modern_practice for more informations). 

## Is it detectable ?

The difference between the original image and the generated one (with encoded data) is undetectable by the human eye.
