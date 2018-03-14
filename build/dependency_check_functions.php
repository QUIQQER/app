<?php

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


function isPhpXMLInstalled()
{
    return in_array('xml', get_loaded_extensions());
}


function isOracleJdkInstalled()
{
    exec("javac -version 2> /dev/null", $output, $returnCode);

    if ($returnCode != 0) {
        return false;
    }

    return true;
}


function isJavaHomeSet()
{
    if (!getenv('JAVA_HOME')) {
        return false;
    }

    return true;
}


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
