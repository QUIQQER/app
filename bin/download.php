<?php

// Import QUIQQER Bootstrap
define('QUIQQER_SYSTEM', true);
$packagesDir = str_replace('quiqqer/app/bin', '', dirname(__FILE__));
require_once $packagesDir . '/header.php';

if (!QUI::getUserBySession()->isSU()) {
    exit;
}

try {
    $projectName = $_GET['project'];
    $Project     = QUI::getProject($projectName);
} catch (\Exception $exception) {
    exit;
}

$projectName = $Project->getName();

// Folder to store our ZIP
$tempFolderPath = QUI::getTemp()->createFolder('app');

$zipName = "app-{$projectName}.zip";
$zipPath = $tempFolderPath . $zipName;

// Delete older zip, since the zip isn't overwritten
if (file_exists($zipPath)) {
    unlink($zipPath);
}

// ZIP the build/ folder
\QUI\Archiver\Zip::zip($packagesDir . "quiqqer/app/build", $zipPath);


// Add config file for API URL
$Zip = new ZipArchive();
$Zip->open($zipPath, ZipArchive::CREATE);

$apiUrl  = QUI\REST\Server::getInstance()->getAddress() . "quiqqer/app/structure/$projectName/de";
$content = "api_url=\"$apiUrl\"";
$Zip->addFromString('config.ini', $content);

$Zip->close();

// Return file download
header("Content-Type: application/zip");
header("Content-Disposition: attachment; filename=\"$zipName\"");
readfile($zipPath);

exit;
