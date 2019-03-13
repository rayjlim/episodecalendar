<?php

interface ShowsDAO
{

    /**
     * Get Domain object by primry key
     *
     * @param String $id primary key
     *
     * @return Shows 
     */
    public function load($id);

}