<?php

/**
 * This file contains QUI\APP\Validate
 */
namespace QUI\APP;

use QUI;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class Validate
 * @package QUI\APP
 */
class Validate
{
    /**
     * Validates is the given request is a QUIQQER app request. If no request is given the current request is used.
     * @param Request|null $Request
     * @return bool - Returns true if the request is from a QUIQQER app, otherwise false
     */
    public static function isAppRequest(Request $Request = null)
    {
        if ($Request == null) {
            $Request = QUI::getRequest();
        }

        if ($Request->query->get('app') == 1) {
            return true;
        }

        if (QUI::getSession()->get('__APP__') == 1) {
            return true;
        }

        return false;
    }
}
