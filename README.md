# PHP Openreach Bill Decoder 
Decodes Openreach-format DAT billing files for backoffice processing.

Example:

<?php

// Load OR Billing Helper
require_once dirname(__FILE__).'/php-or-bill-decoder/ORBillDecoder.class.php';

// input params
if(!$file = @$argv[1]) die("Usage {$argv[0]} <dat file>");

// Load file
$btor = new ORBilling();
$btor->load($file);

// Render to screen
$txt = $btor->toText();
print_r($txt);
