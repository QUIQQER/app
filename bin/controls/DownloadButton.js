/**
 * @module package/quiqqer/app/bin/DownloadButton
 *
 * @require qui/QUI
 * @require qui/controls/Control
 * @require package/quiqqer/translator/bin/controls/Update
 * @require Ajax
 */
define('package/quiqqer/app/bin/controls/DownloadButton', [

    'qui/QUI',
    'qui/controls/Control',
    'qui/controls/buttons/Button',
    'Ajax',
    'Locale'

], function (QUI, QUIControl, QUIButton, QUIAjax, QUILocale)
{
    "use strict";

    var lg = 'quiqqer/app';


    return new Class({

        Extends: QUIControl,
        Type   : 'package/quiqqer/app/bin/controls/DownloadButton',

        Binds: [
            '$onImport',
            'setProject',
            '$download'
        ],

        initialize: function (options)
        {
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
        setProject: function (Project)
        {
            this.$Project = Project;

            if (!this.$Languages) {
                this.$onImport();
            }
        },

        /**
         * event : on import
         */
        $onImport: function ()
        {
            if (!this.$Project) {
                return;
            }

            var Parent = this.getElm().getParent();

            this.getElm().destroy();

            new QUIButton({
                icon   : 'fa fa-download',
                text   : QUILocale.get(lg, 'download.button'),
                'class': Parent.className,
                events : {
                    onClick: this.$download
                }
            }).inject(Parent);

        },

        $download: function ()
        {
            window.open('//' + window.location.hostname + '/packages/quiqqer/app/bin/download.php', '_blank')
        }
    });
});