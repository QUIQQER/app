<?php

/**
 * This script requires the first parameter to be the QUIQQER APP API URL
 * These parameters can be used optionally: --noIcon and/or --noSplash to skip icon and/or splashscreen generation
 */

echo "\nBuild started\n";

$generateIcon   = true;
$generateSplash = true;

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

try {
    $apiData = json_decode(file_get_contents($apiUrl, true));
} catch (Exception $ex) {
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
  .toolbar-title {
    color: {$colors->menuFontColor};
  }
}

// Primary Color for Menu Button
\$colors: (
        primary: {$colors->menuFontColor},
);


";

file_put_contents('src/theme/custom.scss', $scss);


/**
 * ===============================
 * =          CONFIG             =
 * ===============================
 */
$showAds = $apiData->advertisment ? 'true' : 'false';

$config = "
export let config = {
    'showAds': {$showAds},
    'imprintUrl': '{$apiData->imprint->url}'
};
";

file_put_contents('src/app/config.ts', $config);


/**
 * ===============================
 * =      ICON & SPLASH          =
 * ===============================
 */
$logo   = $apiData->logo;
$splash = $apiData->splash;

if (!empty($logo) && $generateIcon) {
    copy($logo, 'resources/icon.png');
    echo "\nGeneriere Icons...\n";
    echo exec('ionic resources --icon');
}

if (!empty($splash) && $generateSplash) {
    copy($splash, 'resources/splash.png');
    echo "\nGeneriere Splashscreens...\n";
    echo exec('ionic resources --splash');
}


/**
 * ===============================
 * =         SIDE MENU           =
 * ===============================
 */
$pages = "// Pages for sidemenu generated via build script\nexport let pages = [";
foreach ($apiData->menu as $page) {
    $pages .= "{title: '{$page->title}', url: '{$page->url}'},";
}
$pages .= "];";

file_put_contents('src/app/pages.ts', $pages);


echo "\nBuild completed\n";
