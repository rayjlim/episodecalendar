<?php

use \Lpt\DevHelp;
use \Slim\http\Request as Request;
use \Slim\http\Response as Response;

class ProgramHandler extends SortPageHandler
{

    function main() 
    {   
        return function($request, $response)
        {
            DevHelp::debugMsg('start program list');
        
            $iDao = DAOFactory::getProgramsDAO();
            $programs = $iDao->queryAll();
            return $this->view->render($response, 'programs.twig', [
                'programs'  => $programs
            ]);
        };
    }

    function getProgramEpisodes() 
    {
        return function($request, $response, $args)
        {
            $requestParams = $request->getQueryParams();
            $programId =  $args['programId'];
            DevHelp::debugMsg('start /programs/'.$programId.'/shows/ ');
            
            $program = DAOFactory::getProgramsDAO()->load($programId);
            $episodes = DAOFactory::getEpisodesDAO()->findAllByProgram($programId);

            $showFullEpisodeList = isset($requestParams['full']) && $requestParams['full'] == 'true';
            $iResourceDao = DAOFactory::getResourceDAO();
            
            $externalEpisodes = $iResourceDao->getEpisodes ($program, $showFullEpisodeList);
            return $this->view->render($response, 'program_episode_list.twig', [
                'program'    => $program,
                'shows'      => $episodes,
                'epguideShows' => $externalEpisodes,
                'isEpguideShowsNotEmpty' => empty($epguideShows),
                'page_title' => $program->title
            ]);

        };
    }









    public function getItems($sort, $results, $page, $me) 
    {
        DevHelp::debugMsg('start program list');
        
        $iDao = DAOFactory::getProgramsDAO();
        $programs = $iDao->queryAll();
        
        $app = \Slim\Slim::getInstance();
        $app->render('programs.twig', array(
            'programs'  => $programs
        ));
    }

    public function getAll() 
    {   
        return function()
        {
            $app = \Slim\Slim::getInstance();
            // \Slim\Slim::getInstance()->log->info("start program list"); 
            DevHelp::debugMsg('start program all');

            $iDao = DAOFactory::getProgramsDAO();
            $programs = $iDao->queryAll();
            echo '{"programs": ' . json_encode($programs) . '}';
        };
    }

    public function get() 
    {   
        return function($id)
        {
            DevHelp::debugMsg('start /api/programs/ ');
            $iDao = DAOFactory::getProgramsDAO();

            $program = $iDao->load($id);
            echo '{"program": ' . json_encode($program) . '}';
        };
    }

    

    public function add() 
    {
        $me = $this;
        return function () use ($me)
        {
            DevHelp::debugMsg('start add');

            $app = \Slim\Slim::getInstance();
            $program = $me->addProgramUnit($app->request(), DAOFactory::getProgramsDAO());

            echo json_encode($program);
        };
    }

    /** TODO : unit test **/
    public function addProgramUnit($request, $iDao){
        
        $program = json_decode($request->getBody());
        if (!isset($program->title)){
            throw new Exception ('Invalid Program params');
        }

        $id = $iDao->insert($program);
        $program->id = $id;
       
        return $program;
    }


    public function update() 
    {
        $me = $this;
        return function ($programId) use ($me)
        {
		$app = \Slim\Slim::getInstance();
        DevHelp::debugMsg('start update');
        $request = $app->request();
        
        $program = json_decode($request->getBody());
        $program->id = $programId;
        $iDao = DAOFactory::getProgramsDAO();
        $iDao->update( $program);
        
        echo json_encode($program);
        };
    }


    /** TODO : unit test **/
    public function updateProgramUnit($request, $iDao){
        
        $program = json_decode($request->getBody());
        $program->id = $programId;
        $iDao = DAOFactory::getProgramsDAO();
        $iDao->update( $program);
       
        return $program;
    }


    public function delete() 
    {
        return function ($programId)
        {
        DevHelp::debugMsg('start delete');
        $iDao = DAOFactory::getProgramsDAO();
        $iDao->delete($programId);
        echo '{"rows_affected": "1"}'; 
        };
    }




