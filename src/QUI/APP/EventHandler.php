<?php

/**
 * This file contains QUI\APP\EventHandler
 */

namespace QUI\APP;

use QUI;

/**
 * Class EventHandler
 *
 * @package QUI\APP
 */
class EventHandler
{
    /**
     * Listens to project config save
     *
     * @param $project
     * @param array $config
     * @param array $params
     * @throws QUI\Exception
     */
    public static function onProjectConfigSave($project, array $config, array $params)
    {
        if (!isset($params['quiqqerApp.settings.title'])) {
            return;
        }

        try {
            $Project = QUI::getProject($project);
        } catch (QUI\Exception $Exception) {
            return;
        }

        // title & desc
        $Package = QUI::getPackage('quiqqer/app');
        $Config  = $Package->getConfig();
        $group   = 'quiqqer/app';

        $var_title    = 'app.title.' . $Project->getName();
        $var_desc     = 'app.description.' . $Project->getName();
        $titles       = json_decode($params['quiqqerApp.settings.title'], true);
        $descriptions = json_decode($params['quiqqerApp.settings.description'], true);

        try {
            QUI\Translator::add($group, $var_title, $Package->getName());
        } catch (QUI\Exception $Exception) {
            // Throws error if lang var already exists
        }

        try {
            QUI\Translator::add($group, $var_desc, $Package->getName());
        } catch (QUI\Exception $Exception) {
            // Throws error if lang var already exists
        }

        try {
            QUI\Translator::update(
                'quiqqer/app',
                $var_title,
                $Package->getName(),
                $titles
            );
        } catch (QUI\Exception $Exception) {
            QUI\System\Log::writeException($Exception);
        }

        try {
            QUI\Translator::update(
                'quiqqer/app',
                $var_desc,
                $Package->getName(),
                $descriptions
            );
        } catch (QUI\Exception $Exception) {
            QUI\System\Log::writeException($Exception);
        }


        $isConfigChanged = false;

        // sideMenu
        if (isset($params['quiqqerApp.settings.sideMenu'])) {
            $sideMenu = json_decode($params['quiqqerApp.settings.sideMenu'], true);
            if ($sideMenu) {
                foreach ($sideMenu as $lang => $entries) {
                    $Config->setValue(
                        'sideMenu',
                        $Project->getName() . '_' . $lang,
                        json_encode($entries)
                    );
                }
                $isConfigChanged = true;
            }
        }


        // bottomMenu
        if (isset($params['quiqqerApp.settings.bottomMenu'])) {
            $bottomMenu = json_decode($params['quiqqerApp.settings.bottomMenu'], true);
            if ($bottomMenu) {
                foreach ($bottomMenu as $lang => $entries) {
                    $Config->setValue(
                        'bottomMenu',
                        $Project->getName() . '_' . $lang,
                        json_encode($entries)
                    );
                }
                $isConfigChanged = true;
            }
        }

        if ($isConfigChanged) {
            $Config->save();
        }

        QUI\Translator::publish('quiqqer/app');

        // clear cache
        QUI\Cache\Manager::clear(
            'quiqqer/app/settings/' . $Project->getName()
        );
    }


    /**
     * Listens to page requests
     *
     * @param QUI\Rewrite $Rewrite
     * @param $url
     */
    public static function onRequest(QUI\Rewrite $Rewrite, $url)
    {
        // If request comes from a QUIQQER app
        if (Validate::isAppRequest()) {
            // Save that this is an QUIQQER app session
            QUI::getSession()->set('__APP__', 1);

            // Remove SAMEORIGIN Policy for iframes inside the app
            QUI::getGlobalResponse()->headers->remove("X-Frame-Options");
            QUI::getGlobalResponse()->headers->add(['Access-Control-Allow-Origin' => '*']);
        }
    }


    /**
     * Listens to the template's templateGetHeader-event
     *
     * @param QUI\Template $Template The template that fired the event
     */
    public static function onTemplateGetHeader(QUI\Template $Template)
    {
        if (Validate::isAppRequest()) {
            $Template->extendHeaderWithJavaScriptFile(URL_OPT_DIR . 'quiqqer/app/bin/register-service-worker.js');

            // Disable header and footer
            $Template->setAttributes([
                'template-header' => false,
                'template-footer' => false,
            ]);
        }
    }
}
