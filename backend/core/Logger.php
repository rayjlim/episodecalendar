<?php

class Logger
{

    static function log($message)
    {
        if (! isset($_SESSION['logger'])) {
            $iResource = new \EpcalResource ();
            $date = $iResource->getDateTime();
            $filename = LOGS_DIR.DIR_SEP.LOG_PREFIX."-" . $date->format("Y-m").".txt";
            $fileData = $date->format("Y-m-d G:i:s") . "    " . $message."\n";
            $iResource->writeFile($filename, $fileData);
        }
    }
}
