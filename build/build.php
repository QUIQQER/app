<?php

$apiUrl = "http://quiqqer.local/api/quiqqer/app/structure/Mainproject/de";

$apiData = json_decode(file_get_contents($apiUrl, true));

var_dump($apiData);

$colors = $apiData->colors;

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

file_put_contents('src/theme/custom_vars.scss', $scss);
