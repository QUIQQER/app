<?php

/**
 * This script requires the first parameter to be the QUIQQER APP API URL
 * These parameters can be used optionally: --noIcon and/or --noSplash to skip icon and/or splashscreen generation
 */


echo "\nBuild started\n";

$runNpm = true;

$restoreState = true;

$generateIcon   = true;
$generateSplash = true;

// Is no npm flag set?
if (in_array('--noNpm', $argv)) {
    $runNpm = false;
    echo "Not running npm install\n";
}

// Is no state restore flag set?
if (in_array('--noRestore', $argv)) {
    $restoreState = false;
    echo "Not restoring Ionic State\n";
}

// Is no icon generation flag set?
if (in_array('--noIcon', $argv)) {
    $generateIcon = false;
    echo "Not generating Icons\n";
}

// Is no splash generation flag set?
if (in_array('--noSplash', $argv)) {
    $generateSplash = false;
    echo "Not generating Splash\n";
}


// Get API URL from first argument
if (!isset($argv[1])) {
    // If no first argument exit with error
    echo "\n ERROR: You need to provide an API URL!\n";
    exit;
}
$apiUrl = $argv[1]; // e.g: http://quiqqer.local/api/quiqqer/app/structure/Mainproject/de


// Install npm Modules
if ($runNpm) {
    echo "\nInstalling Node Modules, this may take a while...\n";
    shell_exec('npm install');
}


// Restore Ionic State
if ($restoreState) {
    echo "\nRestoring Ionic State...\n";
    shell_exec('ionic state restore');
}


// Try do get data from Api
try {
    // Get JSON data from API
    $apiData = json_decode(file_get_contents($apiUrl, true));
} catch (Exception $ex) {
    // If anything goes wrong exit with error
    echo "\n ERROR: Invalid API URL!\n";
    exit;
}

//var_dump($apiData);


/**
 * ===============================
 * =            SCSS             =
 * ===============================
 */
$colors = $apiData->colors;

// Show bottom tab bar?
$tabBarDisplayStyle = '';
if (!$apiData->useBottomMenu) {
    $tabBarDisplayStyle = 'display: none !important;';
}

$scss = "
// --------------------------------------------------
// Page
// --------------------------------------------------
\$text-color: {$colors->fontColor};

\$background-color: {$colors->backgroundColor};


// --------------------------------------------------
// Bottom Tabbar
// --------------------------------------------------

// Background
\$tabs-background: {$colors->menuBackgroundColor};

// Icon/Text Color
.tabbar * {
  color: {$colors->menuFontColor} !important;
}

// Display Tabbar?
ion-tabs {
  .tabbar {
   {$tabBarDisplayStyle}
  }
}


// --------------------------------------------------
// Top Toolbar
// --------------------------------------------------
ion-header {
  // Background
  .toolbar-background {
    background-color: {$colors->menuBackgroundColor} !important;
  }

  // Font Color
  ion-navbar.toolbar * {
    color: {$colors->menuFontColor};
  }
}

// Sidebar Title Color
ion-toolbar.toolbar ion-title * {
  color: {$colors->menuFontColor};
}

// Primary Color for Menu Button
\$colors: (
        primary: {$colors->menuFontColor},
);


";

// Save to file
file_put_contents('src/theme/custom.scss', $scss);


/**
 * ===============================
 * =          CONFIG             =
 * ===============================
 */
// Show advertisement banner?
$showAds = $apiData->advertisment ? 'true' : 'false';

$config = "
export let config = {
    'showAds': {$showAds},
    'imprintUrl': '{$apiData->imprint->url}'
};
";

// Save to file
file_put_contents('src/app/config.ts', $config);


/**
 * ===============================
 * =      ICON & SPLASH          =
 * ===============================
 */
$logo   = $apiData->logo;
$splash = $apiData->splash;

// If Logo URL and no Flag set, generate Icon
if (!empty($logo) && $generateIcon) {
    // Download the Icon
    copy($logo, 'resources/icon.png');

    echo "\nGeneriere Icons...\n";

    // Execute Ionic icon generation command
    echo shell_exec('ionic resources --icon');
}

// If Splash URL and no Flag set, generate Splash
if (!empty($splash) && $generateSplash) {
    // Download the Splash
    copy($splash, 'resources/splash.png');

    echo "\nGeneriere Splashscreens...\n";

    // Execute Ionic splash generation command
    echo shell_exec('ionic resources --splash');
}


/**
 * ===============================
 * =         SIDE MENU           =
 * ===============================
 */
// Build array of pages for sidemenu
$pages = "// Pages for sidemenu generated via build script\nexport let pages = [";
foreach ($apiData->menu as $page) {
    $pages .= "{title: '{$page->title}', url: '{$page->url}'},";
}
$pages .= "];";

// Save to file
file_put_contents('src/app/pages.ts', $pages);


/**
 * ===============================
 * =         CONFIG.xml          =
 * ===============================
 */
$appTitle = $apiData->title;
$author   = $apiData->author;

$xmlConfig                   = new SimpleXMLElement(file_get_contents('config.xml'));
$xmlConfig->attributes()->id = 'com.' . preg_replace('/\s/', '', $appTitle);

$xmlConfig->name        = $appTitle;
$xmlConfig->description = $apiData->description;

$xmlConfig->author          = $author->name;

$xmlAuthorAttributes        = $xmlConfig->author->attributes();
$xmlAuthorAttributes->email = $author->email;
$xmlAuthorAttributes->href  = $author->website;


$xmlConfig->saveXML('config.xml');

echo "\nBuild completed\n";
