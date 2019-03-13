<?php

// load required files
require 'vendor/autoload.php';
require '_configs/app_config.php';

// register Slim auto-loader
\Slim\Slim::registerAutoloader();
use RedBean_Facade as R;
define( 'PROGRAMS', 'ec_programs' );
define( 'SHOWS', 'ec_shows' );

// set up database connection
R::setup('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME, DB_USER, DB_PASSWORD);
R::freeze(true);
R::ext('xdispense', function( $type ){ 
        return R::getRedBean()->dispense( $type ); 
    });

// R::debug(true);
// initialize app
$app = new \Slim\Slim();

$app->group('/programs', function () use ($app) {
    $app->get('/', function () use ($app) {
        $get_data = $app->request->get();
        $programs = [];
        
        $limit = 10;
        $programs = R::findAll(PROGRAMS);
        $app->response()->header('Content-Type', 'application/json');
        
        $sequencedArray = array_values(array_map("getExportValues", $programs));
        echo json_encode($sequencedArray);

    });
    $app->get('/:id', function ($id) use ($app) {
        $program = R::load(PROGRAMS, $id);
        $app->response()->header('Content-Type', 'application/json');
        
        echo json_encode($program->export());
    });
    $app->get('/:id/shows/', function ($id) use ($app) {
        $program = R::load(PROGRAMS, $id);
        $app->response()->header('Content-Type', 'application/json');
        echo '{"program":';
        echo json_encode($program->export());
        echo ', "episodes":
        ';
        $shows = R::findAll(SHOWS, ' program_id = '.$id);
       $sequencedArray = array_values(array_map("getExportValues", $shows));
        echo json_encode($sequencedArray);
        echo '}';
    });
    $app->post('/', function () use ($app) {
        http_response_code(201);
        $programBean = R::xdispense(PROGRAMS);
        $request = $app->request();
        
        $program = json_decode($request->getBody());
        $programBean->user_id = 0;
        $programBean->content = $program->content;
        $programBean->date = $program->date;
        $programBean->id = R::store($programBean);


        $app->response()->header('Content-Type', 'application/json');
        
        echo json_encode($programBean->export());

        // {"content":"blah","date":"2015-03-10"}
    });
    $app->put('/:id', function ($id) use ($app) {
        
        $movieBean = R::load(PROGRAMS, $id);
        $request = $app->request();
        
        $program = json_decode($request->getBody());
        $programBean->title = $program->title;
        $programBean->dt_viewed = $program->dt_viewed;
        $programBean->comments = $program->comments;
        R::store($movieBean);
        $app->response()->header('Content-Type', 'application/json');
        
        echo json_encode($movieBean->export());
    });
    
    $app->delete('/:id', function ($id) use ($app) {
        $program = R::load(PROGRAMS, $id);
        R::trash($program);
        $app->response()->header('Content-Type', 'application/json');
        echo "{}";
    });
});

$app->group('/programs', function () use ($app) {
    $app->get('/', function () use ($app) {
        $get_data = $app->request->get();
        $programs = [];
        
        $limit = 10;
        $programs = R::findAll(PROGRAMS);
        $app->response()->header('Content-Type', 'application/json');
        
        $sequencedArray = array_values(array_map("getExportValues", $programs));
        echo json_encode($sequencedArray);

    });
    $app->get('/:id', function ($id) use ($app) {
        $program = R::load(PROGRAMS, $id);
        $app->response()->header('Content-Type', 'application/json');
        
        echo json_encode($program->export());
    });
    $app->post('/', function () use ($app) {
        http_response_code(201);
        $programBean = R::xdispense(PROGRAMS);
        $request = $app->request();
        
        $program = json_decode($request->getBody());
        $programBean->user_id = 0;
        $programBean->content = $program->content;
        $programBean->date = $program->date;
        $programBean->id = R::store($programBean);


        $app->response()->header('Content-Type', 'application/json');
        
        echo json_encode($programBean->export());

        // {"content":"blah","date":"2015-03-10"}
    });
    $app->put('/:id', function ($id) use ($app) {
        
        $movieBean = R::load(PROGRAMS, $id);
        $request = $app->request();
        
        $program = json_decode($request->getBody());
        $programBean->title = $program->title;
        $programBean->dt_viewed = $program->dt_viewed;
        $programBean->comments = $program->comments;
        R::store($movieBean);
        $app->response()->header('Content-Type', 'application/json');
        
        echo json_encode($movieBean->export());
    });
    
    $app->delete('/:id', function ($id) use ($app) {
        $program = R::load(PROGRAMS, $id);
        R::trash($program);
        $app->response()->header('Content-Type', 'application/json');
        echo "{}";
    });
});

// run
$app->run();

function getExportValues($item){
    return $item->export();     
}