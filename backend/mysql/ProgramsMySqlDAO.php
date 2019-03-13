<?php
/**
    * MoviesMySqlDAO.class.php
    *
    * PHP Version 5.4
    *
    * @date     2007-11-28
    * @category Personal
    * @package  Lpt
    * @author   Raymond Lim <rayjlim1@gmail.com>
    * @license  lilplaytime http://www.lilplaytime.com
    * @link     www.lilplaytime.com
    * 
    */

/**
    * MoviesMySqlDAO
    *
    * Handle the Search params to create the sql, labels and url extensions
    *
    * @date     2007-11-28
    * @category Personal
    * @package  Lpt
    * @author   Raymond Lim <rayjlim1@gmail.com>
    * @license  lilplaytime http://www.lilplaytime.com
    * @link     www.lilplaytime.com
    */ 
class ProgramsMySqlDAO extends BaseMySqlDAO implements ProgramsDAO
{

     /**
     * Get all records from table ordered by field
     *
     * @return array Movies 
     */
    public function queryAll()
    {
        $sql = <<<SQL
            SELECT * 
            FROM ec_programs
            order by title
SQL;
        $sqlQuery = new SqlQuery($sql);
        return $this->getList($sqlQuery);
    }

    public function queryOldLastChecked($daysPast)
    {
        $sql = <<<SQL
            SELECT * 
            FROM ec_programs
            WHERE DATE(date_of_last_check) < CURDATE() - INTERVAL ? DAY 
            and query_code <> 0
SQL;
        $sqlQuery = new SqlQuery($sql);
        $sqlQuery->setNumber($daysPast);
        return $this->getList($sqlQuery);
    }

    /** 
     * Get Domain object by primry key
     *
     * @param String $id primary key
     *
     * @return object Movie
     */
    public function load($id)
    {
        $sql = 'SELECT * FROM ec_programs WHERE id = ? limit 1';
        $sqlQuery = new SqlQuery($sql);
        $sqlQuery->setNumber($id);
        return $this->getRow($sqlQuery);
    }
    /**
     * Insert record to table
     *
     * @param object $movie movie
    *
    * @return integer id of new movie 
     */
    public function insert($program)
    {
        $sql = <<<SQL
            INSERT INTO ec_programs 
            (title, query_code, epguide_title, 
            size_of_last_parse, date_of_last_parse, date_of_last_check, notes) 
            VALUES (?, ?, ?, ?, ?, ?, ?)
SQL;
        $sqlQuery = new SqlQuery($sql);
        
        $sqlQuery->set($program->title);
        $sqlQuery->set(isset($program->query_code) && $program->query_code==='' ? $program->query_code:null);        
        $sqlQuery->set(isset($program->epguide_title) && $program->epguide_title==='' ? $program->epguide_title:null);
        $sqlQuery->set(isset($program->size_of_last_parse) && $program->size_of_last_parse==='' ? $program->size_of_last_parse:null);
        $sqlQuery->set(isset($program->date_of_last_parse) && $program->date_of_last_parse===''?$program->date_of_last_parse:null);
        $sqlQuery->set(isset($program->date_of_last_check) && $program->date_of_last_check===''?$program->date_of_last_check:null);
		$sqlQuery->set(isset($program->notes) && $program->notes==='' ? $program->notes:null);        
        $id = $this->executeInsert($sqlQuery);
        \Lpt\DevHelp::debugMsg('insert complete'.$id);
        return $id;
    }
/**
     * Update record in table
     *
     * @param object $movie movie
     *
     * @return integer rows affected 
     */
    public function update($program)
    {

        $sql = <<<SQL
            UPDATE ec_programs 
            SET title = ?, 
            query_code = ?,  
            epguide_title = ?, 
			notes = ?
           
SQL;

        $includeMetaDetails = isset($program->size_of_last_parse);
        if($includeMetaDetails){
        $sql .= <<<SQL
            ,
            size_of_last_parse = ?, 
            date_of_last_parse = ?, 
            date_of_last_check = ?,
            date_of_season_end = ?
SQL;
        }

$sql .= <<<SQL
            WHERE id = ?
SQL;
        
        $sqlQuery = new SqlQuery($sql);
        
        $sqlQuery->set($program->title);
        $sqlQuery->set($program->query_code);
        $sqlQuery->set($program->epguide_title);
		$sqlQuery->set($program->notes);
        if($includeMetaDetails){
            $sqlQuery->setNumber($program->size_of_last_parse);
            $sqlQuery->set($program->date_of_last_parse);
            $sqlQuery->set($program->date_of_last_check);
            $sqlQuery->set($program->date_of_season_end);
        }
        $sqlQuery->setNumber($program->id);
        return $this->executeUpdate($sqlQuery);
    }