    public function clearparse() 
    {
        return function ($programId) 
        {
        DevHelp::debugMsg('start clearparse');

        $iDao = DAOFactory::getProgramsDAO();
        $program = $iDao->load($programId);
        $program->size_of_last_parse = 0;
        $program->date_of_last_parse = "";
        $program->date_of_last_check = "";
        $iDao->update($program);
        
        echo '{"status": "Completed"}';
        };
    }

    public function checkEpguide() 
    {
        return function ($programId) 
        {
        DevHelp::debugMsg('start checkEpguide');
        $helper = DAOFactory::BatchHelper();
       
        $helper->checkEpguidesProgram($programId);

        echo '{"status": "complete"}';
        };
    }

    public function checkWiki() 
    {
        return function () 
        {
        DevHelp::debugMsg('start checkWiki');
        $resource = DAOFactory::getResourceDAO();
        $programsDao = DAOFactory::getProgramsDAO();
        $episodeDao = DAOFactory::getEpisodesDAO();
         $parser = new WikiParser($programsDao, 
            $episodeDao,
            $resource);
        $url = $parser->generateUrl();
        //echo 'url:'.$url.'<br>';
        $content = $resource->load($url);

        $isValid = $parser->isContentValid($content);
        if ($isValid){
            //echo 'is valid<br>';
            
            $data = $parser->parse($content);
            //echo 'shows found: '. count($data).'<br>';
            $parser->saveData($data);
        }  
        echo '{"status": "complete"}';
        };
    }
    
    public function searchEpguide() 
    {
        $me = $this;
        return function($programId) use ($me)
        {
            DevHelp::debugMsg('start /programs/'.$programId.'/searchEpguide/ ');
            $pageData = $me->searchEpguideUnit($programId, 
                DAOFactory::getResourceDAO(), 
                DAOFactory::getProgramsDAO());
            
            $app = \Slim\Slim::getInstance();
            $app->render('epguide_results.twig', $pageData);

        };
    }

    /** Unit Tested **/
    public function searchEpguideUnit($programId, $iResource, $programsDAO)
    {
        $program = $programsDAO->load($programId);
        
        //url get
        $url = "http://www.google.com/search?hl=en&q=allintitle%3A&q=site%3Aepguides.com&btnG=Search&q=";
        $url .= urlencode($program->title);

        $content = $iResource->load ($url);
        libxml_use_internal_errors(true);
        $dom = new DOMDocument();
        $html = $dom->loadHTML($content);
        $dom->preserveWhiteSpace = false;
        $parentElement = $dom->getElementById('ires');
        
        $html = '';
        foreach($parentElement->childNodes as $node) {
           $html .= $dom->saveHTML($node);
        }

        return array(
         'program'    => $program,
         'content'      => $html
        );
    }

    public function epguidedefintion() 
    {   
        $me = $this;
        return function($programId) use ($me)
        {
            DevHelp::debugMsg('start /programs/'.$programId.'/epguidedefintion/ ');

            $app = \Slim\Slim::getInstance();
            $me->epguideDefinitionUnit($programId, $app->request(), DAOFactory::getResourceDAO(), DAOFactory::getProgramsDAO());
            echo 'scrape complete';
            echo '<a href="'.baseurl.'programs/">Back to Programs</a>';
        };
    }

    /** Unit Tested **/
    public function epguideDefinitionUnit($programId, $request, $iResource, $programsDAO)
    {
        //url get
        $epguide_title = $request->params('epguideTitle');

        $url = "http://epguides.com";
        $url .= '/'.$epguide_title.'/';

        $content = $iResource->load($url);
        //look for the cvs number
         libxml_use_internal_errors(true);
        $dom = new DOMDocument();
        $html = $dom->loadHTML($content);
        $dom->preserveWhiteSpace = false;
        $parentElement = $dom->getElementById('topnavbar');

        $innerHTML= ''; 
        $children = $parentElement->childNodes; 
        foreach ($children as $child) { 
            $innerHTML .= $child->ownerDocument->saveXML( $child ); 
        } 
        $program = $programsDAO->load($programId);

        //echo $innerHTML;

        preg_match(
            '/rage=([0-9]*)"/', 
            $innerHTML, $matches
        );
        
        $program->query_code = $matches[1];
        DevHelp::debugMsg('query_code:'.$program->query_code);

        $program->epguide_title = str_replace('/','',$epguide_title);
        $programsDAO->update($program);

    }
}
