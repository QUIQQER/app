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

// Is dev flag set? Activate all flags
if (in_array('--dev', $argv)) {
    $runNpm         = false;
    $restoreState   = false;
    $generateIcon   = false;
    $generateSplash = false;
    echo "Dev Mode:\n";
}

// Is no npm flag set?
if (in_array('--noNpm', $argv) || !$runNpm) {
    $runNpm = false;
    echo "Not running npm install\n";
}

// Is no state restore flag set?
if (in_array('--noRestore', $argv) || !$restoreState) {
    $restoreState = false;
    echo "Not restoring Ionic State\n";
}

// Is no icon generation flag set?
if (in_array('--noIcon', $argv) || !$generateIcon) {
    $generateIcon = false;
    echo "Not generating Icons\n";
}

// Is no splash generation flag set?
if (in_array('--noSplash', $argv) || !$generateSplash) {
    $generateSplash = false;
    echo "Not generating Splash\n";
}


// Get API URL from config.ini
$configIni = parse_ini_file('config.ini', true);
if ($configIni === false) {
    // If no config.ini found or not parseable
    error("config.ini could not be found");
    exit;
}

$apiUrl = $configIni['api_url']; // e.g: http://quiqqer.local/api/quiqqer/app/structure/Mainproject/de

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
    error("Invalid API URL!");
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
   
    > a:first-child {
      display: none;
    }
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
    'admobid': '{$apiData->admobid}',
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

    echo "\nGenerating Icons...\n";

    $cmd = 'ionic resources --icon';

    // Execute Ionic icon generation command
    $result = liveExecuteCommand($cmd);

    if ($result['exit_status'] == 1) {
        error(
            'Something went wrong generating the icons.' . PHP_EOL
            . "Try again later or try running '{$cmd}' manually" . PHP_EOL
            . "Further information:" . PHP_EOL
            . $result['output']
        );
    }
}

// If Splash URL and no Flag set, generate Splash
if (!empty($splash) && $generateSplash) {
    // Download the Splash
    copy($splash, 'resources/splash.png');

    echo "\nGenerating Splashscreens...\n";

    $cmd = 'ionic resources --splash';

    // Execute Ionic splash generation command
    $result = liveExecuteCommand($cmd);

    if ($result['exit_status'] == 1) {
        error(
            'Something went wrong generating the splashscreens.' . PHP_EOL
            . "Try again later or try running '{$cmd}' manually" . PHP_EOL
            . "Further information:" . PHP_EOL
            . $result['output']
        );
    }
}


/**
 * ===============================
 * =         SIDE MENU           =
 * ===============================
 */
// Build array of pages for sidemenu
$pages = "// Pages for sidemenu generated via build script\nexport let pages = [";
foreach ($apiData->sideMenu as $page) {
    $pages .= "{title: '{$page->title}', url: '{$page->url}', icon: '{$page->icon}'},";
}
$pages .= "];";

// Save to file
file_put_contents('src/app/pages.ts', $pages);


/**
 * ===============================
 * =         BOTTOM MENU         =
 * ===============================
 */
// Build array of pages for sidemenu
$pages = "// Pages for bottom menu generated via build script\nexport let bottomMenu = [";
foreach ($apiData->bottomMenu as $page) {
    $pages .= "{title: '{$page->title}', url: '{$page->url}'},";
}
$pages .= "];";

// Save to file
file_put_contents('src/assets/bottomMenu.ts', $pages);


/**
 * ===============================
 * =      STATIC PAGES/URLs      =
 * ===============================
 */
// Build array of pages for sidemenu
$staticURLs = "// static pages generated via build script\nvar static_urls = [";
foreach ($apiData->sideMenu as $page) {
    if ($page->isStatic) {
        $staticURLs .= "'{$page->url}',";
    }
}
$staticURLs .= "];";

// Save to file
file_put_contents('src/assets/static_pages.js', $staticURLs);


/**
 * ===============================
 * =         CONFIG.xml          =
 * ===============================
 */
$appTitle = $apiData->title;
$author   = $apiData->author;

$xmlConfig                        = new SimpleXMLElement(file_get_contents('config.xml'));
$xmlConfig->attributes()->id      = 'com.' . preg_replace('/\s/', '', $appTitle);
$xmlConfig->attributes()->version = $apiData->version;

$xmlConfig->name        = $appTitle;
$xmlConfig->description = $apiData->description;

$xmlConfig->author = $author->name;

$xmlAuthorAttributes        = $xmlConfig->author->attributes();
$xmlAuthorAttributes->email = $author->email;
$xmlAuthorAttributes->href  = $author->website;


$xmlConfig->saveXML('config.xml');

echo "\nBuild completed\n";


/**
 * Execute the given command by displaying console output live to the user.
 * Modified version of Amith Snippet from Stackoverflow
 * @see http://stackoverflow.com/a/32664523/3002417
 * @param  string $cmd :  command to be executed
 * @return array   exit_status  :  exit status of the executed command
 *                 output       :  console output of the executed command
 */
function liveExecuteCommand($cmd)
{
    // end all output buffers if any
    while (@ ob_end_flush()) {
    }

    $proc = popen("$cmd 2>&1 ; echo Exit status : $?", 'r');

    $live_output     = "";
    $complete_output = "";

    while (!feof($proc)) {
        $live_output     = fread($proc, 4096);
        $complete_output = $complete_output . $live_output;
        echo "$live_output";
        @ flush();
    }

    pclose($proc);

    // get exit status
    preg_match('/[0-9]+$/', $complete_output, $matches);

    // return exit status and intended output
    return array(
        'exit_status' => intval($matches[0]),
        'output'      => str_replace("Exit status : " . $matches[0], '', $complete_output)
    );
}

/**
 * Outputs an error message (colored red)
 *
 * @param $message
 */
function error($message)
{
    echo "\033[0;31mERROR:" . PHP_EOL . "$message\033[0m" . PHP_EOL;
}
