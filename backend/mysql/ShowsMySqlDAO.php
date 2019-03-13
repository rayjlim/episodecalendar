<?php

class ShowsMySqlDAO extends BaseMySqlDAO implements ShowsDAO
{

     /**
     * Get all records from table ordered by field
     *
     * @return array Shows 
     */
    public function queryAll()
    {
        $sql = <<<SQL
            SELECT s.*, p.title as program_name 
            FROM ec_shows s, ec_programs p
            WHERE s.program_id = p.id
SQL;
        $sqlQuery = new SqlQuery($sql);
        return $this->getList($sqlQuery);
    }

    /**
     * Get Domain object by primry key
     *
     * @param String $id primary key
     *
     * @return object Show
     */
    public function load($id)
    {
        
        $sql = <<<SQL
            SELECT s.*, p.title as program_name 
            FROM ec_shows s, ec_programs p
            WHERE s.id = ?
            AND s.program_id = p.id
            limit 1
SQL;

        $sqlQuery = new SqlQuery($sql);
        $sqlQuery->setNumber($id);
        return $this->getRow($sqlQuery);
    }

     /**
     * Get all records from table ordered by field
     *
     * @return array Shows 
     */
    public function findAllByProgram($program_id)
    {
        $sql = <<<SQL
            SELECT s.*, p.title as program_name 
            FROM ec_shows s, ec_programs p
            WHERE s.program_id = ?
            AND s.program_id = p.id
SQL;
        $sqlQuery = new SqlQuery($sql);
        $sqlQuery->setNumber($program_id);
        return $this->getList($sqlQuery);
    }

   /**
     * Get all records from table ordered by field
     *
     * @return array Shows
     */
    public function findAllByDatePassed($daysPast)
    {
        $sql = <<<SQL
            SELECT s.*, p.title as program_name 
            FROM ec_shows s, ec_programs p
            WHERE airdate < ?
            AND s.program_id = p.id
SQL;
        $sqlQuery = new SqlQuery($sql);
        $targetDate = strtotime($daysPast." days");

        $sqlQuery->set(date('Y-m-d', $targetDate));
        return $this->getList($sqlQuery);
    }
   /**
     * Get all records from table ordered by field
     *
     * @return array  
     */
    public function findNotSentToCalendar()
    {
        $sql = <<<SQL
            SELECT s.*, p.title as program_name 
            FROM ec_shows s, ec_programs p
            WHERE sent_to_calendar like ''
            AND s.program_id = p.id
SQL;
        $sqlQuery = new SqlQuery($sql);
        return $this->getList($sqlQuery);
    }

    /**
     * Insert record to table
     *
     * @param object $show show
    *
    * @return integer id of new show 
     */
    public function insert($show)
    {
        
        $sql = <<<SQL
            INSERT INTO ec_shows 
            (program_id, episode_index, season, season_episode_number, 
            production_code, airdate, title, is_special, sent_to_calendar) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
SQL;
        $sqlQuery = new SqlQuery($sql);
        $sqlQuery->setNumber($show->program_id);
        $sqlQuery->setNumber(isset($show->episode_index) ? $show->episode_index:0);
        $sqlQuery->setNumber(isset($show->season) ? $show->season:0);
        $sqlQuery->setNumber(isset($show->season_episode_number) ? $show->season_episode_number:0);
        $sqlQuery->set(isset($show->production_code) ? $show->production_code:0);
        $tempAirdate = new DateTime($show->airdate);
        $sqlQuery->set($tempAirdate->format('Y-m-d H:i:s'));
        $sqlQuery->set($show->title);//
        $sqlQuery->setNumber(isset($show->is_special) ? $show->is_special:0);
        $sqlQuery->set(isset($show->sent_to_calendar) ? $show->sent_to_calendar:null);

//
        $id = $this->executeInsert($sqlQuery);
        \Lpt\DevHelp::debugMsg('insert complete'.$id);
        return $id;
    }

 /**
     * Delete record from table
     *
     * @param integer $id primary key
     *
    * @return integer affected rows 
     */
    public function delete($showId)
    {
        $sql = 'DELETE FROM ec_shows WHERE id = ?';
        $sqlQuery = new SqlQuery($sql);
        $sqlQuery->setNumber($showId);
        return $this->executeUpdate($sqlQuery);
    }


 /**
     * Delete record from table
     *
     * @param integer $id primary key
     *
    * @return integer affected rows 
     */
    public function update($show)
    {
         $sql = <<<SQL
            UPDATE ec_shows 
            set is_saved_for_later = ?,
            sent_to_calendar = ?
            where id = ?
            
SQL;
        $sqlQuery = new SqlQuery($sql);
        $sqlQuery->set($show->is_saved_for_later);
        $sqlQuery->set($show->sent_to_calendar);
        $sqlQuery->setNumber($show->id);
        return $this->executeUpdate($sqlQuery);
    }

/**
    *  Create SQL for search params
    *
    * @return String
    */
    public function removeBatch($shows) 
    {
        $showlist = '-1'; //non-existent id
        foreach ($shows as $show){
            if (!$show->is_saved_for_later){
            $showlist .= ', '.$show->id;
            }
        }

        $sql = 'DELETE FROM ec_shows WHERE id in ('.$showlist.')';
        $sqlQuery = new SqlQuery($sql);
        return $this->executeUpdate($sqlQuery);
    }

    /**
      * Read row
     *
     * @param array $row table row
     *
     * @return object movie
     */
    protected function readRow($row)
    {
        $show = new Show();
        
        $show->id = $row['id'];
        $show->program_id = $row['program_id'];
        $show->episode_index = $row['episode_index'];
        $show->season = $row['season'];
        $show->season_episode_number = $row['season_episode_number'];
        $show->production_code = $row['production_code'];
        $show->airdate = ($row['airdate']);
        $show->title = $row['title'];
        $show->is_special = $row['is_special'];
        $show->sent_to_calendar = $row['sent_to_calendar'];
        $show->is_saved_for_later = $row['is_saved_for_later'];

        $show->program_name = $row['program_name'];
        return $show;
    }
}