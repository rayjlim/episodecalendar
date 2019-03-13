<?php

session_start();

require 'backend/core/_app-constants.php';
require 'backend/core/__deploy-config.php';
require 'backend/core/DevHelp.php';
require 'backend/core/Logger.php';
require 'vendor/autoload.php';

use Facebook\FacebookSession;
use Facebook\FacebookRedirectLoginHelper;
use Facebook\FacebookRequest;
use Facebook\GraphUser;
use Facebook\FacebookRequestException;
use \Lpt\DevHelp;

$app = new \Slim\App;
// $app->add(function (ServerRequestInterface $request, ResponseInterface $response, callable $next) {
//     // Use the PSR 7 $response object

//     return $next($request, $response);
// });
// Get container
$container = $app->getContainer();

// Register component on container
$container['view'] = function ($container) {
    $view = new \Slim\Views\Twig('templates', [
        'cache' => realpath('../templates/cache')
    ]);

    // Instantiate and add Slim specific extension
    $router = $container->get('router');
    $uri = \Slim\Http\Uri::createFromEnvironment(new \Slim\Http\Environment($_SERVER));
    $view->addExtension(new Slim\Views\TwigExtension($router, $uri));

    return $view;
};

// END TEMPLATING 

// Create monolog logger and store logger in container as singleton 
// (Singleton resources retrieve the same log resource definition each time)

//PAGE MESSAGE LOGIC
if (isset($_SESSION['page_message']) ) {
    $container['view']->getEnvironment()->addGlobal ('page_message', $_SESSION['page_message']);
    $_SESSION['page_message'] = null;

}
$container['view']->getEnvironment()->addGlobal (    'rooturl', rooturl);
$container['view']->getEnvironment()->addGlobal (    'baseurl', baseurl);
$container['view']->getEnvironment()->addGlobal (    'torrent_word', 'torrent');

//SETTING THE XHR SESSION SO IT CAN BE PICKED UP BY THE DEVHELP LOGGER
// $_SESSION['xhr'] = $app->request()->isAjax();

// ROUTING
$programHandler = new ProgramHandler();
$episodeHandler = new EpisodeHandler();
$batchHandler = new BatchHandler();
$userProgramHandler = new UserProgramHandler();

$app->get('/', function ($request, $response, $args) { return $response->withRedirect('programs/'); });

$app->get('/programs/', $programHandler->main());
$app->get('/programs/{programId}/episodes/', $programHandler->getProgramEpisodes());
$app->get('/episodes/',                  $episodeHandler->main());

$logHandler = new LogHandler();
$app->get('/logs/',                $logHandler->getUrlHandler());
$app->get('/logs/{logfileName}',    $logHandler->getUrlHandlerWithParam());
$app->delete('/logs/{logfileName}', $logHandler->delete());


$app->get('/ping', function(){
    echo 'ping';
});









$app->get('/myprograms/', $userProgramHandler->getUrlHandler());
$app->post('/myprograms/', $userProgramHandler->add());
$app->get('/myprograms/:programId/shows/', $userProgramHandler->getProgramShows());
$app->get('/myshows/', $userProgramHandler->getAllMyShows());



$app->get('/programs/:programId/define/', $programHandler->searchEpguide()); //test covered

$app->get('/purgepassedepisodes',     $batchHandler->purgePassed(false));          //cover in functional
$app->get('/checkallnewepisodes',     $batchHandler->checkExternalAll(false));		//cover in functional
$app->get('/sendtocalendar',          $batchHandler->sendUnsentToCalendar(false));	//cover in functional


$app->post('/episodes/',                 $episodeHandler->add(false));
$app->get('/episodes/:id/toggleIsSaved', $episodeHandler->toggleIsSaved(false));
$app->get('/episodes/:showId/sendtocalendar', $episodeHandler->sendToCalendar(false));

$app->get('/torrents/',          $batchHandler->getTorrents(false));

// ---------- APIs ----------------
$app->get('/api/programs/',       $programHandler->getAll());
$app->get('/api/programs/:id',    $programHandler->get());
$app->post('/api/programs/',      $programHandler->add());
$app->put('/api/programs/:id',    $programHandler->update());
$app->delete('/api/programs/:id', $programHandler->delete());

$app->get('/api/programs/:programId/checknewepisodes', $programHandler->checkEpguide());
$app->get('/api/programs/:programId/clearparse',       $programHandler->clearparse());
$app->post('/api/programs/:programId/epguidedefintion', $programHandler->epguidedefintion($app));
$app->get('/api/programs/checknewufc/', $programHandler->checkWiki());

$app->get('/api/episodes/',       $episodeHandler->getAll());
$app->get('/api/episodes/:id',    $episodeHandler->get());
$app->post('/api/episodes/',      $episodeHandler->add($app));
$app->put('/api/episodes/:id',    $episodeHandler->update());
$app->delete('/api/episodes/:id', $episodeHandler->delete());
$app->get('/api/episodes/:episodeId/sendtocalendar', $episodeHandler->sendToCalendar());
$app->get('/api/episodes/:episodeId/toggleIsSaved',  $episodeHandler->toggleIsSaved());

$app->get('/api/purgepassedepisodes',     $batchHandler->purgePassed());
$app->get('/api/checkallnewepisodes',     $batchHandler->checkExternalAll());
$app->get('/api/sendtocalendar',          $batchHandler->sendUnsentToCalendar());

$app->delete('/api/myshows/:id', $userProgramHandler->deleteShow());

// ---------- end APIs ----------------

// END ROUTING
$app->run();
