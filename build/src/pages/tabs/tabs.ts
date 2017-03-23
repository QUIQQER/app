import {Component} from '@angular/core';
import {NavParams, ModalController, Platform} from 'ionic-angular';

import {HomePage} from '../home/home';
import {AboutPage} from '../about/about';
import {ContactPage} from '../contact/contact';
import {Network} from "@ionic-native/network";
import {NetworkCheckPage} from "../../modals/network-check/network-check";

@Component({
    templateUrl: 'tabs.html'
})
export class TabsPage
{
    // Pages for the tabs
    tab1Root: any = HomePage;
    tab2Root: any = AboutPage;
    tab3Root: any = ContactPage;
    mySelectedIndex: number;

    private url;
    private title;

    constructor(
        navParams: NavParams,
        private modalCtrl: ModalController,
        private platform: Platform,
        private Network: Network
    )
    {
        // If we lose network connection show no network screen
        Network.onDisconnect().subscribe(() => {
           this.showNoNetworkModal();
        });

        // If we are created with params use them
        this.mySelectedIndex = navParams.get('tabIndex') || 0;
        this.url = navParams.get('url');
        this.title = navParams.get('title');
    }

    /**
     * Shows the no network connection modal
     */
    showNoNetworkModal()
    {
        // Create the modal
        let NetworkCheckModal = this.modalCtrl.create(NetworkCheckPage, {}, {enableBackdropDismiss: false});

        // If the modal is closed and there is still no network show it again
        NetworkCheckModal.onWillDismiss(()=>{
            if(this.Network.type == 'none') {
                this.showNoNetworkModal();
            }
        });

        // Open the modal
        NetworkCheckModal.present();
    }
}
