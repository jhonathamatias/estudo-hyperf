<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */
use Hyperf\HttpServer\Router\Router;

Router::addRoute(['GET', 'POST', 'HEAD'], '/', 'App\Controller\IndexController@index');

Router::get('/favicon.ico', function () {
    return '';
});

// Rotas de demonstração sobre poluição de estado em corrotinas
Router::get('/demo/state-polluted', \App\Controller\CoroutineDemoController::class . '@testStatePolluted');
Router::get('/demo/state-unpolluted', \App\Controller\CoroutineDemoController::class . '@testStateUnpolluted');
Router::get('/demo/state-unpolluted-email', \App\Controller\CoroutineDemoController::class . '@testStateUnpollutedEmail');


