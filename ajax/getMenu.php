<?php

/**
 * This file contains package_quiqqer_app_getMenu
 */

/**
 * Returns the menu entries from the project
 *
 * @param {String} $project - Project JSON data
 * @param {String} $menu - Which menu to get ('sideMenu' or 'bottomMenu')
 *
 * @return array
 */
QUI::$Ajax->registerFunction(
    'package_quiqqer_app_ajax_getMenu',
    function ($project, $menu) {

        $Project = QUI::getProjectManager()->decode($project);
        $Config  = QUI::getPackage('quiqqer/app')->getConfig();

        $ids = $Config->getValue($menu, $Project->getName() . '_' . $Project->getLang());

        if (!$ids) {
            return array();
        }

        $result = array();
        $ids    = explode(',', $ids);

        foreach ($ids as $id) {
            try {
                $Site     = $Project->get($id);
                $result[] = array(
                    'id'    => $Site->getId(),
                    'title' => $Site->getAttribute('title'),
                    'name'  => $Site->getAttribute('name')
                );
            } catch (QUI\Exception $Exception) {
            }
        }

        return $result;
    },
    array('project', 'menu'),
    'Permission::checkAdminUser'
);
