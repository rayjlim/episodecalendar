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
    * SqlQuery
    *
    * Config holder
    *
    * @date     2007-11-28
    * @category Personal
    * @package  Default
    * @author   Raymond Lim <rayjlim1@gmail.com>
    * @license  lilplaytime http://www.lilplaytime.com
    * @link     www.lilplaytime.com
    */ 
class SqlQuery
{
    var $txt;
    var $params = array();
    var $idx = 0;

    /**
    * Constructor
    *
    * @param String $txt zapytanie sql
    *
    * @return n/a
    */
    function __construct($txt)
    {
        $this->txt = $txt;
    }

    /**
    * Set string param
    *
    * @param String $value value set
    *
    * @return n/a    
    */
    public function setString($value)
    {
        $value = mysqli_escape_string(ConnectionFactory::getConnection(), $value);
        $this->params[$this->idx++] = "'".$value."'";
    }

    /**
    * Set string param
    *
    * @param String $value value to set
    *
    * @return n/a
    */
    public function set($value)
    {
        $value = mysqli_escape_string(ConnectionFactory::getConnection(), $value);
        $this->params[$this->idx++] = "'".$value."'";
    }

    /**
    * Metoda zamienia znaki zapytania
    * na wartosci przekazane jako parametr metody
    *
    * @param String $value wartosc do wstawienia
    *
    * @return n/a
    */
    public function setNumber($value)
    {
        if ($value===null) {
            $this->params[$this->idx++] = "null";
            return;
        }
        if (!is_numeric($value)) {
            throw new Exception($value.' is not a number');
        }
        $this->params[$this->idx++] = "'".$value."'";
    }

    /**
    * Get sql query
    *
    * @return String
    */
    public function getQuery()
    {
        if ($this->idx==0) {
            return $this->txt;
        }
        $params = explode("?", $this->txt);
        $sql = '';
        for ($i=0; $i<=$this->idx; $i++) {
            if ($i >= count($this->params)) {
                $sql .= $params[$i];
            } else {
                $sql .= $params[$i].$this->params[$i];
            }
        }
        return $sql;
    }

}