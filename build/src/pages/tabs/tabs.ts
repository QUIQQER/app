import {Component} from '@angular/core';
import {NavParams, ModalController, Platform} from 'ionic-angular';

import {HomePage} from '../home/home';
import {AboutPage} from '../about/about';
import {ContactPage} from '../contact/contact';
import {Network} from "ionic-native";
import {NetworkCheckPage} from "../../modals/network-check/network-check";

@Component({
    templateUrl: 'tabs.html'
})
export class TabsPage {
    // this tells the tabs component which Pages
    // should be each tab's root Page
    tab1Root: any = HomePage;
    tab2Root: any = AboutPage;
    tab3Root: any = ContactPage;
    mySelectedIndex: number;

    private url;
    private title;

    constructor(navParams: NavParams, private modalCtrl: ModalController, private platform: Platform)
    {
        Network.onDisconnect().subscribe(() => {
           this.showNoNetworkModal();
        });

        this.mySelectedIndex = navParams.get('tabIndex') || 0;
        this.url = navParams.get('url');
        this.title = navParams.get('title');
    }

    showNoNetworkModal()
    {
        let NetworkCheckModal = this.modalCtrl.create(NetworkCheckPage, {}, {enableBackdropDismiss: false});
        NetworkCheckModal.onWillDismiss(()=>{
            if(Network.type == 'none') {
                this.showNoNetworkModal();
            }
        });
        NetworkCheckModal.present();
    }
}
