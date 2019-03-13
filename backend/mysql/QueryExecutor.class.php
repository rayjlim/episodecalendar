<?php
/**
    * ConnectionProperty.class.php
    *
    * PHP Version 5.4
    *
    * @date     2007-11-28
    * @category Personal
    * @package  Default
    * @author   Raymond Lim <rayjlim1@gmail.com>
    * @license  lilplaytime http://www.lilplaytime.com
    * @link     www.lilplaytime.com
    * 
    */
/**
    * QueryExecutor
    *
    * Engine for the queries
    *
    * @date     2007-11-28
    * @category Personal
    * @package  Default
    * @author   Raymond Lim <rayjlim1@gmail.com>
    * @license  lilplaytime http://www.lilplaytime.com
    * @link     www.lilplaytime.com
    */
class QueryExecutor
{

    /**
    *  execute
    *
    * @param string $sqlQuery SQL
    *
    * @return array rows returned
    */
    public static function execute($sqlQuery)
    {
        $query = $sqlQuery->getQuery();
        \Lpt\DevHelp::debugMsg($query);

        $mysqli = ConnectionFactory::getConnection();	
        $result = $mysqli->query($query);

        if (!$result) {
            echo $query;
            throw new Exception($mysqli->error);
        }
        $index=0;
        $tab = array();
        while ($row = $result->fetch_array()) {
            $tab[$index++] = $row;
        }
        $result->close();
        //$mysqli->close();
        return $tab;
    }
    /**
    *  executeUpdate
    *
    * @param string $sqlQuery SQL
    *
    * @return array rows returned
    */
    public static function executeUpdate($sqlQuery)
    {
        $query = $sqlQuery->getQuery();
        \Lpt\DevHelp::debugMsg($query);

        $mysqli = ConnectionFactory::getConnection();	
        $result = $mysqli->query($query);
        if (!$result) {
            echo $query;
            throw new Exception($mysqli->error);
        }

        $returnValue = $mysqli->affected_rows;
        //$mysqli->close();
        return $returnValue;		
    }
    /**
    *  executeInsert
    *
    * @param string $sqlQuery SQL
    *
    * @return array rows returned
    */
    public static function executeInsert($sqlQuery)
    {
        $query = $sqlQuery->getQuery();
        \Lpt\DevHelp::debugMsg($query);

        $mysqli = ConnectionFactory::getConnection();
        $result = $mysqli->query($query);
        if (!$result) {
            echo $query;
            throw new Exception($mysqli->error);
        }
        $returnValue = $mysqli->insert_id;
        //$mysqli->close();
        return $returnValue;
    }
}
?>