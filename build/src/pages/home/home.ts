import {Component} from '@angular/core';
import {NavParams, ModalController} from "ionic-angular";
import {DomSanitizer, SafeResourceUrl} from "@angular/platform-browser";
import {ImprintPage} from "../../modals/imprint/imprint";

@Component({
    selector: 'page-home',
    templateUrl: 'home.html'
})
export class HomePage
{
    private defaultUrl: string = 'http://placehold.it/200x200';
    private url: SafeResourceUrl;

    private title: String = 'Home';

    constructor(private params: NavParams,
                private sanitizer: DomSanitizer,
                private modalCtrl: ModalController)
    {
        let url   = params.get('url');
        let title = params.get('title');

        // If opened with URL param we have to tell Angular it's save since it's used for iframe src
        if (typeof url == 'undefined') {
            // If no URL provided (e.g.on startup) use the default url
            this.url = sanitizer.bypassSecurityTrustResourceUrl(this.defaultUrl);
        } else {
            this.url = sanitizer.bypassSecurityTrustResourceUrl(url+'?app=1');
        }

        // If title provided use it
        if (typeof title != 'undefined') {
            this.title = title;
        }
    }

    /**
     * Opens the imprint modal
     */
    public showImprint()
    {
        this.modalCtrl.create(ImprintPage).present();
    }
}
