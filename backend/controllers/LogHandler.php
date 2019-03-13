<?php
use \Lpt\DevHelp;
class LogHandler 
{

    public function getUrlHandler() 
    {
        $me = $this;
        return function ($request, $response, $args) use ($me) {

            \Lpt\DevHelp::debugMsg('start logs list');
        
            $logfileName = '';
            $filelist = $me->readFilelist();
        
            if (count($filelist) > 0) {
                \Lpt\DevHelp::debugMsg('reading first file');
                $logfileName = $filelist[count($filelist)-1];
            }
            $me->readFileAndRender($logfileName, $filelist, $this, $response);
        };
    }
    
    public function getUrlHandlerWithParam() 
    {
        $me = $this;
        return function ($request, $response, $args) use ($me)
        {
            $logfileName = $args['logfileName'];
            \Lpt\DevHelp::debugMsg('start logs list with param');
            $filelist = $me->readFilelist();
            $me->readFileAndRender($logfileName, $filelist, $this, $response);

        };
    }

    public function delete() 
    {
        $me = $this;
        return function ($request, $response, $args) use ($me)
        {
            $logfileName = $args['logfileName'];
            $iResource = DAOFactory::getResourceDAO();
            $iResource->removefile(LOGS_DIR . DIR_SEP . $logfileName);

            $data['pageMessage'] = 'File Removed: ' . $logfileName;
            DevHelp::debugMsg($data['pageMessage']);
            //forward to xhr_action    
            $_SESSION['page_message'] = $data['pageMessage'];

            echo json_encode($data);
            // if ($request->isAjax() ) {
                
            // } else {
            //     DevHelp::redirectHelper($baseurl.'logs/');
            // }

        };
    }

    public function readFilelist()
    {
        $iResource = DAOFactory::getResourceDAO();
        $filelist = $iResource->readdir(LOGS_DIR);
        //NEED TO REMOVE NON EP CAL ENTRIES
        for ($i = count($filelist)-1; $i >= 0; $i--){
            if (strpos($filelist[$i], LOG_PREFIX) === FALSE){
                unset($filelist[$i]);
            }
        }
        return array_values($filelist);
    }

    public function readFileAndRender($logfileName, $filelist, $container, $response)
    {
        
        // TODO VALIDATE LOGNAME PASSED IS IN CORRECT FORMAT (PREFIX____.TXT)
        $logfile = '';
        if ($logfileName != '') {
            \Lpt\DevHelp::debugMsg('$logfileName: '. $logfileName);
            $iResource = DAOFactory::getResourceDAO();
            $logfile = $iResource->readfile(LOGS_DIR . DIR_SEP . $logfileName);
        }
    
        return $container->view->render($response, 'logs.twig', [
            'logs'  => $filelist, 
            'logfileName' => $logfileName,
            'logfile' => $logfile
        ]);
    }
}