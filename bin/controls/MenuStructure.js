/**
 * @module package/quiqqer/app/bin/MenuStructure
 *
 * Auswahl für die Menüstruktur
 *
 * @require qui/QUI
 * @require qui/controls/Control
 * @require qui/controls/buttons/Select
 * @require qui/controls/loader/Loader
 * @require controls/grid/Grid
 * @require Ajax
 * @require Locale
 */
define('package/quiqqer/app/bin/controls/MenuStructure', [

    'qui/QUI',
    'qui/controls/Control',
    'qui/controls/buttons/Select',
    'qui/controls/loader/Loader',
    'qui/controls/windows/Confirm',
    'controls/grid/Grid',
    'Ajax',
    'Locale',

    'css!package/quiqqer/app/bin/controls/MenuStructure.css'

], function (QUI, QUIControl, QUISelect, QUILoader, QUIConfirm, Grid, QUIAjax, QUILocale) {
    "use strict";

    var lg = 'quiqqer/app';

    return new Class({

        Extends: QUIControl,
        Type: 'package/quiqqer/app/bin/controls/MenuStructure',

        Binds: [
            '$onImport',
            'setProject',
            'refresh',
            'save',
            'openAddDialog',
            'openRemoveDialog',
            '$refreshButtons'
        ],

        initialize: function (options) {
            this.$Elm = null;
            this.$Languages = null;
            this.$Project = null;

            this.Loader = null;

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

            this.$Input = this.getElm();

            this.$Elm = new Element('div', {
                'class': 'quiqqer-app-menu-structure',
                html: '<div class="quiqqer-app-menu-structure-langselect"></div>' +
                '<div class="quiqqer-app-menu-structure-data"></div>'
            }).wraps(this.$Elm);

            this.Loader = new QUILoader().inject(this.$Elm);


            var SelectContainer = this.$Elm.getElement(
                '.quiqqer-app-menu-structure-langselect'
            );

            var GridContainer = this.$Elm.getElement(
                '.quiqqer-app-menu-structure-data'
            );

            this.$Grid = new Grid(GridContainer, {
                buttons: [{
                    name: 'up',
                    icon: 'fa fa-chevron-up',
                    disabled: true,
                    events: {
                        onClick: function () {
                            this.$Grid.moveup();
                            this.save();
                        }.bind(this)
                    }
                }, {
                    name: 'down',
                    icon: 'fa fa-chevron-down',
                    disabled: true,
                    events: {
                        onClick: function () {
                            this.$Grid.movedown();
                            this.save();
                        }.bind(this)
                    }
                }, {
                    type: 'seperator'
                }, {
                    name: 'add',
                    textimage: 'fa fa-plus',
                    text: QUILocale.get('quiqqer/system', 'add'),
                    events: {
                        onClick: this.openAddDialog
                    }
                }, {
                    name: 'remove',
                    textimage: 'fa fa-trash',
                    text: QUILocale.get('quiqqer/system', 'remove'),
                    events: {
                        onClick: this.openRemoveDialog
                    },
                    disabled: true
                }],
                columnModel: [{
                    header: QUILocale.get('quiqqer/system', 'id'),
                    dataIndex: 'id',
                    dataType: 'number',
                    width: 60
                }, {
                    header: QUILocale.get('quiqqer/system', 'title'),
                    dataIndex: 'title',
                    dataType: 'string',
                    width: 200
                }, {
                    header: QUILocale.get('quiqqer/system', 'name'),
                    dataIndex: 'name',
                    dataType: 'string',
                    width: 200
                }],
                height: 300,
                sortHeader: false
            });

            this.$Grid.addEvents({
                onClick: this.$refreshButtons
            });

            this.$Select = new QUISelect({
                events: {
                    onChange: this.refresh
                },
                styles: {
                    width: '100%'
                }
            }).inject(SelectContainer);

            this.$getLanguages().then(function (langs) {
                var i, len, lang;

                for (i = 0, len = langs.length; i < len; i++) {
                    lang = langs[i];

                    this.$Select.appendChild(
                        QUILocale.get('quiqqer/system', 'language.' + lang),
                        lang,
                        URL_BIN_DIR + '16x16/flags/' + lang + '.png'
                    );
                }

                this.$Select.setValue(langs[0]);
            }.bind(this));
        },

        /**
         *
         * @returns {Promise}
         */
        refresh: function () {
            var self = this;

            this.Loader.show();

            return new Promise(function (resolve, reject) {
                var language = self.$Select.getValue();

                if (self.$Input.value !== '') {
                    var value = self.$Input.value;

                    try {
                        data = JSON.decode(value);

                        if (!data) {
                            data = {};
                        }
                    } catch (e) {
                        var data = {};
                    }

                    if (language in data) {
                        var ids = data[language].split(',');

                        QUIAjax.get('package_quiqqer_app_ajax_getSitesData', function (result) {
                            self.$Grid.setData({
                                data: result
                            });

                            self.Loader.hide();
                            resolve();
                        }, {
                            'package': 'quiqqer/app',
                            project: JSON.encode({
                                name: self.$Project.getName(),
                                lang: language
                            }),
                            ids: JSON.encode(ids),
                            onError: reject
                        });

                        return;
                    }
                }

                QUIAjax.get('package_quiqqer_app_ajax_getMenu', function (result) {
                    self.$Grid.setData({
                        data: result
                    });

                    self.save();
                    self.Loader.hide();
                    resolve();
                }, {
                    'package': 'quiqqer/app',
                    project: JSON.encode({
                        name: self.$Project.getName(),
                        lang: language
                    }),
                    onError: reject
                });
            });
        },

        /**
         * Saves the current language data to the input
         */
        save: function () {
            var data = {},
                value = this.$Input.value,
                lang = this.$Select.getValue();

            try {
                data = JSON.decode(value);

                if (!data) {
                    data = {};
                }
            } catch (e) {
            }

            var ids = this.$Grid.getData().map(function (Entry) {
                return Entry.id;
            });

            data[lang] = ids.join(',');

            this.$Input.value = JSON.encode(data);
        },

        /**
         * Open the dialog to add a site
         */
        openAddDialog: function () {
            var self = this;

            require(['controls/projects/Popup'], function (Popup) {
                new Popup({
                    events: {
                        onSubmit: function (Win, value) {
                            self.addSite(value.ids[0]).then(function () {
                                self.$refreshButtons();
                            });
                        }
                    }
                }).open();
            });
        },

        /**
         * Opens the delete / remove dialog
         */
        openRemoveDialog: function () {
            var self = this,
                selectedData = this.$Grid.getSelectedData(),
                selectedIndices = this.$Grid.getSelectedIndices();

            new QUIConfirm({
                icon: 'fa fa-trash',
                title: QUILocale.get(lg, 'window.remove.title'),
                maxHeight: 300,
                maxWidth: 450,
                events: {
                    onOpen: function (Win) {
                        Win.getContent().set('html', QUILocale.get(lg, 'window.remove.content'));
                    },

                    onSubmit: function () {
                        self.$Grid.deleteRows(selectedIndices);
                        self.save();
                        self.$refreshButtons();
                    }
                }
            }).open();
        },

        /**
         * Add a site to the grid
         *
         * @param {String|Number} id - Site ID
         */
        addSite: function (id) {
            var self = this;

            this.Loader.show();

            return new Promise(function (resolve) {
                QUIAjax.get('package_quiqqer_app_ajax_getSiteData', function (result) {
                    self.$Grid.addRow({
                        id: result.id,
                        title: result.title,
                        name: result.name
                    });

                    self.save();
                    self.Loader.hide();
                    resolve();
                }, {
                    'package': 'quiqqer/app',
                    project: JSON.encode({
                        name: self.$Project.getName(),
                        lang: self.$Select.getValue()
                    }),
                    id: id
                });
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
        },

        /**
         * Refresh the status of each button
         */
        $refreshButtons: function () {
            var buttons = this.$Grid.getButtons(),
                selected = this.$Grid.getSelectedIndices();

            var Add = buttons.filter(function (Btn) {
                return Btn.getAttribute('name') == 'add';
            })[0];

            var Remove = buttons.filter(function (Btn) {
                return Btn.getAttribute('name') == 'remove';
            })[0];

            var Up = buttons.filter(function (Btn) {
                return Btn.getAttribute('name') == 'up';
            })[0];

            var Down = buttons.filter(function (Btn) {
                return Btn.getAttribute('name') == 'down';
            })[0];

            Add.enable();
            Remove.disable();
            Up.disable();
            Down.disable();

            if (selected.length) {
                Remove.enable();
                Up.enable();
                Down.enable();
            }
        }
    });
});
