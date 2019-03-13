<?php

class Program
{
    
    var $id;
    var $title;

    var $query_code;

    var $epguide_title;
    var $size_of_last_parse=0;
    var $date_of_last_parse;
    var $date_of_last_check;
    var $date_of_season_end;
        // if empty, then shows not hoarded 
        // if has value, then don't send to calendar except episodes after that date

	var $notes;
    var $episodes = null;

}