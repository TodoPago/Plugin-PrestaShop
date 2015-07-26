<?php

include_once('Color.php');

class FontPhp {
    private $font;
    private $text;
    private $rotationAngle;
    private $backgroundColor;
    private $foregroundColor;

    public function __construct($font) {
        $this->font = max(0, intval($font));
        $this->backgroundColor = new Color('white');
        $this->foregroundColor = new Color('black');
        $this->setRotationAngle(0);
    }

    public function getText() {
        return $this->text;
    }

    public function setText($text) {
        $this->text = $text;
    }

    public function getRotationAngle() {
        return (360 - $this->rotationAngle) % 360;
    }

    public function setRotationAngle($rotationAngle) {
        $this->rotationAngle = (int)$rotationAngle;
        if ($this->rotationAngle !== 90 && $this->rotationAngle !== 180 && $this->rotationAngle !== 270) {
            $this->rotationAngle = 0;
        }

        $this->rotationAngle = (360 - $this->rotationAngle) % 360;
    }

    public function getBackgroundColor() {
        return $this->backgroundColor;
    }

    public function setBackgroundColor($backgroundColor) {
        $this->backgroundColor = $backgroundColor;
    }

    public function getForegroundColor() {
        return $this->foregroundColor;
    }

    public function setForegroundColor($foregroundColor) {
        $this->foregroundColor = $foregroundColor;
    }

    public function getDimension() {
        $w = imagefontwidth($this->font) * strlen($this->text);
        $h = imagefontheight($this->font);

        $rotationAngle = $this->getRotationAngle();
        if ($rotationAngle === 90 || $rotationAngle === 270) {
            return array($h, $w);
        } else {
            return array($w, $h);
        }
    }

    public function draw($im, $x, $y) {
        if ($this->getRotationAngle() !== 0) {
            if (!function_exists('imagerotate')) {
                throw new Exception('The method imagerotate doesn\'t exist on your server. Do not use any rotation.');
            }

            $w = imagefontwidth($this->font) * strlen($this->text);
            $h = imagefontheight($this->font);
            $gd = imagecreatetruecolor($w, $h);
            imagefilledrectangle($gd, 0, 0, $w - 1, $h - 1, $this->backgroundColor->allocate($gd));
            imagestring($gd, $this->font, 0, 0, $this->text, $this->foregroundColor->allocate($gd));
            $gd = imagerotate($gd, $this->rotationAngle, 0);
            imagecopy($im, $gd, $x, $y, 0, 0, imagesx($gd), imagesy($gd));
        } else {
            imagestring($im, $this->font, $x, $y, $this->text, $this->foregroundColor->allocate($im));
        }
    }
}
