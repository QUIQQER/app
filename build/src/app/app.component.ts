import {Component, ViewChild} from '@angular/core';
import {Platform, MenuController, Nav} from 'ionic-angular';
import {StatusBar, Splashscreen, AdMob} from 'ionic-native';

import {TabsPage} from '../pages/tabs/tabs';
import {config} from "./config";
import {pages} from "./pages";
import {TranslateService} from "ng2-translate";

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
export class MyApp
{
    @ViewChild(Nav) nav: Nav;

    appPages: PageInterface[] = [];

    rootPage = TabsPage;

    constructor(private platform: Platform,
                public menu: MenuController,
                private translate: TranslateService)
    {
        this.initializeTranslation();

        // Add pages to sidemenu
        for (let page of pages) {
            this.appPages.push({
                title: page.title,
                component: TabsPage,
                tabIndex: 0,
                icon: 'paper',
                url: page.url
            });
        }

        platform.ready().then(() => {
            StatusBar.styleDefault();
            Splashscreen.hide();

            if(AdMob && config.showAds) {
                AdMob.createBanner({
                    adId: config.admobid,
                    autoShow: true,
                    position: 8 // Bottom Center
                });
            }
        });

    }


    /**
     * Initializes the translation service with the current users language.
     * @return {Observable<any>} - Completes when language loaded
     */
    initializeTranslation()
    {
        let userLang = navigator.language.split('-')[0];
        userLang = /(de|en)/gi.test(userLang) ? userLang : 'en';

        this.translate.setDefaultLang('en');
        return this.translate.use(userLang);
    }


    /**
     * Opens a page as root level (ergo no back button) from the sidemenu
     * @param {PageInterface} page - The page to open
     */
    openPage(page: PageInterface)
    {
        // Close the open menu
        this.menu.close();

        // Open page as root level so there is no back button
        this.nav.setRoot(page.component, {tabIndex: page.tabIndex, url: page.url, title: page.title});
    }

}
