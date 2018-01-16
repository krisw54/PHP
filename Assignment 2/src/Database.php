<?php

namespace Toolkit;

class Database {

    private $dbh;

    public function __construct($cfg)
    {
        $this->dbh = new \PDO(
            'mysql:host=' . $cfg['db']['host'] .
            ';dbname=' . $cfg['db']['db'],
            $cfg['db']['user'],
            $cfg['db']['pass']);
    }

    public function select($parameters)
    {
        $fields = array ('*');

        if (isset($parameters['fields']) && $parameters['fields'][0] != '*') {
            $fields = $this->prepareFields($parameters['fields']);
        }
        $sql = 'SELECT ' . implode(', ', $fields) .
            ' FROM `' . $parameters['table'] . '`' .
            $this->addWhere($parameters);

        $sth = $this->dbh->prepare($sql);

        if (isset($parameters['conditions'])) {
            $this->bind($sth, $this->getValues($parameters['conditions']));
        }

        $sth->execute();

        if ($sth->rowCount()) {
            return $sth->fetchAll(\PDO::FETCH_ASSOC);
        } else {
            return null;
        }
    }

    public function insert(Array $parameters)
    {
        $recordsPlaceholders = array ();
        $values = array ();
        foreach ($parameters['records'] as $record) {
            $placeholders = array ();
            for ($i = 0; $i < count($record); $i++) {
                $placeholders[] = '?';
                $values[] = $record[$i];
            }
            $recordsPlaceholders[] = implode(', ', $placeholders);
        }

        $sql = 'INSERT INTO ' . $parameters['table'] .
            ' (' . implode(', ', $this->prepareFields($parameters['fields'])) . ')' .
            ' VALUES (' . implode('), (', $recordsPlaceholders) . ')';
        $sth = $this->dbh->prepare($sql);

        $this->bind($sth, $values)->execute();
        return $sth->rowCount();
    }

    public function update(Array $parameters)
    {
        $set = [];
        $values = array ();
        foreach ($parameters['fieldValues'] as $field => $value) {
            $set[] = '`' . $field . '` = ?';
            $values[] = $value;
        }
        $sql = 'UPDATE `' . $parameters['table'] .
            '` SET ' . implode(', ', $set) .
            $this->addWhere($parameters)
        ;
        $sth = $this->dbh->prepare($sql);

        if (isset($parameters['conditions'])) {
            $values = array_merge($values, $this->getValues($parameters['conditions']));
        }
        $this->bind($sth, $values);

        $sth->execute();

        return $sth->rowCount();
    }

    public function delete(Array $parameters)
    {
        $sql = 'DELETE FROM `' . $parameters['table'] . '`' . $this->addWhere($parameters);

        $sth = $this->dbh->prepare($sql);

        if(isset($parameters['conditions'])) {
            $this->bind($sth, $this->getValues($parameters['conditions']));
        }
        $sth->execute();
        return $sth->rowCount();
    }

    private function addWhere(Array $parameters)
    {
        if (! isset($parameters['conditions'])) {
            return '';
        } else {
            $fields = array ();

            foreach ($parameters['conditions'] as $condition) {
                $fields[] = '`' . key($condition) . '` ' .
                    $condition['join'] .
                    ' ?';
            }
            return ' WHERE ' . implode(' AND ', $fields);
        }
    }

    private function prepareFields(Array $fields)
    {
        $preparedFields = array ();
        foreach ($fields as $field) {
            $preparedFields[] = '`' . $field . '`';
        }
        return $preparedFields;
    }

    private function getValues(Array $arr) {
        $values = array();
        foreach ($arr as $value) {
            $values[] = current($value);
        }
        return $values;
    }

    private function bind(\PDOStatement $sth, Array $values)
    {
        for ($i = 0; $i < count($values); $i++) {
            $sth->bindValue($i + 1, $values[$i]);
        }
        return $sth;
    }
}