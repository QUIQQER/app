import {Component} from '@angular/core';
import {NavParams, ModalController} from 'ionic-angular';
import {Storage} from '@ionic/storage';

import {HomePage} from '../home/home';
import {Network} from "@ionic-native/network";
import {NetworkCheckPage} from "../../modals/network-check/network-check";
import {bottomMenu} from "../../assets/bottomMenu";
import {config} from "../../app/config";
import {WelcomeModal} from "../../modals/welcome/welcome";

@Component({
    templateUrl: 'tabs.html'
})
export class TabsPage {
    // Pages for the tabs
    Home: any = HomePage;
    mySelectedIndex: number;

    bottomMenuPages: Array<any>;
    bottomMenuIconLayout: string = 'icon-top';
    private url;

    constructor(navParams: NavParams,
                private modalCtrl: ModalController,
                private Network: Network,
                private storage: Storage,) {
        let self = this;

        // If App is used for first time display Welcome-Modal
        this.isFirstUse().then(function (isFirstUse) {

            if (isFirstUse) {
                let Welcome = modalCtrl.create(WelcomeModal, null, {enableBackdropDismiss: false});
                Welcome.onDidDismiss(function (returnedData) {
                    self.storage.set('isFirstUse', false);
                });
                Welcome.present();
            }
        });


        // If we lose network connection show no network screen
        Network.onDisconnect().subscribe(() => {
            // this.showNoNetworkModal();
        });

        this.bottomMenuPages = bottomMenu;
        this.bottomMenuIconLayout = config.bottomMenuIconLayout;

        // If we are created with params use them
        this.mySelectedIndex = navParams.get('tabIndex') || 0;
        this.url = navParams.get('url');
        this.title = navParams.get('title');
    }

    private title;

    /**
     * Shows the no network connection modal
     */
    showNoNetworkModal() {
        // Create the modal
        let NetworkCheckModal = this.modalCtrl.create(NetworkCheckPage, {}, {enableBackdropDismiss: false});

        // If the modal is closed and there is still no network show it again
        NetworkCheckModal.onWillDismiss(() => {
            if (this.Network.type == 'none') {
                // this.showNoNetworkModal();
            }
        });

        // Open the modal
        NetworkCheckModal.present();
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
