import {Component} from '@angular/core';
import {NavParams, ModalController} from 'ionic-angular';
import {Storage} from '@ionic/storage';

import {HomePage} from '../home/home';
import {bottomMenu} from "../../assets/bottomMenu";
import {config} from "../../app/config";
import {WelcomeModal} from "../../modals/welcome/welcome";
import {staticUrls} from "../../assets/staticUrls";

@Component({
    templateUrl: 'tabs.html'
})
export class TabsPage {
    Home: any = HomePage;
    mySelectedIndex: number;

    bottomMenuPages: Array<any>;
    bottomMenuIconLayout: string = 'icon-top';

    private url: string;
    private title: string;

    constructor(navParams: NavParams,
                modalCtrl: ModalController,
                private storage: Storage,) {
        let self = this;

        // If App is used for first time display Welcome-Modal
        this.isFirstUse().then(function (isFirstUse) {

            if (isFirstUse) {
                if (staticUrls.length > 0) {
                    let Welcome = modalCtrl.create(WelcomeModal, null, {enableBackdropDismiss: false});
                    Welcome.onDidDismiss(function (returnedData) {
                        self.storage.set('isFirstUse', false);
                    });
                    Welcome.present();
                } else {
                    self.storage.set('isFirstUse', false);
                }
            }
        });

        this.bottomMenuPages = bottomMenu;
        this.bottomMenuIconLayout = config.bottomMenuIconLayout;

        // If we are created with params use them
        this.mySelectedIndex = navParams.get('tabIndex') || 0;
        this.url = navParams.get('url');
        this.title = navParams.get('title');
    }

    /**
     * Returns if the users is using this app for the first time. Resolves with a promise containing a boolean.
     *
     * @return {Promise<boolean>} - Promise resolving with boolean
     */
    private isFirstUse(): Promise<boolean> {
        let self = this;
        return new Promise(function (resolve) {
            self.storage.get('isFirstUse').then(function (isFirstUse) {
                if (isFirstUse == null || isFirstUse) {
                    resolve(true);
                } else {
                    resolve(false);
                }
            });
        });
    }
}
