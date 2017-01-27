/**
 * @module package/quiqqer/app/bin/AvailableLanguages#
 *
 * @require qui/QUI
 * @require qui/controls/Control
 * @require Ajax
 * @require Locale
 * @require css!package/quiqqer/app/bin/controls/AvailableLanguages.css
 */
define('package/quiqqer/app/bin/controls/AvailableLanguages', [

    'qui/QUI',
    'qui/controls/Control',
    'Ajax',
    'Locale',

    'css!package/quiqqer/app/bin/controls/AvailableLanguages.css'

], function (QUI, QUIControl, QUIAjax, QUILocale) {
    "use strict";

    return new Class({

        Extends: QUIControl,
        Type: 'package/quiqqer/app/bin/controls/AvailableLanguages',

        Binds: [
            '$onImport',
            'setProject',
            '$onCheckboxChange'
        ],

        initialize: function (options) {
            this.parent(options);

            this.$Elm = null;
            this.$Input = null;
            this.$Project = null;

            this.addEvents({
                onImport: this.$onImport,
                onInject: this.$onInject
            });
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

            this.$Input = this.getElm();

            this.$Elm = new Element('div', {
                'class': 'field-container-field quiqqer-app-setting-availableLanguages'
            }).wraps(this.$Input);


            QUIAjax.get('ajax_system_getAvailableLanguages', function (languages) {
                var i, len, lang, Lang, Label;

                for (i = 0, len = languages.length; i < len; i++) {
                    lang = languages[i];

                    Label = new Element('label', {
                        html: '<input type="checkbox" value="' + lang + '" />' +
                        QUILocale.get('quiqqer/system', 'language.' + lang),
                        'class': 'quiqqer-app-setting-availableLanguages-language'
                    }).inject(this.$Elm);

                    Label.getElement('input').addEvent('change', this.$onCheckboxChange);
                }

                var values = this.$Input.value.split(','),
                    list = this.$Elm.getElements('[type="checkbox"]');

                var findLanguageElement = function (lang) {
                    for (var c = 0, clen = list.length; c < clen; c++) {
                        if (list[c].value == lang) {
                            return list[c];
                        }
                    }
                    return false;
                };

                for (i = 0, len = values.length; i < len; i++) {
                    Lang = findLanguageElement(values[i]);
                    if (Lang) {
                        Lang.checked = true;
                    }
                }
            }.bind(this));
        },

        /**
         * event : on checkbox change
         */
        $onCheckboxChange: function () {
            var selected = this.$Elm.getElements('[type="checkbox"]').filter(function (Elm) {
                return Elm.checked;
            }).map(function (Elm) {
                return Elm.value;
            });

            this.$Input.value = selected.join(',');
            this.$Input.fireEvent('change');
        }
    });
});