import {Component} from '@angular/core';
import {ViewController} from 'ionic-angular';
import {SafeResourceUrl, DomSanitizer} from "@angular/platform-browser";
import {config} from "../../app/config";

@Component({
    selector: 'page-imprint',
    templateUrl: 'imprint.html'
})
export class ImprintPage
{
    private url: SafeResourceUrl;

    constructor(private viewCtrl: ViewController, sanitizer: DomSanitizer)
    {
        // URL is used for iframe src, so we have to tell Angular it's save
        this.url = sanitizer.bypassSecurityTrustResourceUrl(config.imprintUrl+'?app=1');
    }

    /**
     * Closes the imprint
     */
    public dismiss()
    {
        this.viewCtrl.dismiss();
    }
}