 /**
     * Delete record from table
     *
     * @param integer $id primary key
     *
    * @return integer affected rows 
     */
    public function delete($programId)
    {
        $sql = 'DELETE FROM ec_programs WHERE id = ?';
        $sqlQuery = new SqlQuery($sql);
        $sqlQuery->setNumber($programId);
        return $this->executeUpdate($sqlQuery);
    }


    /**
      * Read row
     *
     * @param array $row table row
     *
     * @return object program
     */
    protected function readRow($row)
    {
        $program = new Program();
        
        $program->id = $row['id'];
        $program->title = $row['title'];
        $program->query_code = $row['query_code'];
        $program->epguide_title = $row['epguide_title'];
        $program->size_of_last_parse = $row['size_of_last_parse'];
        $program->date_of_last_parse = $row['date_of_last_parse'];
        $program->date_of_last_check = $row['date_of_last_check'];
        $program->date_of_season_end = $row['date_of_season_end'];
		$program->notes = $row['notes'];

        return $program;
    }

 /**
    *  Create SQL for search params
    *
    * @return String
    */
    private function _generateSearchSQL($searchParam) 
    {
        $sqlQuery = 'where 1=1 ';
        if ( $searchParam->title !='') {
            $sqlQuery .= 'and title like \'%'.$searchParam->title.'%\' ';
        }   
        if ($searchParam->rating !='') {
            $sqlQuery .= 'and rating  = \''.$searchParam->rating.'\' ';
        }
        if ($searchParam->dt_released !='') {
            $sqlQuery .= 'and dt_released_full >= \''
            .$searchParam->dt_released.'-1-1\' and dt_released_full <= \''
            .$searchParam->dt_released.'-12-31\' ';
        }
        if ($searchParam->imdb_genre !='') {
            $sqlQuery .= 'and imdb_genre like \'%'.$searchParam->imdb_genre.'%\' ';
        }
        if ($searchParam->dt_viewed !='') {
            $sqlQuery .= 'and dt_viewed >= \''
            .$searchParam->dt_viewed.'-1-1\' and dt_viewed <= \''
            .$searchParam->dt_viewed.'-12-31\' ';
        } 
        if ($searchParam->disk !='') {
            if ($searchParam->disk == '*') {
                $sqlQuery .= 'and disk <> \'\'';
            } else {
                $sqlQuery .= 'and disk like \'%'.$searchParam->disk.'%\' ';
            }
        }

        if (!empty($searchParam->storyline)) {
            $sqlQuery .= 'and storyline like \'%'.$searchParam->storyline.'%\' ';
        }
        if (!empty($searchParam->comment)) {
            $sqlQuery .= 'and comments like \'%'.$searchParam->comment.'%\' ';
        }
        if ($searchParam->useIsViewed) {
            $sqlQuery .= ($searchParam->isViewed) 
            ? 'and dt_viewed != \'0000-00-00\'' 
            : 'and dt_viewed = \'0000-00-00\'';
        }
  
        return $sqlQuery . $this->_generateOrderBy($searchParam);
    }

       /**
    * generate SQL order by
    *
    * @return string order by sql
    */
    private function _generateOrderBy($searchParam)
    {
        $orderBy = ' ORDER BY ';
        if (!$searchParam->isViewed) {
            $orderBy .= 'comments desc, dt_released_full, title';
        } else {
            if ($searchParam->isSearchOperation) {
                if ($searchParam->disk != '') {
                    $orderBy .='disk, title';
                } else {
                    $orderBy .='title';
                }
            } else {
                $orderBy .= 'dt_viewed desc';
            }
        }
        return $orderBy;
    }
}
