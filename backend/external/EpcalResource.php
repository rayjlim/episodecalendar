<?php
use \Lpt\DevHelp;

class EpcalResource extends Resource implements IEpcalResource
{

    var $shortenMonth = array("Jan","Feb","Mar","Apr","May","Jun","Jul","Aug","Sep","Oct","Nov","Dec");
    var $expandedMonth   = array("January","February","March","April","May","June","July","August","September","October","November","December");
       
    public function sendToGcal($title, $content, $date)
    {
        $client = $_SESSION['gClient'];
        $service = new Google_Service_Calendar($client);

        $event = new Google_Service_Calendar_Event();
        $event->setSummary($title);
        $event->setLocation($content);

        $start = new Google_Service_Calendar_EventDateTime();
        $start->setDate($date);
        $event->setStart($start);
        $event->setEnd($start);
        $createdEvent = $service->events->insert(GCAL_ID, $event);

        return $createdEvent->getId();
    }


    /**
    * populateFromSite
    *
    * control logic for the populateFromImdb
    *
    * @param object $db_conn      Connection to database
    * @param array  $post         page post parameters
    * @param array  $get          page get parameters
    * @param object $iResourceDAO object for getting external content
    *
    * @return boolean update successful
    */
    function populateFromSite($epguide_title){
        //get querycode from epguide page

    }

    function getEpisodes(Program $program, $showFullList)
    {
        $content = $this->getEpisodeContent($program->query_code);
        return $this->convertContentToShows($content, $showFullList);
    }

    function getEpisodeContent($queryCode)
    {
        DevHelp::debugMsg('start getEpisodeContent');
        $epguideURL = 'http://epguides.com/common/exportToCSVmaze.asp?maze='.urlencode($queryCode);
        DevHelp::debugMsg('...epguideURL '.$epguideURL);
        $contents = $this->load($epguideURL);
        $sTarget = '<pre>';
        $start = stripos($contents, $sTarget) + strlen($sTarget);
        if ($start > strlen($contents))
        {
            return '';  // no results found
        }
        $end = stripos($contents, '</pre>', $start);
        

        $parsedString = substr($contents, $start, $end - $start);
        return $parsedString;
    }

    function convertContentToShows($content, $showFullList)
    {
        DevHelp::debugMsg('start convertContentToShows');
        $episodes = preg_split("/\\\r?\\n/", $content);
        $episodes = array_splice($episodes, 2, count($episodes) - 3);  //remove meta data rows
        //parse into array and loop over array
        
        $epguideShows = array();
        $now = $this->getDateTime();
        DevHelp::debugMsg("..parsing: ".count($episodes));
        foreach ($episodes as $episode){
            if (strlen ($episode) > MINIMUM_ROW_LENGTH){
                $parsedShow = $this->parseContents($episode);
                if ($parsedShow != null){
                    $airDateTime = new DateTime($parsedShow->airdate);
                    if ($showFullList || ( $airDateTime > $now)){
                        array_push($epguideShows, $parsedShow);
                    }
                    }
            }
        }
        DevHelp::debugMsg("..epguidesShows found: ".count($epguideShows));
        
        return $epguideShows;    
    }

/**
    * parseContents
    *
    * control logic for the parseContents
    *
    * @param object $movie    Existing Movie object
    * @param string $contents Site Contents
    *
    * @return array entities found
    */
    function parseContents($row){
        $airdateIndex = 3;
        //1,1,1,"",03/Feb/06,"Yesterday's Jam",n
        $splitValue = preg_split("/,/", $row);
        //DevHelp::debugMsg('check:'.preg_match('/\d\d\/[A-Za-z]*\/\d\d/', $splitValue[$this->airdateIndex]).';'.$splitValue[$this->airdateIndex]);
        $check = preg_match('/\d\d\ [A-Za-z]*\ \d\d/', $splitValue[$airdateIndex]);
        if ($splitValue[0] != '' && $check != 0){
            $airdate = str_replace($this->shortenMonth, $this->expandedMonth, $splitValue[$airdateIndex]);
            // $airdate = str_replace('/', ' ', $airdate);
            
            $episode = new Episode();
            $episode->season = $splitValue[1];
            $episode->season_episode_number = $splitValue[2];
            //DevHelp::debugMsg("..airdate: ". $airdate);
            $temp = DateTime::createFromFormat('d M y H:i:s', $airdate.' 00:00:00');
            $episode->airdate = $temp->format('Y-m-d');
            $episode->title = str_replace("\"", "", $splitValue[4]);
            $episode->sent_to_calendar = '';

            return $episode;

        } else{
            return null;
        }
    }

    function getTorretContent($search_string)
    {
        DevHelp::debugMsg('start getTorretContent');
        // $url = 'http://torrent'.'z.eu/feed?q='.urlencode('Tosh.0+s6e15');
        $url = 'http://torrent'.'z.eu/feed?q='.urlencode($search_string);
        DevHelp::debugMsg('..'.$url);

        $curl = curl_init();
        $userAgent = 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; .NET CLR 1.1.4322)';

        curl_setopt($curl,CURLOPT_URL,$url); //The URL to fetch. This can also be set when initializing a session with curl_init().
        curl_setopt($curl,CURLOPT_RETURNTRANSFER,TRUE); //TRUE to return the transfer as a string of the return value of curl_exec() instead of outputting it out directly.
        curl_setopt($curl,CURLOPT_CONNECTTIMEOUT,5); //The number of seconds to wait while trying to connect.  

        curl_setopt($curl, CURLOPT_USERAGENT, $userAgent); //The contents of the "User-Agent: " header to be used in a HTTP request.
        curl_setopt($curl, CURLOPT_FAILONERROR, TRUE); //To fail silently if the HTTP code returned is greater than or equal to 400.
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, TRUE); //To follow any "Location: " header that the server sends as part of the HTTP header.
        curl_setopt($curl, CURLOPT_AUTOREFERER, TRUE); //To automatically set the Referer: field in requests where it follows a Location: redirect.
        curl_setopt($curl, CURLOPT_TIMEOUT, 10); //The maximum number of seconds to allow cURL functions to execute.   

        $contents = curl_exec($curl);
        curl_close($curl);
        //echo $contents;

        $rss = simplexml_load_string($contents);
        $foundItems = array();
        if($rss)
        {
       $items = $rss->channel->item;
        
        $sizeRegex  = '/Size: (\d+)/';
        $seedsRegex = '/Seeds: (\d+)/';
        $peersRegex = '/Peers: (\d+)/';
        $hashRegex  = '/Hash: ([a-zA-Z0-9]*)/';
        foreach($items as $item)
        {
            $title = $item->title;
            $link = $item->link;
            $published_on = $item->pubDate;
            $description = $item->description;
            preg_match($sizeRegex, $description, $matches1);
            preg_match($seedsRegex, $description, $matches2);
            preg_match($peersRegex, $description, $matches3);
            
            $hash = str_replace("http://torrent"."z.eu/", "", $item->guid);
            $torrentInfo = array(
                    "title"=> $title,
                    "link" => $link,
                    "size" => $matches1[1],
                    "seeds"=> $matches2[1],
                    "peers"=> $matches3[1],
                    "hash" => $hash
            );
            
            array_push($foundItems, $torrentInfo);
        }
        }
        return $foundItems;
    }
}// end class
