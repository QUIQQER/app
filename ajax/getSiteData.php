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
    'package_quiqqer_app_ajax_getSiteData',
    function ($project, $id) {
        $Project = QUI::getProjectManager()->decode($project);
        $Site    = $Project->get($id);

        return array(
            'id'    => $Site->getId(),
            'title' => $Site->getAttribute('title'),
            'name'  => $Site->getAttribute('name')
        );
    },
    array('project', 'id'),
    'Permission::checkAdminUser'
);
