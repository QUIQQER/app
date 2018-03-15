import {Component} from '@angular/core';
import {DomSanitizer, SafeResourceUrl} from "@angular/platform-browser";
import {ViewController} from 'ionic-angular';

import {staticUrls} from "../../assets/staticUrls";
import {Network} from "@ionic-native/network";

@Component({
    selector: 'modal-welcome',
    templateUrl: 'welcome.html'
})
export class WelcomeModal {

    public staticPages: SafeResourceUrl[] = [];
    private loadedPages: number = 0;
    private isOffline: boolean;

    constructor(public viewCtrl: ViewController, private sanitizer: DomSanitizer, Network: Network,) {
        this.isOffline = Network.type == 'none';

        if (this.isOffline) {
            // If we're offline wait until we're online before loading pages
            Network.onConnect().subscribe(() => {
                this.isOffline = false;
                console.log('Device is offline: ', this.isOffline);
                this.loadPages();
            });
        } else {
            // We're online, time to load the pages
            this.loadPages();
        }

        console.log('Device is offline: ', this.isOffline);
    }


    private loadPages() {
        this.staticPages = staticUrls.map(this.getSanitizedUrl.bind(this));

        if (this.staticPages.length == 0) {
            this.dismiss();
        }
    }


    public iframeLoaded() {

        this.loadedPages++;

        if (this.loadedPages == staticUrls.length) {
            console.log('All pages loaded');
            this.dismiss();
        }
    }

    public getSanitizedUrl(url: string) {
        return this.sanitizer.bypassSecurityTrustResourceUrl(url + '?app=1');
    }


    /**
     * Closes this page
     */
    dismiss() {
        this.viewCtrl.dismiss();
    }

}
