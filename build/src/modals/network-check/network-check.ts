import {Component} from '@angular/core';
import {ViewController} from 'ionic-angular';
import {Network} from "ionic-native";

@Component({
    selector: 'page-network-check',
    templateUrl: 'network-check.html'
})
export class NetworkCheckPage {

    constructor(public viewCtrl: ViewController) {
        Network.onConnect().subscribe(() => {
           this.dismiss();
        });
    }

    ionViewDidLoad() {
        console.log('ionViewDidLoad NetworkCheckPage');
    }

    dismiss() {
        this.viewCtrl.dismiss();
    }

}
