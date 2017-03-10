<?php

/**
 * This file contains QUI\APP\EventHandler
 */
namespace QUI\APP;

use QUI;

/**
 * Class RestProvider
 *
 * @package QUI\OAuth
 */
class EventHandler
{
    /**
     * @param $project
     * @param array $config
     * @param array $params
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

        // title
        $Package = QUI::getPackage('quiqqer/app');
        $Config  = $Package->getConfig();
        $group   = 'quiqqer/app';
        $var     = 'app.title.' . $Project->getName();
        $titles  = json_decode($params['quiqqerApp.settings.title'], true);

        try {
            QUI\Translator::add($group, $var, $Package->getName());
        } catch (QUI\Exception $Exception) {
        }

        try {
            QUI\Translator::update(
                'quiqqer/app',
                $var,
                $Package->getName(),
                $titles
            );
        } catch (QUI\Exception $Exception) {
            QUI\System\Log::writeException($Exception);
        }

        // menu
        if (isset($params['quiqqerApp.settings.menu'])) {
            $menu = json_decode($params['quiqqerApp.settings.menu'], true);
            QUI\System\Log::writeRecursive($menu);
            if ($menu) {
                foreach ($menu as $lang => $entries) {
                    $Config->setValue(
                        'menu',
                        $Project->getName() . '_' . $lang,
                        $entries
                    );
                }

                $Config->save();
            }
        }

        QUI\Translator::publish('quiqqer/app');

        // clear cache
        QUI\Cache\Manager::clear(
            'quiqqer/app/settings/' . $Project->getName()
        );
    }


    /**
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
        }
    }
}
