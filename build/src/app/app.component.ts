import {Component, ViewChild} from '@angular/core';
import {Platform, MenuController, Nav} from 'ionic-angular';
import {StatusBar, Splashscreen} from 'ionic-native';

import {TabsPage} from '../pages/tabs/tabs';
import {Test} from "tslint/lib/lint";

export interface PageInterface {
    title: string;
    component: any;
    icon: string;
    url?: string;
    tabIndex?: number;
}

@Component({
    templateUrl: 'app.html'
})
export class MyApp {
    @ViewChild(Nav) nav: Nav;

    // set our app's pages
    appPages: PageInterface[] = [
        {title: 'Home', component: TabsPage, icon: 'calendar'},
        {title: 'About', component: TabsPage, tabIndex: 1, icon: 'information-circle'},
        {title: 'Contact', component: TabsPage, tabIndex: 2, icon: 'contacts'},
        {title: 'Test', component: TabsPage, tabIndex: 0, icon: 'arrow-forward', url: 'http://placehold.it/300x300'}
    ];

    rootPage = TabsPage;

    constructor(platform: Platform, public menu: MenuController) {
        platform.ready().then(() => {
            StatusBar.styleDefault();
            Splashscreen.hide();
        });

    }

    openPage(page: PageInterface) {
        // the nav component was found using @ViewChild(Nav)
        // reset the nav to remove previous pages and only have this page
        // we wouldn't want the back button to show in this scenario
        this.menu.close();

        this.nav.setRoot(page.component, {tabIndex: page.tabIndex, url: page.url, title: page.title});
    }

}
