<?php

// Import QUIQQER Bootstrap
define('QUIQQER_SYSTEM', true);
$packagesDir = str_replace('quiqqer/app/bin', '', dirname(__FILE__));
require_once $packagesDir . '/header.php';

// Folder to store our ZIP
$tempFolderPath = QUI::getTemp()->createFolder('app');

// ZIP the build/ folder
\QUI\Archiver\Zip::zip($packagesDir . "quiqqer/app/build", $tempFolderPath . "app.zip");

// Return file download
header("Content-Type: application/zip");
header("Content-Disposition: attachment; filename=\"app.zip\"");
readfile($tempFolderPath . "app.zip");

exit;
