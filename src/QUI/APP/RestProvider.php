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

        $Slim->get('quiqqer/app/', function (
            RequestInterface $Request,
            ResponseInterface $Response,
            $args
        ) {

        });

        $Slim->get('quiqqer/app/structure/{project}/{lang}', function (
            RequestInterface $Request,
            ResponseInterface $Response,
            $args
        ) {
            $Project = QUI::getProject($args['project'], $args['lang']);

        });

        $Slim->get('quiqqer/app/content/{project}/{lang}/{id}', function (
            RequestInterface $Request,
            ResponseInterface $Response,
            $args
        ) {
            $Project = QUI::getProject($args['project'], $args['lang']);

        });
    }
}
