This library offer you the possibility to encrypt and decrypt a string into an image (GD) resource. All methods (open, write, or send an image) are included into this project.

# How to use it ? #

```
// Write data

$stegano = new Stegano();
$stegano -> setData($data);
$stegano -> setOrigin("origin.jpg");
$stegano -> draw("encoded.png");

/* Note : output format could only be PNG, because it's a loose-less format. */

// read data

$stegano = new Stegano();
echo '<pre>'.$stegano -> decodeImageFile("encoded.png").'</pre>';
```

# How it works ? #

Data are encoded in the color of each pixel (3 bits of data on each pixel, encoded in RGB value). (http://en.wikipedia.org/wiki/Steganography#Example_from_modern_practice for more informations).

# Is it detectable ? #

The difference between the original image and the generated one (with encoded data) is undetectable by the human eye.