<?php

include_once('Color.php');
include_once('FontPhp.php');
include_once('Label.php');

class Barcode {
    const SIZE_SPACING_FONT = 5;
    const AUTO_LABEL = '##!!AUTO_LABEL!!##';

	const COLOR_BG = 0;
    const COLOR_FG = 1;

    private $checksum;
    private $ratio;

    protected $colorFg, $colorBg;       
    protected $scale;                   
    protected $offsetX, $offsetY;       
    protected $labels = array();        
    protected $pushLabel = array(0, 0); 

    protected $thickness;       
    protected $keys, $code;     
    protected $positionX;       
    protected $font;            
    protected $text;            
    protected $checksumValue;   
    protected $displayChecksum; 
    protected $label;           
    protected $defaultLabel;    

    public function __construct() {
		$this->setOffsetX(0);
        $this->setOffsetY(0);
        $this->setForegroundColor(0x000000);
        $this->setBackgroundColor(0xffffff);
        $this->setScale(1);

        $this->setThickness(30);

        $this->defaultLabel = new Label();
        $this->defaultLabel->setPosition(Label::POSITION_BOTTOM);
        $this->setLabel(self::AUTO_LABEL);
        $this->setFont(new FontPhp(5));

        $this->text = '';
        $this->checksumValue = false;
        $this->positionX = 0;

        $this->keys = array('0', '1', '2', '3', '4', '5', '6', '7', '8', '9');
        $this->code = array(
            '00110',   
            '10001',   
            '01001',   
            '11000',   
            '00101',   
            '10100',   
            '01100',   
            '00011',   
            '10010',   
            '01010'    
        );

        $this->setChecksum(false);
        $this->setRatio(2);
    }

    public function getThickness() {
        return $this->thickness;
    }

    public function setThickness($thickness) {
        $thickness = intval($thickness);
        if ($thickness <= 0) {
            throw new Exception('The thickness must be larger than 0.');
        }

        $this->thickness = $thickness;
    }

    public function getLabel() {
        $label = $this->label;
        if ($this->label === self::AUTO_LABEL) {
            $label = $this->text;
            if ($this->displayChecksum === true && ($checksum = $this->processChecksum()) !== false) {
                $label .= $checksum;
            }
        }

        return $label;
    }

    public function setLabel($label) {
        $this->label = $label;
    }

    public function getFont() {
        return $this->font;
    }

    public function setFont($font) {
        if (is_int($font)) {
            if ($font === 0) {
                $font = null;
            } else {
                $font = new FontPhp($font);
            }
        }

        $this->font = $font;
    }

    public function parse($text) {
        $this->text = $text;
        $this->checksumValue = false; 
        $this->validate();

        $this->addDefaultLabel();
    }

    public function getChecksum() {
        return $this->processChecksum();
    }

    public function setDisplayChecksum($displayChecksum) {
        $this->displayChecksum = (bool)$displayChecksum;
    }

    protected function addDefaultLabel() {
        $label = $this->getLabel();
        $font = $this->font;
        if ($label !== null && $label !== '' && $font !== null && $this->defaultLabel !== null) {
            $this->defaultLabel->setText($label);
            $this->defaultLabel->setFont($font);
            $this->addLabel($this->defaultLabel);
        }
    }

    protected function findIndex($var) {
        return array_search($var, $this->keys);
    }

    protected function findCode($var) {
        return $this->code[$this->findIndex($var)];
    }

    protected function drawChar($im, $code, $startBar = true) {
        $colors = array(self::COLOR_FG, self::COLOR_BG);
        $currentColor = $startBar ? 0 : 1;
        $c = strlen($code);
        for ($i = 0; $i < $c; $i++) {
            for ($j = 0; $j < intval($code[$i]) + 1; $j++) {
                $this->drawSingleBar($im, $colors[$currentColor]);
                $this->nextX();
            }

            $currentColor = ($currentColor + 1) % 2;
        }
    }

    protected function drawSingleBar($im, $color) {
        $this->drawFilledRectangle($im, $this->positionX, 0, $this->positionX, $this->thickness - 1, $color);
    }

    protected function nextX() {
        $this->positionX++;
    }

    public function getForegroundColor() {
        return $this->colorFg;
    }

    public function setForegroundColor($code) {
        if ($code instanceof Color) {
            $this->colorFg = $code;
        } else {
            $this->colorFg = new Color($code);
        }
    }

