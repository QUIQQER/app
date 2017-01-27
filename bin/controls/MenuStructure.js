/**
 * @module package/quiqqer/app/bin/MenuStructure
 *
 * @require qui/QUI
 * @require qui/controls/Control
 * @require Ajax
 */
define('package/quiqqer/app/bin/controls/MenuStructure', [

    'qui/QUI',
    'qui/controls/Control',
    'Ajax'

], function (QUI, QUIControl, QUIAjax) {
    "use strict";

    return new Class({

        Extends: QUIControl,
        Type: 'package/quiqqer/app/bin/controls/MenuStructure',

        Binds: [
            '$onImport',
            'setProject'
        ],

        initialize: function (options) {
            this.$Elm = null;
            this.$Languages = null;
            this.$Project = null;

            this.addEvents({
                onImport: this.$onImport,
                onInject: this.$onInject
            });

            this.parent(options);
        },

        /**
         * Set the internal project
         *
         * @param {Object} Project
         */
        setProject: function (Project) {
            this.$Project = Project;

            if (!this.$Languages) {
                this.$onImport();
            }
        },

        /**
         * event : on import
         */
        $onImport: function () {
            if (!this.$Project) {
                return;
            }

            this.$getLanguages().then(function (langs) {

            });
        },

        /**
         * Return all available languages
         *
         * @returns {Promise}
         */
        $getLanguages: function () {
            return new Promise(function (resolve) {
                QUIAjax.get('ajax_system_getAvailableLanguages', resolve);
            });
        }
    });
});