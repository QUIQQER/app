import {Component} from '@angular/core';
import {NavParams} from 'ionic-angular';

import {HomePage} from '../home/home';
import {AboutPage} from '../about/about';
import {ContactPage} from '../contact/contact';

@Component({
    templateUrl: 'tabs.html'
})
export class TabsPage {
    // this tells the tabs component which Pages
    // should be each tab's root Page
    tab1Root: any = HomePage;
    tab2Root: any = AboutPage;
    tab3Root: any = ContactPage;
    mySelectedIndex: number;

    private url;
    private title;

    constructor(navParams: NavParams) {
        this.mySelectedIndex = navParams.get('tabIndex') || 0;
        this.url = navParams.get('url');
        this.title = navParams.get('title');
    }
}
