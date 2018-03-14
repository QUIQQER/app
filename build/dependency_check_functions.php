<?php

/**
 * Returns if Node is installed
 * Interprets the output of 'node -v' and it's return code
 *
 * @return bool
 */
function isNodeInstalled()
{
    exec("node -v", $output, $returnCode);

    if ($returnCode != 0) {
        return false;
    }

    if (strpos(trim($output[0]), 'v') != 0) {
        return false;
    }

    return true;
}


/**
 * Returns if php-xml extension is installed
 * Checks if 'xml' is in the loaded php-extensions
 *
 * @return bool
 */
function isPhpXMLInstalled()
{
    return in_array('xml', get_loaded_extensions());
}


/**
 * Checks if Oracle JDK is installed
 * Interprets the return code of 'javac -version'
 *
 * @return bool
 */
function isOracleJdkInstalled()
{
    // Route output of the command to /dev/null since javac behaves weird with exec
    exec("javac -version 2> /dev/null", $output, $returnCode);

    if ($returnCode != 0) {
        return false;
    }

    return true;
}


/**
 * Checks if the JAVA_HOME environment variable is set
 *
 * @return bool
 */
function isJavaHomeSet()
{
    if (!getenv('JAVA_HOME')) {
        return false;
    }

    return true;
}


/**
 * Checks if Ionic Framework is installed (globally)
 * Interprets the output and return code of 'ionic -v'
 *
 * @return bool
 */
function isIonicInstalled()
{
    exec('ionic -v', $output, $returnCode);

    if ($returnCode != 0) {
        return false;
    }

    $output = trim($output[0]);
    if (!preg_match('/^\d+\.\d+\.\d+$/', $output) && strpos($output, '2.') != 0) {
        return false;
    }

    return true;
}


/**
 * Checks if Cordova is installed (globally)
 * Interprets the output and return code of 'cordova -v'
 *
 * @return bool
 */
function isCordovaInstalled()
{
    exec('cordova -v', $output, $returnCode);

    if ($returnCode != 0) {
        return false;
    }

    $output = trim($output[0]);
    if (!preg_match('/^\d+\.\d+\.\d+$/', $output)) {
        return false;
    }

    return true;
}
