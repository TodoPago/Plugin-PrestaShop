<?php

function convertText($text) {
    $text = stripslashes($text);
    if (function_exists('mb_convert_encoding')) {
        $text = mb_convert_encoding($text, 'ISO-8859-1', 'UTF-8');
    }

    return $text;
}

function showError() {
    header('Content-Type: image/png');
    readfile('error.png');
    exit;
}

$requiredKeys = array('dpi', 'scale', 'rotation', 'text');

// Check if everything is present in the request
foreach ($requiredKeys as $key) {
    if (!isset($_GET[$key])) {
        showError();
    }
}

$class_dir = 'Codebar';
include_once($class_dir . DIRECTORY_SEPARATOR . 'Barcode.php');
require_once($class_dir . DIRECTORY_SEPARATOR . 'Color.php');
require_once($class_dir . DIRECTORY_SEPARATOR . 'Draw.php');

$drawException = null;
try {
    $color_black = new Color(0, 0, 0);
    $color_white = new Color(255, 255, 255);

    $code_generated = new Barcode();
    $code_generated->setScale(max(1, min(4, $_GET['scale'])));
    $code_generated->setBackgroundColor($color_white);
    $code_generated->setForegroundColor($color_black);

    if ($_GET['text'] !== '') {
        $text = convertText($_GET['text']);
        $code_generated->parse($text);
    }
} catch(Exception $exception) {
    $drawException = $exception;
}

$drawing = new Draw('', $color_white);
if($drawException) {
    $drawing->drawException($drawException);
} else {
    $drawing->setBarcode($code_generated);
    $drawing->setRotationAngle($_GET['rotation']);
    $drawing->setDPI($_GET['dpi'] === 'NULL' ? null : max(72, min(300, intval($_GET['dpi']))));
    $drawing->draw();
}

header('Content-Type: image/png');
$drawing->finish();
