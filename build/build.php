<?php

/**
 * This script can be called with parameters --noIcon and/or --noSplash to skip icon and/or splashscreen generation
 */

echo "\nBuild started\n";

// Get command line options
$options = getopt('', array('noIcon', 'noSplash'));

// Print out used options
$optionsString = "";
foreach ($options as $key => $option) {
    $optionsString .= $option;
    if ($key != count($options)) {
        $optionsString .= ", ";
    }
}
echo "Used Options: $optionsString\n";

$apiUrl = "http://quiqqer.local/api/quiqqer/app/structure/Mainproject/de";
if (isset($options['apiUrl'])) {
    $apiUrl = $options['apiUrl'];
}

$apiData = json_decode(file_get_contents($apiUrl, true));

var_dump($apiData);



/**
 ===============================
 =            SCSS             =
 ===============================
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
===============================
=          CONFIG             =
===============================
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
===============================
=      ICON & SPLASH          =
===============================
 */
$logo = $apiData->logo;
$splash = $apiData->splash;

if (!empty($logo) && !isset($options['noIcon'])) {
    copy($logo, 'resources/icon.png');
    echo "\nGeneriere Icons...\n";
    echo exec('ionic resources --icon');
}

if (!empty($splash) && !isset($options['noSplash'])) {
    copy($splash, 'resources/splash.png');
    echo "\nGeneriere Splashscreens...\n";
    echo exec('ionic resources --splash');
}



/**
===============================
=         SIDE MENU           =
===============================
 */
$pages = "// Pages for sidemenu generated via build script\nexport let pages = [";
foreach ($apiData->menu as $page) {
    $pages .= "{title: '{$page->title}', url: '{$page->url}'},";
}
$pages .= "];";

file_put_contents('src/app/pages.ts', $pages);



echo "\nBuild completed\n";
