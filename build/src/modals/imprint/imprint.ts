import {Component} from '@angular/core';
import {ViewController} from 'ionic-angular';
import {SafeResourceUrl, DomSanitizer} from "@angular/platform-browser";
import {config} from "../../app/config";

@Component({
    selector: 'page-imprint',
    templateUrl: 'imprint.html'
})
export class ImprintPage {

    constructor(private viewCtrl: ViewController,
                private sanitizer: DomSanitizer,) {
        this.url = sanitizer.bypassSecurityTrustResourceUrl(config.imprintUrl+'?app=1');
    }

    private url: SafeResourceUrl;

    public dismiss() {
        this.viewCtrl.dismiss();
    }
}
