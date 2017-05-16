<?php

/**
 * This file contains QUI\APP\RestProvider
 */

namespace QUI\APP;

use QUI;
use QUI\REST\Server;

use Psr\Http\Message\ServerRequestInterface as RequestInterface;
use Psr\Http\Message\ResponseInterface as ResponseInterface;

/**
 * Class RestProvider
 *
 * @package QUI\OAuth
 */
class RestProvider implements QUI\REST\ProviderInterface
{
    /**
     * @param Server $Server
     */
    public function register(Server $Server)
    {
        $Slim = $Server->getSlim();

        $Slim->get('/quiqqer/app/', self::class . ':help');
        $Slim->get('/quiqqer/app/structure/{project}/{lang}', self::class . ':structure');
        $Slim->get('/quiqqer/app/content/{project}/{lang}/{id}', self::class . ':content');
    }

    /**
     * Outputs some information about the QUIQER APP
     *
     * @param RequestInterface $Request
     * @param ResponseInterface $Response
     * @param $args
     * @return mixed
     */
    public function help(
        RequestInterface $Request,
        ResponseInterface $Response,
        $args
    ) {
        return $Response->withStatus(200)
            ->withHeader('Content-Type', 'application/json')
            ->write('QUIQQER APP Info');
    }

    /**
     * Returns the app structure
     *
     * @param RequestInterface $Request
     * @param ResponseInterface $Response
     * @param $args
     */
    public function structure(
        RequestInterface $Request,
        ResponseInterface $Response,
        $args
    ) {
        $Project   = QUI::getProject($args['project'], $args['lang']);
        $host      = $Project->getVHost(true, true);
        $cacheName = 'quiqqer/app/settings/' . $Project->getName();

        try {
            return $Response->withStatus(200)
                ->withHeader('Content-Type', 'application/json')
                ->write(QUI\Cache\Manager::get($cacheName));
        } catch (\Exception $Exception) {
        }

        $Locale = new QUI\Locale();
        $Locale->setCurrent($Project->getLang());

        // Logo
        $logo = '';

        if ($Project->getConfig('quiqqerApp.settings.logo')) {
            try {
                $Image = QUI\Projects\Media\Utils::getImageByUrl(
                    $Project->getConfig('quiqqerApp.settings.logo')
                );

                $logo = $host . $Image->getSizeCacheUrl();
            } catch (QUI\Exception $Exception) {
            }
        }

        // splash
        $splash = '';

        if ($Project->getConfig('quiqqerApp.settings.splash')) {
            try {
                $Image = QUI\Projects\Media\Utils::getImageByUrl(
                    $Project->getConfig('quiqqerApp.settings.splash')
                );

                $splash = $host . $Image->getSizeCacheUrl();
            } catch (QUI\Exception $Exception) {
            }
        }

        // placeholder
        $placeholder = '';

        if ($Project->getConfig('placeholder')) {
            try {
                $Image = QUI\Projects\Media\Utils::getImageByUrl(
                    $Project->getConfig('placeholder')
                );

                $placeholder = $host . $Image->getSizeCacheUrl();
            } catch (QUI\Exception $Exception) {
            }
        }


        // Impressum
        $imprint = null;
        $sites   = $Project->getSites(array(
            'where' => array(
                'type' => 'quiqqer/sitetypes:types/legalnotes'
            ),
            'limit' => 1
        ));

        if (isset($sites[0])) {
            /* @var $Imprint QUI\Projects\Site */
            $Imprint = $sites[0];

            if ($Imprint->getAttribute('active')) {
                $imprint = $this->getSiteData($Imprint);
            }
        }

        // title
        $result = array(
            'title'         => $Locale->get('quiqqer/app', 'app.title.' . $Project->getName()),
            'description'   => $Locale->get('quiqqer/app', 'app.description.' . $Project->getName()),
            'version'       => $Project->getConfig('quiqqerApp.settings.version'),
            'author'        => array(
                'name'    => $Project->getConfig('quiqqerApp.settings.author.name'),
                'email'   => $Project->getConfig('quiqqerApp.settings.author.email'),
                'website' => $Project->getHost()
            ),
            'logo'          => $logo,
            'splash'        => $splash,
            'placeholder'   => $placeholder,
            'sideMenu'      => $this->getMenu('sideMenu', $Project),
            'bottomMenu'    => $this->getMenu('bottomMenu', $Project),
            'imprint'       => $imprint,
            'advertisment'  => !!$Project->getConfig('quiqqerApp.settings.advertisement'),
            'admobid'       => $Project->getConfig('quiqqerApp.settings.advertisement.admobid'),
            'useBottomMenu' => !!$Project->getConfig('quiqqerApp.settings.menuBottom'),
            'languages'     => $Project->getConfig('quiqqerApp.settings.availableLanguages'),
            'lastEdit'      => time(),
            'colors'        => array(
                'fontColor'           => $Project->getConfig('quiqqerApp.settings.fontColor'),
                'backgroundColor'     => $Project->getConfig('quiqqerApp.settings.backgroundColor'),
                'menuFontColor'       => $Project->getConfig('quiqqerApp.settings.menuFontColor'),
                'menuBackgroundColor' => $Project->getConfig('quiqqerApp.settings.menuBackgroundColor')
            )
        );

        QUI\Cache\Manager::set($cacheName, $result);

        return $Response->withStatus(200)
            ->withHeader('Content-Type', 'application/json')
            ->write(json_encode($result));
    }

    /**
     * Return the content of a site
     *
     * @param RequestInterface $Request
     * @param ResponseInterface $Response
     * @param $args
     *
     * @return string
     */
    public function content(
        RequestInterface $Request,
        ResponseInterface $Response,
        $args
    ) {
        $Project = QUI::getProject($args['project'], $args['lang']);
        $Site    = $Project->get($args['id']);

        return $Response->withStatus(200)
            ->write($Site->getAttribute('content'));
    }

    /**
     * Return the data for a site
     *
     * @param QUI\Projects\Site $Site
     * @return array
     */
    protected function getSiteData(QUI\Projects\Site $Site)
    {
        return array(
            'id'       => $Site->getId(),
            'title'    => $Site->getAttribute('title'),
            'name'     => $Site->getAttribute('name'),
            'url'      => $Site->getUrlRewritten(),
            'lastEdit' => $Site->getAttribute('e_date'),
            'icon'     => $Site->getAttribute('image_site'),
        );
    }


    private function getMenu($menuType, QUI\Projects\Project $Project)
    {
        $Package = QUI::getPackage('quiqqer/app');
        $Config  = $Package->getConfig();

        $staticPageIDs = $this->getStaticPageIDs($Project);

        $menu = array();
        $ids  = $Config->getValue(
            $menuType,
            $Project->getName() . '_' . $Project->getLang()
        );

        if ($ids) {
            $ids = explode(',', $ids);

            foreach ($ids as $id) {
                try {
                    $Site = $Project->get($id);

                    if ($Site->getAttribute('active')) {
                        $site = $this->getSiteData($Site);

                        $site['isStatic'] = false;
                        if (in_array($id, $staticPageIDs)) {
                            $site['isStatic'] = true;
                        }

                        $menu[] = $site;
                    }
                } catch (QUI\Exception $Exception) {
                }
            }
        }

        return $menu;
    }


    private function getStaticPageIDs(QUI\Projects\Project $Project)
    {
        $staticPageIDs = $Project->getConfig('quiqqerApp.settings.staticPages');

        if ($staticPageIDs) {
            return explode(',', $staticPageIDs);
        }

        return array();
    }
}
