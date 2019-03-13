<?php
/**
 * @Entity @Table(name="ec_shows")
 */
class Episode
{
   
    var $id;

    var $season;

    var $season_episode_number;

    var $airdate;

    var $title;

    var $overview;

    var $sent_to_calendar;

    var $program_id;

    var $program_name;

    var $is_saved_for_late=0;

    function jsonToClass($json_obj){
        //$this->airdate = date_create_from_format('y-m-d', $json_show->airdate);
        $this->airdate = $json_obj->airdate;
        if(!isset($json_obj->program_id)){
            throw new InvalidArgumentException ("missing program id");
        }
        $this->program_id = $json_obj->program_id;
        $this->title = isset($json_obj->title) ? $json_obj->title:null;
        $this->season = isset($json_obj->season) ? $json_obj->season:null;
        $this->season_episode_number = isset($json_obj->season_episode_number) ? $json_obj->season_episode_number:null;
        $this->overview = isset($json_obj->overview) ? $json_obj->overview:null;
    }

    function getAirdateString(){

        $tempAirdate = new DateTime($this->airdate);
        return $tempAirdate->format('m/d/Y');

    }

}