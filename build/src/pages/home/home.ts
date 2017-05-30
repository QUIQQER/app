import {Component} from '@angular/core';
import {NavParams, ModalController} from "ionic-angular";
import {DomSanitizer, SafeResourceUrl} from "@angular/platform-browser";
import {ImprintPage} from "../../modals/imprint/imprint";
import {pages} from "../../app/pages";
import {Network} from "@ionic-native/network";
import {staticUrls} from "../../assets/staticUrls";

@Component({
    selector: 'page-home',
    templateUrl: 'home.html'
})
export class HomePage {
    private url: SafeResourceUrl;
    private title: String = 'Home';
    private isOffline: boolean = false;
    private isStaticPage: boolean = false;

    constructor(private params: NavParams,
                private sanitizer: DomSanitizer,
                private modalCtrl: ModalController,
                private Network: Network,) {
        let url = params.get('url');
        let title = params.get('title');
        let Page = pages[0];

        this.isOffline = Network.type == 'none';

        if (typeof url == 'undefined') {
            // If no URL provided (e.g.on startup) use the first url from menu
            url = Page.url;
        }

        this.isStaticPage = staticUrls.indexOf(url) > -1;

        this.url = this.getSanitizedUrl(url);

        if (typeof title == 'undefined') {
            // If no title provided (e.g.on startup) use the first title from menu
            this.title = Page.title;
        }

        let self = this;
        Network.onConnect().subscribe(() => {
            self.isOffline = false;
        });
        Network.onDisconnect().subscribe(() => {
            self.isOffline = true;
        });
    }

    /**
     * Opens the imprint modal
     */
    public showImprint() {
        this.modalCtrl.create(ImprintPage).present();
    }

    /**
     * Returns an security bypassed url so that it can be used in HTML
     * @param {string} url
     * @return {SafeResourceUrl}
     */
    public getSanitizedUrl(url: string) {
        return this.sanitizer.bypassSecurityTrustResourceUrl(url + '?app=1');
    }
}
