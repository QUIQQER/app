import {Component} from '@angular/core';
import {NavParams} from "ionic-angular";
import {DomSanitizer, SafeResourceUrl} from "@angular/platform-browser";

@Component({
    selector: 'page-home',
    templateUrl: 'home.html'
})
export class HomePage {

    private defaultUrl: string = 'http://placehold.it/200x200';
    private url: SafeResourceUrl;

    private title: String = 'Home';

    constructor(params: NavParams, sanitizer: DomSanitizer) {
        let url   = params.get('url');
        let title = params.get('title');

        if (typeof url == 'undefined') {
            this.url = sanitizer.bypassSecurityTrustResourceUrl(this.defaultUrl);
        } else {
            this.url = sanitizer.bypassSecurityTrustResourceUrl(url);
        }

        if (typeof title != 'undefined') {
            this.title = title;
        }

    }

}