    public function getBackgroundColor() {
        return $this->colorBg;
    }

    public function setBackgroundColor($code) {
        if ($code instanceof Color) {
            $this->colorBg = $code;
        } else {
            $this->colorBg = new Color($code);
        }

        foreach ($this->labels as $label) {
            $label->setBackgroundColor($this->colorBg);
        }
    }

    public function setColor($fg, $bg) {
        $this->setForegroundColor($fg);
        $this->setBackgroundColor($bg);
    }

    public function getScale() {
        return $this->scale;
    }

    public function setScale($scale) {
        $scale = intval($scale);
        if ($scale <= 0) {
            throw new Exception('The scale must be larger than 0.');
        }

        $this->scale = $scale;
    }
	
    public function getOffsetX() {
        return $this->offsetX;
    }

    public function setOffsetX($offsetX) {
        $offsetX = intval($offsetX);
        if ($offsetX < 0) {
            throw new Exception('The offset X must be 0 or larger.');
        }

        $this->offsetX = $offsetX;
    }
	
    public function getOffsetY() {
        return $this->offsetY;
    }

    public function setOffsetY($offsetY) {
        $offsetY = intval($offsetY);
        if ($offsetY < 0) {
            throw new Exception('The offset Y must be 0 or larger.');
        }

        $this->offsetY = $offsetY;
    }

    public function addLabel(Label $label) {
        $label->setBackgroundColor($this->colorBg);
        $this->labels[] = $label;
    }

    public function removeLabel(Label $label) {
        $remove = -1;
        $c = count($this->labels);
        for ($i = 0; $i < $c; $i++) {
            if ($this->labels[$i] === $label) {
                $remove = $i;
                break;
            }
        }

        if ($remove > -1) {
            array_splice($this->labels, $remove, 1);
        }
    }

    public function clearLabels() {
        $this->labels = array();
    }

    protected function drawText($im, $x1, $y1, $x2, $y2) {
        foreach ($this->labels as $label) {
            $label->draw($im,
                ($x1 + $this->offsetX) * $this->scale + $this->pushLabel[0],
                ($y1 + $this->offsetY) * $this->scale + $this->pushLabel[1],
                ($x2 + $this->offsetX) * $this->scale + $this->pushLabel[0],
                ($y2 + $this->offsetY) * $this->scale + $this->pushLabel[1]);
        }
    }

    protected function drawPixel($im, $x, $y, $color = self::COLOR_FG) {
        $xR = ($x + $this->offsetX) * $this->scale + $this->pushLabel[0];
        $yR = ($y + $this->offsetY) * $this->scale + $this->pushLabel[1];

        imagefilledrectangle($im,
            $xR,
            $yR,
            $xR + $this->scale - 1,
            $yR + $this->scale - 1,
            $this->getColor($im, $color));
    }

    protected function drawRectangle($im, $x1, $y1, $x2, $y2, $color = self::COLOR_FG) {
        if ($this->scale === 1) {
            imagefilledrectangle($im,
                ($x1 + $this->offsetX) + $this->pushLabel[0],
                ($y1 + $this->offsetY) + $this->pushLabel[1],
                ($x2 + $this->offsetX) + $this->pushLabel[0],
                ($y2 + $this->offsetY) + $this->pushLabel[1],
                $this->getColor($im, $color));
        } else {
            imagefilledrectangle($im, ($x1 + $this->offsetX) * $this->scale + $this->pushLabel[0], ($y1 + $this->offsetY) * $this->scale + $this->pushLabel[1], ($x2 + $this->offsetX) * $this->scale + $this->pushLabel[0] + $this->scale - 1, ($y1 + $this->offsetY) * $this->scale + $this->pushLabel[1] + $this->scale - 1, $this->getColor($im, $color));
            imagefilledrectangle($im, ($x1 + $this->offsetX) * $this->scale + $this->pushLabel[0], ($y1 + $this->offsetY) * $this->scale + $this->pushLabel[1], ($x1 + $this->offsetX) * $this->scale + $this->pushLabel[0] + $this->scale - 1, ($y2 + $this->offsetY) * $this->scale + $this->pushLabel[1] + $this->scale - 1, $this->getColor($im, $color));
            imagefilledrectangle($im, ($x2 + $this->offsetX) * $this->scale + $this->pushLabel[0], ($y1 + $this->offsetY) * $this->scale + $this->pushLabel[1], ($x2 + $this->offsetX) * $this->scale + $this->pushLabel[0] + $this->scale - 1, ($y2 + $this->offsetY) * $this->scale + $this->pushLabel[1] + $this->scale - 1, $this->getColor($im, $color));
            imagefilledrectangle($im, ($x1 + $this->offsetX) * $this->scale + $this->pushLabel[0], ($y2 + $this->offsetY) * $this->scale + $this->pushLabel[1], ($x2 + $this->offsetX) * $this->scale + $this->pushLabel[0] + $this->scale - 1, ($y2 + $this->offsetY) * $this->scale + $this->pushLabel[1] + $this->scale - 1, $this->getColor($im, $color));
        }
    }

