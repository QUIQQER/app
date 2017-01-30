<?php

/**
 * This file contains package_quiqqer_app_ajax_getSiteData
 */

/**
 * Returns the data of a site
 *
 * @param {string} $project - Project JSON data
 * @param {string|integer} $id - Project JSON data
 *
 * @return array
 */
QUI::$Ajax->registerFunction(
    'package_quiqqer_app_ajax_getSitesData',
    function ($project, $ids) {
        $Project = QUI::getProjectManager()->decode($project);
        $ids     = json_decode($ids, true);
        $result  = array();

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
    array('project', 'ids'),
    'Permission::checkAdminUser'
);
