<?php

for ($i = 0; $i < $argc; $i++) {
  $options[$i] = $argv[$i];
}
$recursive = false;
$outputIMG = "sprite.png";
$outputSTL = "style.css";

$short_options = "ri::s:";
$long_options = array("recursive", "output-image::", "output-style:");
$options = getopt($short_options, $long_options);

if (isset($options["r"]) || isset($options["recursive"])) {
  $recursive = true;
}

if (isset($options["output-image"]) || (isset($options["i"]))) {
  $info = new SplFileInfo(isset($options["output-image"]) ? $options["output-image"] : $options["i"]);

  if ($info->getExtension() == "png") {
    $outputIMG = isset($options["output-image"]) ? $options["output-image"] : $options["i"];

  } elseif ($info->getExtension() !== "png") {
    $outputIMG = isset($options["output-image"]) ? $options["output-image"] . ".png" : $options["i"] . ".png";
  }
  unset($info);
}

if (isset($options["output-style"]) || (isset($options["s"]))) {
  $info = new SplFileInfo(isset($options["output-style"]) ? $options["output-style"] : $options["s"]);

  if ($info->getExtension() == "css") {
    $outputSTL = isset($options["output-style"]) ? $options["output-style"] : $options["s"];

  } elseif ($info->getExtension() !== "css") {
    $outputSTL = isset($options["output-style"]) ? $options["output-style"] . ".css" : $options["s"] . ".css";
  }
  unset($info);
}

$folder = $argv[$argc - 1];
function glob_recursive($folder, $extension)
{
  static $i = 0;
  static $array_images = [];
  $files = glob("$folder/*.$extension");
  $folders = glob("$folder/*", GLOB_ONLYDIR);

  if ($folders) {
    foreach ($folders as $folder) {
      glob_recursive($folder, $extension);
    }
  }

  if ($files) {
    foreach ($files as $file) {
      $array_images[$i] = $file;
      $i++;
    }
  }
  return $array_images;
}
if ($recursive) {
  $paths = glob_recursive($folder, "png");
} else {
  $paths = glob("$folder/*.png");
}


foreach ($paths as $path) {
  $images[] = imagecreatefrompng($path);
}

$width = 0;
$height = 0;

foreach ($images as $image) {
  $width = max($width, imagesx($image));
  $height += imagesy($image);
}

$sprite = imagecreatetruecolor($width, $height); //create sprite image

$transparent = imagecolorallocate($sprite, 0, 0, 0); // transparent color
imagecolortransparent($sprite, $transparent);

$y = 0;

foreach ($images as $image) {
    imagecopy($sprite, $image, 0, $y, 0, 0, $width, imagesy($image));
    $y += @imagesy($image);
}


@imagepng($sprite, "sprite.png"); //save images sprite


$css = '';
$x = 0;
$y = 0;

foreach ($paths as $image) {
  // Get the file name and extension of the image
  $parts = pathinfo($image);

  $name = $parts['filename'];
  $ext = $parts['extension'];

  // Create CSS rule
  $css .= ".$name {\n";
  $css .= "    background-image: url('png.png');\n";
  $css .= "    background-position: $x" . "px $y" . "px;\n";
  $css .= "}\n";

  $x += $width;
  $y += $height;

  echo $css;
}

imagedestroy($sprite);

file_put_contents ("style.css", $css);//save CSS rules