    protected function drawFilledRectangle($im, $x1, $y1, $x2, $y2, $color = self::COLOR_FG) {
        if ($x1 > $x2) { 
            $x1 ^= $x2 ^= $x1 ^= $x2;
        }

        if ($y1 > $y2) { 
            $y1 ^= $y2 ^= $y1 ^= $y2;
        }

        imagefilledrectangle($im,
            ($x1 + $this->offsetX) * $this->scale + $this->pushLabel[0],
            ($y1 + $this->offsetY) * $this->scale + $this->pushLabel[1],
            ($x2 + $this->offsetX) * $this->scale + $this->pushLabel[0] + $this->scale - 1,
            ($y2 + $this->offsetY) * $this->scale + $this->pushLabel[1] + $this->scale - 1,
            $this->getColor($im, $color));
    }

    protected function getColor($im, $color) {
        if ($color === self::COLOR_BG) {
            return $this->colorBg->allocate($im);
        } else {
            return $this->colorFg->allocate($im);
        }
    }

    private function getBiggestLabels($reversed = false) {
        $searchLR = $reversed ? 1 : 0;
        $searchTB = $reversed ? 0 : 1;

        $labels = array();
        foreach ($this->labels as $label) {
            $position = $label->getPosition();
            if (isset($labels[$position])) {
                $savedDimension = $labels[$position]->getDimension();
                $dimension = $label->getDimension();
                if ($position === BCGLabel::POSITION_LEFT || $position === BCGLabel::POSITION_RIGHT) {
                    if ($dimension[$searchLR] > $savedDimension[$searchLR]) {
                        $labels[$position] = $label;
                    }
                } else {
                    if ($dimension[$searchTB] > $savedDimension[$searchTB]) {
                        $labels[$position] = $label;
                    }
                }
            } else {
                $labels[$position] = $label;
            }
        }

        return $labels;
    }
	
    public function setChecksum($checksum) {
        $this->checksum = (bool)$checksum;
    }

    public function setRatio($ratio) {
        $this->ratio = $ratio;
    }

    public function draw($im) {
        $temp_text = $this->text;

        if ($this->checksum === true) {
            $this->calculateChecksum();
            $temp_text .= $this->keys[$this->checksumValue];
        }

        $this->drawChar($im, '0000', true);

        $c = strlen($temp_text);
        for ($i = 0; $i < $c; $i += 2) {
            $temp_bar = '';
            $c2 = strlen($this->findCode($temp_text[$i]));
            for ($j = 0; $j < $c2; $j++) {
                $temp_bar .= substr($this->findCode($temp_text[$i]), $j, 1);
                $temp_bar .= substr($this->findCode($temp_text[$i + 1]), $j, 1);
            }

            $this->drawChar($im, $this->changeBars($temp_bar), true);
        }

        $this->drawChar($im, $this->changeBars('100'), true);
        $this->drawText($im, 0, 0, $this->positionX, $this->thickness);
    }

