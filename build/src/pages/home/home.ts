import {Component} from '@angular/core';
import {NavParams, ModalController} from "ionic-angular";
import {DomSanitizer, SafeResourceUrl} from "@angular/platform-browser";
import {ImprintPage} from "../../modals/imprint/imprint";
import {pages} from "../../app/pages";

@Component({
    selector: 'page-home',
    templateUrl: 'home.html'
})
export class HomePage {
    private url: SafeResourceUrl;
    private title: String = 'Home';

    constructor(private params: NavParams,
                private sanitizer: DomSanitizer,
                private modalCtrl: ModalController) {
        let url = params.get('url');
        let title = params.get('title');

        let Page = pages[0];

        // If opened with URL param we have to tell Angular it's save since it's used for iframe src
        if (typeof url == 'undefined') {
            // If no URL provided (e.g.on startup) use the first url from menu
            this.url = this.getSanitizedUrl(Page.url);
        } else {
            this.url = this.getSanitizedUrl(url);
        }

        if (typeof title != 'undefined') {
            // If no title provided (e.g.on startup) use the first title from menu
            this.title = Page.title;
        }
    }

    /**
     * Opens the imprint modal
     */
    public showImprint() {
        this.modalCtrl.create(ImprintPage).present();
    }


    public getSanitizedUrl(url : string)
    {
        return this.sanitizer.bypassSecurityTrustResourceUrl(url + '?app=1');
    }
}
