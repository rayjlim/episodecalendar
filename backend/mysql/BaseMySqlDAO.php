<?php

class BaseMySqlDAO
{
    /**
    *  Find Search Params from REQUEST
    *
    * @param string $sqlQuery SQL query
    *
    * @return array multi dimensional array of the rows
    */
    protected function getList($sqlQuery)
    {
        $tab = QueryExecutor::execute($sqlQuery);
        $ret = array();
        for ($i=0; $i < count($tab); $i++) {
            $ret[$i] = $this->readRow($tab[$i]);
        }
        return $ret;
    }

    /**
     * Get row
    *
    * @param string $sqlQuery SQL query
    *
    * @return array rows
     */
    protected function getRow($sqlQuery)
    {
        $tab = QueryExecutor::execute($sqlQuery);
        if (count($tab)==0) {
            return null;
        }
        return $this->readRow($tab[0]);		
    }

    /**
     * Execute sql query
    *
    * @param string $sqlQuery SQL query
    *
    * @return array rows
     */
    protected function execute($sqlQuery)
    {
        return QueryExecutor::execute($sqlQuery);
    }

        
    /**
     * Execute sql query
    *
    * @param string $sqlQuery SQL query
    *
    * @return array rows
     */
    protected function executeUpdate($sqlQuery)
    {
        return QueryExecutor::executeUpdate($sqlQuery);
    }

    /**
     * Query for one row and one column
    *
    * @param string $sqlQuery SQL query
    *
    * @return array rows
     */
    protected function querySingleResult($sqlQuery)
    {
        return QueryExecutor::queryForString($sqlQuery);
    }

    /**
     * Insert row to table
    *
    * @param string $sqlQuery SQL query
    *
    * @return integer new id
     */
    protected function executeInsert($sqlQuery)
    {
        return QueryExecutor::executeInsert($sqlQuery);
    }
}