    public function getDimension($w, $h) {
        $textlength = (3 + ($this->ratio + 1) * 2) * strlen($this->text);
        $startlength = 4;
        $checksumlength = 0;
        if ($this->checksum === true) {
            $checksumlength = (3 + ($this->ratio + 1) * 2);
        }

        $endlength = 2 + ($this->ratio + 1);

        $w += $startlength + $textlength + $checksumlength + $endlength;
        $h += $this->thickness;

        $labels = $this->getBiggestLabels(false);
        $pixelsAround = array(0, 0, 0, 0); 
        if (isset($labels[Label::POSITION_TOP])) {
            $dimension = $labels[Label::POSITION_TOP]->getDimension();
            $pixelsAround[0] += $dimension[1];
        }

        if (isset($labels[Label::POSITION_RIGHT])) {
            $dimension = $labels[Label::POSITION_RIGHT]->getDimension();
            $pixelsAround[1] += $dimension[0];
        }

        if (isset($labels[Label::POSITION_BOTTOM])) {
            $dimension = $labels[Label::POSITION_BOTTOM]->getDimension();
            $pixelsAround[2] += $dimension[1];
        }

        if (isset($labels[Label::POSITION_LEFT])) {
            $dimension = $labels[Label::POSITION_LEFT]->getDimension();
            $pixelsAround[3] += $dimension[0];
        }

        $finalW = ($w + $this->offsetX) * $this->scale;
        $finalH = ($h + $this->offsetY) * $this->scale;

        $reversedLabels = $this->getBiggestLabels(true);
        foreach ($reversedLabels as $label) {
            $dimension = $label->getDimension();
            $alignment = $label->getAlignment();
            if ($label->getPosition() === Label::POSITION_LEFT || $label->getPosition() === Label::POSITION_RIGHT) {
                if ($alignment === Label::ALIGN_TOP) {
                    $pixelsAround[2] = max($pixelsAround[2], $dimension[1] - $finalH);
                } elseif ($alignment === Label::ALIGN_CENTER) {
                    $temp = ceil(($dimension[1] - $finalH) / 2);
                    $pixelsAround[0] = max($pixelsAround[0], $temp);
                    $pixelsAround[2] = max($pixelsAround[2], $temp);
                } elseif ($alignment === Label::ALIGN_BOTTOM) {
                    $pixelsAround[0] = max($pixelsAround[0], $dimension[1] - $finalH);
                }
            } else {
                if ($alignment === Label::ALIGN_LEFT) {
                    $pixelsAround[1] = max($pixelsAround[1], $dimension[0] - $finalW);
                } elseif ($alignment === Label::ALIGN_CENTER) {
                    $temp = ceil(($dimension[0] - $finalW) / 2);
                    $pixelsAround[1] = max($pixelsAround[1], $temp);
                    $pixelsAround[3] = max($pixelsAround[3], $temp);
                } elseif ($alignment === Label::ALIGN_RIGHT) {
                    $pixelsAround[3] = max($pixelsAround[3], $dimension[0] - $finalW);
                }
            }
        }

        $this->pushLabel[0] = $pixelsAround[3];
        $this->pushLabel[1] = $pixelsAround[0];

        $finalW = ($w + $this->offsetX) * $this->scale + $pixelsAround[1] + $pixelsAround[3];
        $finalH = ($h + $this->offsetY) * $this->scale + $pixelsAround[0] + $pixelsAround[2];

        return array($finalW, $finalH);
    }

    protected function validate() {
        $c = strlen($this->text);
        if ($c === 0) {
            throw new Exception('No data has been entered.');
        }

        for ($i = 0; $i < $c; $i++) {
            if (array_search($this->text[$i], $this->keys) === false) {
                throw new Exception('The character \'' . $this->text[$i] . '\' is not allowed.');
            }
        }

        if ($c % 2 !== 0 && $this->checksum === false) {
            throw new Exception('i25 must contain an even amount of digits if checksum is false.');
        } elseif ($c % 2 === 0 && $this->checksum === true) {
            throw new Exception('i25 must contain an odd amount of digits if checksum is true.');
        }
    }

    protected function calculateChecksum() {

        $even = true;
        $this->checksumValue = 0;
        $c = strlen($this->text);
        for ($i = $c; $i > 0; $i--) {
            if ($even === true) {
                $multiplier = 3;
                $even = false;
            } else {
                $multiplier = 1;
                $even = true;
            }

            $this->checksumValue += $this->keys[$this->text[$i - 1]] * $multiplier;
        }

        $this->checksumValue = (10 - $this->checksumValue % 10) % 10;
    }

    protected function processChecksum() {
        if ($this->checksumValue === false) {
            $this->calculateChecksum();
        }

        if ($this->checksumValue !== false) {
            return $this->keys[$this->checksumValue];
        }

        return false;
    }

    private function changeBars($in) {
        if ($this->ratio > 1) {
            $c = strlen($in);
            for ($i = 0; $i < $c; $i++) {
                $in[$i] = $in[$i] === '1' ? $this->ratio : $in[$i];
            }
        }

        return $in;
    }
}
