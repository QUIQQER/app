import {Component} from '@angular/core';
import {ViewController} from 'ionic-angular';

@Component({
    selector: 'page-imprint',
    templateUrl: 'imprint.html'
})
export class ImprintPage {

    constructor(private viewCtrl: ViewController) {}

    public dismiss() {
        this.viewCtrl.dismiss();
    }
}
