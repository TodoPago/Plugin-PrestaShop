<?php

include_once('Barcode.php');
include_once('Color.php');

class Draw {

    private $w, $h;         
    private $color;         
    private $filename;      
    private $im;            
    private $barcode;       
    private $dpi;           
    private $rotateDegree;  
	
    public function __construct($filename = null, Color $color) {
        $this->im = null;
        $this->setFilename($filename);
        $this->color = $color;
        $this->dpi = null;
        $this->rotateDegree = 0.0;
    }

    public function __destruct() {
        $this->destroy();
    }

    public function getFilename() {
        return $this->filename;
    }

    public function setFilename($filename) {
        $this->filename = $filename;
    }

    public function get_im() {
        return $this->im;
    }

    public function set_im($im) {
        $this->im = $im;
    }

    public function getBarcode() {
        return $this->barcode;
    }

    public function setBarcode($barcode) {
        $this->barcode = $barcode;
    }

    public function getDPI() {
        return $this->dpi;
    }

    public function setDPI($dpi) {
        $this->dpi = $dpi;
    }

    public function getRotationAngle() {
        return $this->rotateDegree;
    }

    public function setRotationAngle($degree) {
        $this->rotateDegree = (float)$degree;
    }

    public function draw() {
        $size = $this->barcode->getDimension(0, 0);
        $this->w = max(1, $size[0]);
        $this->h = max(1, $size[1]);
        $this->init();
        $this->barcode->draw($this->im);
    }

    public function finish($quality = 100) {
        $drawer = null;
		
        $im = $this->im;
        if ($this->rotateDegree > 0.0) {
            if (function_exists('imagerotate')) {
                $im = imagerotate($this->im, 360 - $this->rotateDegree, $this->color->allocate($this->im));
            } else {
                throw new Exception('The method imagerotate doesn\'t exist on your server. Do not use any rotation.');
            }
        }
		
        ob_start();
        imagepng($im);
        $bin = ob_get_contents();
        ob_end_clean();

        $this->setInternalProperties($bin);

        if (empty($this->filename)) {
            echo $bin;
        } else {
            file_put_contents($this->filename, $bin);
        }		
    }

    private function setInternalProperties(&$bin) {
        if (strcmp(substr($bin, 0, 8), pack('H*', '89504E470D0A1A0A')) === 0) {
            $chunks = $this->detectChunks($bin);

            $this->internalSetDPI($bin, $chunks);
            $this->internalSetC($bin, $chunks);
        }
    }	
	
    private function detectChunks($bin) {
        $data = substr($bin, 8);
        $chunks = array();
        $c = strlen($data);
        
        $offset = 0;
        while ($offset < $c) {
            $packed = unpack('Nsize/a4chunk', $data);
            $size = $packed['size'];
            $chunk = $packed['chunk'];

            $chunks[] = array('offset' => $offset + 8, 'size' => $size, 'chunk' => $chunk);
            $jump = $size + 12;
            $offset += $jump;
            $data = substr($data, $jump);
        }
        
        return $chunks;
    }

    private function internalSetDPI(&$bin, &$chunks) {
        if ($this->dpi !== null) {
            $meters = (int)($this->dpi * 39.37007874);

            $found = -1;
            $c = count($chunks);
            for($i = 0; $i < $c; $i++) {
                if($chunks[$i]['chunk'] === 'pHYs') {
                    $found = $i;
                    break;
                }
            }

            $data = 'pHYs' . pack('NNC', $meters, $meters, 0x01);
            $crc = self::crc($data, 13);
            $cr = pack('Na13N', 9, $data, $crc);

            if($found == -1) {
                if($c >= 2 && $chunks[0]['chunk'] === 'IHDR') {
                    array_splice($chunks, 1, 0, array(array('offset' => 33, 'size' => 9, 'chunk' => 'pHYs')));

                    for($i = 2; $i < $c; $i++) {
                        $chunks[$i]['offset'] += 21;
                    }

                    $firstPart = substr($bin, 0, 33);
                    $secondPart = substr($bin, 33);
                    $bin = $firstPart;
                    $bin .= $cr;
                    $bin .= $secondPart;
                }
            } else {
                $bin = substr_replace($bin, $cr, $chunks[$i]['offset'], 21);
            }
        }
    }

    private function internalSetC(&$bin, &$chunks) {
        if (count($chunks) >= 2 && $chunks[0]['chunk'] === 'IHDR') {
            $firstPart = substr($bin, 0, 33);
            $secondPart = substr($bin, 33);
            $cr = pack('H*', '0000004C74455874436F707972696768740047656E657261746564207769746820426172636F64652047656E657261746F7220666F722050485020687474703A2F2F7777772E626172636F64657068702E636F6D597F70B8');
            $bin = $firstPart;
            $bin .= $cr;
            $bin .= $secondPart;
        }

    }

    private static $crc_table = array();
    private static $crc_table_computed = false;

    private static function make_crc_table() {
        for ($n = 0; $n < 256; $n++) {
            $c = $n;
            for ($k = 0; $k < 8; $k++) {
                if (($c & 1) == 1) {
                    $c = 0xedb88320 ^ (self::SHR($c, 1));
                } else {
                    $c = self::SHR($c, 1);
                }
            }
            self::$crc_table[$n] = $c;
        }

        self::$crc_table_computed = true;
    }

    private static function SHR($x, $n) {
        $mask = 0x40000000;

        if ($x < 0) {
            $x &= 0x7FFFFFFF;
            $mask = $mask >> ($n - 1);
            return ($x >> $n) | $mask;
        }

        return (int)$x >> (int)$n;
    }

    private static function update_crc($crc, $buf, $len) {
        $c = $crc;

        if (!self::$crc_table_computed) {
            self::make_crc_table();
        }

        for ($n = 0; $n < $len; $n++) {
            $c = self::$crc_table[($c ^ ord($buf[$n])) & 0xff] ^ (self::SHR($c, 8));
        }

        return $c;
    }

    private static function crc($data, $len) {
        return self::update_crc(-1, $data, $len) ^ -1;
    }
	
    public function drawException($exception) {
        $this->w = 1;
        $this->h = 1;
        $this->init();

        $w = imagesx($this->im);
        $h = imagesy($this->im);

        $text = 'Error: ' . $exception->getMessage();

        $width = imagefontwidth(2) * strlen($text);
        $height = imagefontheight(2);
        if ($width > $w || $height > $h) {
            $width = max($w, $width);
            $height = max($h, $height);

            $newimg = imagecreatetruecolor($width, $height);
            imagefill($newimg, 0, 0, imagecolorat($this->im, 0, 0));
            imagecopy($newimg, $this->im, 0, 0, 0, 0, $w, $h);
            $this->im = $newimg;
        }

        $black = new Color('black');
        imagestring($this->im, 2, 0, 0, $text, $black->allocate($this->im));
    }

    public function destroy() {
        @imagedestroy($this->im);
    }

    private function init() {
        if ($this->im === null) {
            $this->im = imagecreatetruecolor($this->w, $this->h)
            or die('Can\'t Initialize the GD Libraty');
            imagefilledrectangle($this->im, 0, 0, $this->w - 1, $this->h - 1, $this->color->allocate($this->im));
        }
    }
}
