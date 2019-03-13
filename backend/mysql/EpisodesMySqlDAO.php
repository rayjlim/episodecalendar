<?php

/**
    * EpisodeMySqlDAO
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
class EpisodesMySqlDAO extends BaseMySqlDAO implements EpisodesDAO
{

     /**
     * Get all records from table ordered by field
     *
     * @return array Episodes 
     */
    public function queryAll($orderby="title")
    {
        $sql = <<<SQL
            SELECT s.*, p.title as program_name 
            FROM ec_shows s, ec_programs p
            WHERE s.program_id = p.id
SQL;
        $sql .=" order by ".$orderby;

        $sqlQuery = new SqlQuery($sql);
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
     * @return array Movies 
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
     * @return array  
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
     * @param object $movie movie
    *
    * @return integer id of new movie 
     */
    public function insert($episode)
    {
        
        $sql = <<<SQL
            INSERT INTO ec_shows 
            (program_id, season, season_episode_number, 
            airdate, title, overview, sent_to_calendar) 
            VALUES (?, ?, ?, ?, ?, ?, ?)
SQL;
        $sqlQuery = new SqlQuery($sql);
        $sqlQuery->setNumber($episode->program_id);
        $sqlQuery->setNumber(isset($episode->season) ? $episode->season:0);
        $sqlQuery->setNumber(isset($episode->season_episode_number) ? $episode->season_episode_number:0);
        $tempAirdate = new DateTime($episode->airdate);
        $sqlQuery->set($tempAirdate->format('Y-m-d H:i:s'));
        $sqlQuery->set($episode->title);//
        $sqlQuery->set(isset($episode->overview) ? $episode->overview:'');
        $sqlQuery->set(isset($episode->sent_to_calendar) ? $episode->sent_to_calendar:null);

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

    public function delete($id)
    {
        $sql = 'DELETE FROM ec_shows WHERE id = ?';
        $sqlQuery = new SqlQuery($sql);
        $sqlQuery->setNumber($id);
        return $this->executeUpdate($sqlQuery);
    }


 /**
     * Delete record from table
     *
     * @param integer $id primary key
     *
    * @return integer affected rows 
     */
    public function update($episode)
    {
         $sql = <<<SQL
            UPDATE ec_shows 
            set is_saved_for_later = ?,
            sent_to_calendar = ?
            where id = ?      
SQL;
        $sqlQuery = new SqlQuery($sql);
        $sqlQuery->set($episode->is_saved_for_later);
        $sqlQuery->set($episode->sent_to_calendar);
        $sqlQuery->setNumber($episode->id);
        return $this->executeUpdate($sqlQuery);
    }

    public function removeBatch($episodes) 
    {
        $episodelist = '-1'; //non-existent id
        foreach ($episodes as $episode){
            if (!$episode->is_saved_for_later){
            $episodelist .= ', '.$episode->id;
            }
        }

        $sql = 'DELETE FROM ec_shows WHERE id in ('.$episodelist.')';
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
        $episode = new Episode();
        
        $episode->id = $row['id'];
        $episode->program_id = $row['program_id'];
        $episode->season = $row['season'];
        $episode->season_episode_number = $row['season_episode_number'];
        $episode->airdate = ($row['airdate']);
        $episode->title = $row['title'];
        $episode->overview = $row['overview'];
        $episode->sent_to_calendar = $row['sent_to_calendar'];
        $episode->is_saved_for_later = $row['is_saved_for_later'];

        $episode->program_name = $row['program_name'];
        return $episode;
    }
}