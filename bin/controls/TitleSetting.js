/**
 * @module package/quiqqer/app/bin/TitleSetting#
 *
 * @require qui/QUI
 * @require qui/controls/Control
 * @require package/quiqqer/translator/bin/controls/Update
 */
define('package/quiqqer/app/bin/controls/TitleSetting', [

    'qui/QUI',
    'qui/controls/Control',
    'controls/lang/InputMultiLang',
    'Ajax'

], function (QUI, QUIControl, InputMultiLang, QUIAjax) {
    "use strict";

    return new Class({

        Extends: QUIControl,
        Type: 'package/quiqqer/app/bin/controls/TitleSetting',

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

            QUIAjax.get([
                'ajax_system_getAvailableLanguages',
                'package_quiqqer_translator_ajax_getVarData'
            ], function (languages, translations) {
                var i, len, lang;
                var data = {};

                for (i = 0, len = languages.length; i < len; i++) {
                    lang = languages[i];

                    if (lang in translations && translations[lang] !== '') {
                        data[lang] = translations[lang];
                    }
                }

                this.$Languages = new InputMultiLang({
                    value: JSON.encode(data),
                    name: 'quiqqerApp.settings.title'
                }).inject(this.$Elm, 'after');

            }.bind(this), {
                'package': 'quiqqer/translator',
                'group': 'quiqqer/app',
                'var': 'app.title.' + this.$Project.getName()
            });
        }
    });
});