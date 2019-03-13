<?php

interface EpisodesDAO
{

    /**
     * Get Domain object by primry key
     *
     * @param String $id primary key
     *
     * @return Episodes 
     */
    public function load($id);
    public function insert($episode);

}