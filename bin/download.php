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

$zipDir    = $packagesDir . "quiqqer/app/build";
$zipName   = "app-{$projectName}.zip";
$zipTarget = $tempFolderPath . $zipName;


// Delete older zip, since the zip isn't overwritten
if (file_exists($zipTarget)) {
    unlink($zipTarget);
}

// Ignore files that may be left over from package development
$ignore = array(
    'node_modules/',
    'platforms/',
    'plugins/',
    'www/assets/',
    'www/build/',
    'www/fonts/',
);

\QUI\Archiver\Zip::zip($zipDir, $zipTarget, $ignore);


// Add config file for API URL (inject into zip instead of creating in package folder before)
$Zip = new ZipArchive();
$Zip->open($zipTarget, ZipArchive::CREATE);

$apiUrl  = QUI\REST\Server::getInstance()->getAddress() . "/quiqqer/app/structure/$projectName/de";
$content = "api_url=\"$apiUrl\"";
$Zip->addFromString('config.ini', $content);

$Zip->close();

// Return file download
header("Content-Type: application/zip");
header("Content-Disposition: attachment; filename=\"$zipName\"");
readfile($zipTarget);

exit;
