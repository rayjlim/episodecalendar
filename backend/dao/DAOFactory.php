<?php

class DAOFactory
{
    
    /**
     * get the connection to the movie table
     *
     * @return MoviesDAO
     */
    public static function getProgramsDAO() {
        return new ProgramsMySqlDAO();
    }
    
    /**
     * get the connection to the movie table
     *
     * @return MoviesDAO
     */
    public static function getEpisodesDAO() {
        return new EpisodesMySqlDAO();
    }
    
    /**
     * get the connection to the movie table
     *
     * @return IMovieResourceDAO
     */
    public static function getResourceDAO() {
        return new EpcalResource();
    }
    
    public static function getUserInfoDAO() {
        return new UserInfoMySqlDAO();
    }
    public static function getUserProgramsDAO() {
        return new UserProgramsMySqlDAO();
    }
    public static function getUserShowsDAO() {
        return new UserShowsMySqlDAO();
    }
    public static function BatchHelper() {
        
        return new BatchHelper(DAOFactory::getProgramsDAO(), DAOFactory::getEpisodesDAO(), DAOFactory::getUserProgramsDAO(), DAOFactory::getUserShowsDAO(), DAOFactory::getResourceDAO());
    }
}
