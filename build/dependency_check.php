<?php

require_once "dependency_check_functions.php";

const TEXT_RED   = "\033[31m";
const TEXT_GREEN = "\033[32m";
const TEXT_RESET = "\033[0m";

/**
 * Prints out all dependencies.
 * Dependency is colored green when it's present, otherwise it's colored red
 *
 * @return bool
 */
function checkDependencies()
{
    // [Name of the dependency => function that checks if it's present]
    $dependencies = array(
        'Node'          => 'isNodeInstalled',
        'php-xml'       => 'isPhpXMLInstalled',
        'Oracle JDK'    => 'isOracleJdkInstalled',
        'JAVA_HOME set' => 'isJavaHomeSet',
        'Ionic 2.*'     => 'isIonicInstalled',
        'Cordova'       => 'isCordovaInstalled'
    );

    $allDependenciesPresent = true;

    foreach ($dependencies as $name => $checkFunction) {
        echo TEXT_GREEN;

        // If dependency is not present...
        if (!$checkFunction()) {
            echo TEXT_RED;
            $allDependenciesPresent = false;
        }
        echo $name;

        echo TEXT_RESET;
        echo "\n";
    }

    return $allDependenciesPresent;
}

