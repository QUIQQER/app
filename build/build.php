<?php

echo "\nBuild started\n";

$apiUrl = "http://quiqqer.local/api/quiqqer/app/structure/Mainproject/de";

$apiData = json_decode(file_get_contents($apiUrl, true));

var_dump($apiData);

/**
 * SCSS
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
  color: {$colors->menuFrontColor} !important;
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
    color: {$colors->menuFrontColor};
  }
}

// Primary Color for Menu Button
\$colors: (
        primary: {$colors->menuFrontColor},
);


";

file_put_contents('src/theme/custom.scss', $scss);


/**
 * Config
 */

$showAds = $apiData->advertisment ? 'true' : 'false';

$config = "
export let config = {
    'showAds': {$showAds}
};
";

file_put_contents('src/app/config.ts', $config);


/**
 * Icon & Splash
 */

$logo = $apiData->logo;
$splash = $apiData->splash;

if (!empty($logo)) {
    copy($logo, 'resources/icon.png');
    echo "\nGeneriere Icons...";
    echo exec('ionic resources --icon');
}

if (!empty($splash)) {
    copy($splash, 'resources/splash.png');
    echo "\nGeneriere Splashscreens...";
    echo exec('ionic resources --splash');
}


echo "\nBuild completed\n";
