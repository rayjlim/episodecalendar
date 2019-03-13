<?php

class DevHelp
{
    /**
    *  debugger_msg
    *  
    * @param string $msg message output
    *
    * @return None
    */
    static function debugMsg($msg)
    {
        $sessionDebug = isset($_SESSION['debug']);
        if ((isset($_SESSION['debug']) && $_SESSION['debug']) && !$sessionDebug) {
            echo $msg.'<br>'; 
        }
    }

    static function debugAndLogMsg($msg)
    {
        if ((isset($_SESSION['debug']) &&  $_SESSION['debug']) && !$_SESSION['xhr']) {
            echo $msg.'<br>'; 
        }
        Logger::log($msg);
    }

    /**
    *  redirectHelper
    *
    * @param string $url target location
    *
    * @return None
    */
    static function redirectHelper($url)
    {
        if (isset($_SESSION['debug']) && $_SESSION['debug']) {
            echo '<a href="'.$url.'">Follow Redirect '.$url.'</a>';
        } else {
            header("Location: $url");
        }
        exit;
    }
}

if (isset($_REQUEST['debug'])) {
    $_SESSION['debug'] = $_REQUEST['debug'] == 'on' 
        ? true 
        : false;
}