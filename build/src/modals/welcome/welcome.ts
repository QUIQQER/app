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

    public staticPagesInitial: SafeResourceUrl[] = [];
    public staticPagesFinal: SafeResourceUrl[] = [];
    private loadedPagesInitial: number = 0;
    private loadedPagesFinal: number = 0;
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
    }


    private loadPages() {
        this.staticPagesInitial = staticUrls.map(this.getSanitizedUrl.bind(this));

        if (this.staticPagesInitial.length == 0) {
            this.dismiss();
        }
    }


    public initialIframeLoaded(staticPage) {

        if(staticPage) {
            console.log("Loaded", staticPage);
            this.loadedPagesInitial++;
            this.staticPagesFinal.push(staticPage);
        }

    }


    public finalIframeLoaded(staticPage) {
        if(staticPage) {
            console.log("Loaded second time: ", staticPage);
            this.loadedPagesFinal++;
            if (this.loadedPagesFinal == staticUrls.length) {
                console.log('All pages loaded twice');
                this.dismiss();
            }
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
