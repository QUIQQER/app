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

        $Package = QUI::getPackage('quiqqer/app');
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

    }
}
