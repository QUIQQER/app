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
        $Project = QUI::getProject($args['project'], $args['lang']);
        $host    = $Project->getVHost(true, true);

        // Logo
        $logo = '';

        if ($Project->getConfig('logo')) {
            try {
                $Image = QUI\Projects\Media\Utils::getImageByUrl(
                    $Project->getConfig('logo')
                );

                $logo = $host . $Image->getSizeCacheUrl();
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

        // placeholder
        $favicon = '';

        if ($Project->getConfig('favicon')) {
            try {
                $Image = QUI\Projects\Media\Utils::getImageByUrl(
                    $Project->getConfig('favicon')
                );

                $favicon = $host . $Image->getSizeCacheUrl();
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
                $imprint = array(
                    'id'    => $Imprint->getId(),
                    'title' => $Imprint->getAttribute('title'),
                    'name'  => $Imprint->getAttribute('name'),
                    'url'   => $Imprint->getUrlRewritten()
                );
            }
        }


        // Menu
        $menu = null;


        // title
        $result = array(
            'title'         => QUI::getLocale()->get('quiqqer/app', 'app.title.' . $Project->getName()),
            'logo'          => $logo,
            'placeholder'   => $placeholder,
            'favicon'       => $favicon,
            'menu'          => $menu,
            'imprint'       => $imprint,
            'advertisment'  => !!$Project->getConfig('quiqqerApp.settings.advertisement'),
            'useBottomMenu' => !!$Project->getConfig('quiqqerApp.settings.menuBottom'),
            'colors'        => array(
                'fontColor'           => $Project->getConfig('quiqqerApp.settings.fontColor'),
                'backgroundColor'     => $Project->getConfig('quiqqerApp.settings.backgroundColor'),
                'menuFrontColor'      => $Project->getConfig('quiqqerApp.settings.menuFrontColor'),
                'menuBackgroundColor' => $Project->getConfig('quiqqerApp.settings.menuBackgroundColor')
            ),

            // @todo muss noch gesetzt werden,
            // wann wurde diese config das letzte mal verändert
            'lastEdit'      => time()
        );

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
}
