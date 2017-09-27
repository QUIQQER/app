<?php

/**
 * The following flags can be used:
 * --noNpm      	skip download & installation of npm packages
 * --skipPlatforms  skip adding iOS/Android platforms
 * --ignoreSSL  	ignore SSL certificate errors when querying QUIQQER App API
 * --noIcon     	skip icon generation
 * --noSplash   	skip splashscreen generation
 *
 * --dev        	activates all of above flags
 */

echo "\nBuild started\n";

$runNpm = true;

$addPlatforms = true;

$generateIcon   = true;
$generateSplash = true;

$sslErrors = true;

$cliInput = fopen("php://stdin", "r");

// If dev flag is set activate all flags
if (in_array('--dev', $argv)) {
    $runNpm         = false;
    $addPlatforms   = false;
    $generateIcon   = false;
    $generateSplash = false;
    $sslErrors      = false;
    echo "Dev Mode:\n";
}

// Is no npm flag set?
if (in_array('--noNpm', $argv) || !$runNpm) {
    $runNpm = false;
    echo "Not running npm install\n";
}

// Is no state restore flag set?
if (in_array('--skipPlatforms', $argv) || !$addPlatforms) {
    $addPlatforms = false;
    echo "Not adding any platforms\n";
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

// Is ignore SSL flag set?
if (in_array('--ignoreSSL', $argv) || !$sslErrors) {
    $sslErrors = false;
    echo "Ignoring SSL Errors\n";
}

// Get API URL from config.ini
$configIni = parse_ini_file('config.ini', true);
if ($configIni === false) {
    // If no config.ini found or not parseable
    error("config.ini could not be found");
}
$apiUrl = $configIni['api_url']; // e.g: http://quiqqer.local/api/quiqqer/app/structure/Mainproject/de


// Install npm Modules
if ($runNpm) {
    echo "\nInstalling Node Modules, this may take a while...\n";
    liveExecuteCommand('npm install');
}


// Add platforms
if ($addPlatforms) {
    $buildPlatforms = getInput("For which platforms do you want to build you app? (Android = a; iOS = i; both = b): ", $cliInput);

    if ($buildPlatforms == 'a' || 'b') {
        echo "\nAdding Android platform (ignore errors), this may take a while...\n";
        liveExecuteCommand('ionic platform add android');
    }

    if ($buildPlatforms == 'i' || 'b') {
        echo "\nAdding iOS platform (ignore errors), this may take a while...\n";
        liveExecuteCommand('ionic platform add ios');
    }
}


// Ignore SSL errors if flag set
$contextOptions = array(
    "ssl" => array(
        "verify_peer"      => $sslErrors,
        "verify_peer_name" => $sslErrors,
    ),
);
$StreamContext  = stream_context_create($contextOptions);

// Try do get data from Api
$apiData = null;
try {
    echo "\nGetting data from API...\n";
    $apiData = json_decode(file_get_contents($apiUrl, true, $StreamContext));
} catch (Exception $ex) {
    // If anything goes wrong exit with error
    error("Invalid API URL!");
}

// If no exception was thrown before but apiData is still not present show an error
if (is_null($apiData)) {
    error("Something went wrong getting data from the API. Make sure you are connected to the internet or try again later.");
}


/**
 * ===============================
 * =            SCSS             =
 * ===============================
 */
echo "\nBuilding styles...\n";

$colors = $apiData->colors;

// Show bottom tab bar?
$tabBarDisplayStyle = '';
if (!$apiData->useBottomMenu) {
    $tabBarDisplayStyle = 'display: none !important;';
}

$bottomBarIconsStyle = "";
$usedIcons           = array();

// Bottom Tab Bar Icons
foreach ($apiData->bottomMenu as $page) {
    $icon = $page->icon;

    // If no icon is set, set a default icon
    if (!$icon || empty($icon)) {
        $icon = 'fa-file-text-o';
    }

    if (!in_array($icon, $usedIcons)) {
        $usedIcons[] = $icon;

        // Add CSS for each icon to use FontAwesome icon in bottom bar
        $bottomBarIconsStyle .= "
        .ion-ios-{$icon}-outline::before,
        .ion-ios-{$icon}::before,
        .ion-md-{$icon}::before {
            @extend .fa-icons-general;
            @extend .{$icon}:before;
        }
        ";
    }
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


// Use FontAwesome Icons in Tabbar
.fa-icons-general {
  display: inline-block;
  font: normal normal normal 14px/1 FontAwesome;
  font-size: 2.5rem;
  text-rendering: auto;
  -webkit-font-smoothing: antialiased;
  -moz-osx-font-smoothing: grayscale;
  transform: translate(0, 0);
}


// Display Tabbar?
ion-tabs {
  .tabbar {
    {$tabBarDisplayStyle}
   
    > a:first-child {
        display: none;
    }
   
    > a.tab-button {
        {$bottomBarIconsStyle}
    }
  }
}


// --------------------------------------------------
// Top Toolbar
// --------------------------------------------------
\$toolbar-background: {$colors->menuBackgroundColor};


// Primary Color for Menu Button
\$colors: (
        primary: {$colors->menuFontColor},
);


// --------------------------------------------------
// Miscellaneous 
// --------------------------------------------------

// Loading Spinner Color
ion-spinner * {
  stroke: #444 !important;
}


";

// Save to file
file_put_contents('src/theme/custom.scss', $scss);


/**
 * ===============================
 * =          CONFIG             =
 * ===============================
 */
echo "\nBuilding config...\n";

// Show advertisement banner?
$showAds = $apiData->advertisment ? 'true' : 'false';

$config = "
export let config = {
    'showAds': {$showAds},
    'admobid': '{$apiData->admobid}',
    'imprintUrl': '{$apiData->imprint->url}',
    'bottomMenuIconLayout': '{$apiData->bottomMenuIconLayout}'
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

// Download the Icon if set in API
if (!empty($logo)) {
    copy($logo, 'resources/icon.png', $StreamContext);
}

// Download the Splashscreen if set in API
if (!empty($splash)) {
    copy($splash, 'resources/splash.png', $StreamContext);
}

// If Logo URL and no Flag set, generate Icon
if ($generateIcon) {
    echo "\nGenerating Icons...\n";

    $cmd = 'ionic resources --icon';

    // Execute Ionic icon generation command
    $result = liveExecuteCommand($cmd);

    if ($result['exit_status'] == 1) {
        error(
            'Something went wrong generating the icons.' . PHP_EOL
            . "Try again later or try running '{$cmd}' manually" . PHP_EOL
            . "Further information:" . PHP_EOL
            . $result['output'],
            false
        );
    }
}

// If Splash URL and no Flag set, generate Splash
if ($generateSplash) {

    echo "\nGenerating Splashscreens...\n";

    $cmd = 'ionic resources --splash';

    // Execute Ionic splash generation command
    $result = liveExecuteCommand($cmd);

    if ($result['exit_status'] == 1) {
        error(
            'Something went wrong generating the splashscreens.' . PHP_EOL
            . "Try again later or try running '{$cmd}' manually" . PHP_EOL
            . "Further information:" . PHP_EOL
            . $result['output'],
            false
        );
    }
}


/**
 * ===============================
 * =         SIDE MENU           =
 * ===============================
 */
echo "\nBuilding side menu...\n";

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
echo "\nBuilding bottom menu...\n";

// Build array of pages for sidemenu
$pages = "// Pages for bottom menu generated via build script\nexport let bottomMenu = [";
foreach ($apiData->bottomMenu as $page) {
    $icon  = $page->icon == false ? 'fa-file-text-o' : $page->icon;
    $pages .= "{title: '{$page->title}', url: '{$page->url}', icon: '{$icon}'},";
}
$pages .= "];";

// Save to file
file_put_contents('src/assets/bottomMenu.ts', $pages);


/**
 * ===============================
 * =      STATIC PAGES/URLs      =
 * ===============================
 */
echo "\nBuilding static pages...\n";

// Build array of pages for sidemenu
$staticURLs = "// static pages generated via build script\nexport let staticUrls = [";
foreach ($apiData->sideMenu as $page) {
    if ($page->isStatic) {
        $staticURLs .= "'{$page->url}',";
    }
}
$staticURLs .= "];";

// Save to file
file_put_contents('src/assets/staticUrls.ts', $staticURLs);


/**
 * ===============================
 * =         CONFIG.xml          =
 * ===============================
 */
echo "\nBuilding config...\n";

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


/**
 * ===============================
 * =      ANDROID APK BUILD      =
 * ===============================
 */
$startAndroidBuild = getInput("Do you want to build the APK file for android now? (y/n): ", $cliInput) == 'y';

if (!$startAndroidBuild) {
    echo "\nNot starting Android build.\n";
    echo "Instructions on how to finish your build for iOS and Android can be found here:\n";
    echo "https://dev.quiqqer.com/quiqqer/app/wikis/build-app\n";
    exit;
}

$sdkPath = getInput("Please enter the absolute path to your Android SDK: ", $cliInput);

if (!is_dir($sdkPath)) {
    error("Directory $sdkPath could not be found, exiting.");
}

if (!is_dir("$sdkPath/tools")) {
    error("Tools directory could not be found inside Android SDK folder ($sdkPath), exiting.");
}

exec("export ANDROID_HOME=$sdkPath");
exec("export PATH=\$PATH:\$ANDROID_HOME/tools");
echo "\nAndroid SDK path set to: $sdkPath\n";

getInput("Press enter to start building the .apk file: ", $cliInput);

echo "\nBuilding .apk file, please wait...\n";
liveExecuteCommand('ionic build android --prod --release');

$apkPath = __DIR__ . "/platforms/android/build/outputs/apk/android-release-unsigned.apk";

$hasKey = getInput("Do you already have a signing key pair? (y/n): ", $cliInput) == 'y';

if ($hasKey) {
    $keyPath = getInput("Please enter the absolute path to your signing key pair: ", $cliInput);
} else {
    echo "\nGenerating signing key pair now. Please follow the instructions:\n";

    liveExecuteCommand("keytool -genkey -v -keystore my-release-key.keystore -alias alias_name -keyalg RSA -keysize 2048 -validity 10000");

    $keyPath = __DIR__ . '/my-release-key.keystore';

    echo "\nSigning key pair stored at: $keyPath. Keep this file or you won't be able to update the app in the future.";
}

if (!file_exists($keyPath)) {
    error("Signing Key Pair could not be found at: $keyPath. Exiting.");
}

getInput("Press enter to start signing the .apk: ", $cliInput);

liveExecuteCommand("jarsigner -verbose -sigalg SHA1withRSA -digestalg SHA1 -keystore $keyPath $apkPath alias_name");

getInput("Press enter to optimize the signed .apk: ", $cliInput);

$buildToolsDirs      = glob("$sdkPath/build-tools/*", GLOB_ONLYDIR);
$latestBuildToolsDir = end($buildToolsDirs);
$zipalignPath        = "$latestBuildToolsDir/zipalign";

while (!file_exists($zipalignPath)) {
    error("Couldn't find zipalign tool under $zipalignPath. Please enter the absolute path here: ", false);
    $zipalignPath = trim(fgets($cliInput));
}

$appName = str_replace(' ', '_', $apiData->title);

liveExecuteCommand("$zipalignPath -v 4 $apkPath $appName.apk");

echo "\nYour app was successfully built as $appName.apk\n";

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
 * Waits for and then returns input from the given CLI [$cli = fopen("php://stdin", "r")]
 *
 * @param $message - Message to display
 * @param $cli - The CLI to listen to
 * @return string - The entered input
 */
function getInput($message, $cli)
{
    echo "\n$message";

    return trim(fgets($cli));
}


/**
 * Outputs an error message (colored red) and exists the program.
 *
 * @param $message
 * @param boolean $exit - Stop execution?
 */
function error($message, $exit = true)
{
    echo "\033[0;31mERROR:" . PHP_EOL . "$message\033[0m" . PHP_EOL;

    if ($exit) {
        exit;
    }
}
