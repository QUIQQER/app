import {Component} from '@angular/core';
import {ViewController} from 'ionic-angular';
import {Network} from "ionic-native";

@Component({
    selector: 'page-network-check',
    templateUrl: 'network-check.html'
})
export class NetworkCheckPage
{
    constructor(public viewCtrl: ViewController)
    {
        // If network connection established we can close this page
        Network.onConnect().subscribe(() => {
           this.dismiss();
        });
    }


    /**
     * Closes this page
     */
    dismiss()
    {
        this.viewCtrl.dismiss();
    }

}
