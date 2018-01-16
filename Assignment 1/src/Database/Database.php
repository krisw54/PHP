<?php

namespace toolkit;

use \PDO;

class Database
{
    private $conn; //stores the database connection

    public function __construct($cfg)
    {
        $servername = $cfg['db']['host'];
        $username = $cfg['db']['user'];
        $password = $cfg['db']['pass'];
        $myDB = $cfg['db']['db'];

        $this->conn = new PDO("mysql:host=$servername;dbname=$myDB", $username, $password);
        $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    public function __destruct()
    {
        $conn = null;
    }

    private function prepareWhereConditions($parameters, $sql)
    {
        if (!isset($parameters['conditions'])) {
            throw new \Exception(
                'No conditions found'
            );
        }
        if (!isset($parameters['operator'])) {
            throw new \Exception(
                'No operator found'
            );
        }
        if ($parameters['operator'] !== "AND" && $parameters['operator'] !== "OR"
            && $parameters['operator'] !== "LIKE" && $parameters['operator'] !='<'
            && $parameters['operator'] !='>' && $parameters['operator'] !='<>'
            && $parameters['operator'] !='<=' && $parameters['operator'] !='>=') {
            throw new \Exception(
                'Invalid operator or not supported'
            );
        }
        if (
            count($parameters['conditions']) !=
            count(array_keys($parameters['conditions']))) {
            throw new \Exception(
                'Number of values and keys don\'t match.'
            );
        }

        $sqlQ = $sql;
        $sqlQ .= " WHERE ";

        foreach ($parameters['conditions'] as $key => $value) {
            $last = $key;
            $keys = array_keys($parameters['conditions']);
            $lastKey = $keys[count($keys) - 1];
            $operator = $parameters['operator'];
            if ($operator == "LIKE" || $operator == ">" || $operator == "<" || $operator == "<>"|| $operator == "<=" || $operator == ">=") {
                $sqlQ .= $operator = " ". key($parameters['conditions']) ." " . $parameters['operator'] ." '" . array_values($parameters['conditions'])[0]."'";
            } else {
                if ($lastKey !== $last) {
                    $sqlQ .= $key . '= :' . $key .  " $operator ";
                } else {
                    $sqlQ .= $key . '= :' . $key;
                }
            }
        }
        return $sqlQ;
    }

    public function select($parameters)
    {
        $sqlQ = "SELECT ";
        if ($parameters['fields'][0] !== "*") {
            $sqlQ .= implode(" ,", $parameters['fields']);
        } else {
            $sqlQ .= "*";
            $sqlQ .= " FROM " . $parameters['table'];
            $result = $this->conn->query($sqlQ);
            $rows = $result->fetchAll(PDO::FETCH_ASSOC);
            if ($rows) {
                return $rows;
            } else {
                return null;
            }
        }

        $sqlQ .= " FROM " . $parameters['table'];

        if (!empty($parameters['conditions'])) {

            $sql = $this->prepareWhereConditions($parameters, $sqlQ);
            $stmt = $this->conn->prepare($sql);

            foreach ($parameters['conditions'] as $key => $value) {
                $stmt->bindValue(':' . $key, $value);
            }

            $stmt->execute();
        } else {
            $stmt= $this->conn->query($sqlQ);
        }
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if ($rows) {
            return $rows;
        } else {
            return null;
        }
    }

    public function delete($parameters)
    {
        $sqlQ = "DELETE ";
        $sqlQ .= " FROM " . $parameters['table'];

        if (!empty($parameters['conditions'])) {

            $sql = $this->prepareWhereConditions($parameters, $sqlQ);
            $stmt = $this->conn->prepare($sql);

            foreach ($parameters['conditions'] as $key => $value) {
                $stmt->bindValue(':' . $key, $value);
            }

            $stmt->execute();

            $count = $stmt->rowCount();
            return $count;
        }
    }

    public function update($parameters)
    {
        $sqlQ = "UPDATE ";
        $sqlQ .= $parameters['table'];
        $sqlQ .= " SET ";
        $count = 0;

        foreach ($parameters['fields'] as $key => $value) {
            $count++;
            $last = $value;
            $keys = array_values($parameters['fields']);
            $lastKey = $keys[count($keys) - 1];

            if ($lastKey !== $last) {
                $sqlQ .= $value . '= :' . "f" . $count . ", ";
            } else {
                $sqlQ .= $value . '= :' . "f" . $count;
            }
        }

        if (!empty($parameters['conditions'])) {

            $sql = $this->prepareWhereConditions($parameters, $sqlQ);

            $stmt = $this->conn->prepare($sql);
            $count = 0;

            foreach ($parameters['updates'] as $key => $value) {
                $count++;
                $stmt->bindValue(':f' . $count, $value);
            }

            foreach ($parameters['conditions'] as $key => $value) {
                $stmt->bindValue(':' . $key, $value);
            }

            $stmt->execute();

            $count = $stmt->rowCount();
            return $count;
        } else {
            return null;
        }
        return $count;
    }

    public function insert($parameters)
    {
        $fields=0;
        $values=0;
        foreach ($parameters['values'] as $key => $value) {
            $values++;
        }
        foreach ($parameters['fields'] as $key => $value) {
            $fields++;
        }
        if ($values === $fields) {
            $sqlQ = "INSERT INTO ";
            $sqlQ .= $parameters['table'];
            $sqlQ .= " (";
            $sqlQ .= implode(" ,", $parameters['fields']);
            $sqlQ .= ")";
            $sqlQ .= " VALUES ";
            $sqlQ .= "(";
            $sql = "";
            foreach ($parameters['values'] as $key => $value) {
                $sql .= "?,";
            }
            $sqlQ .= rtrim($sql, ",");
            $sqlQ .= ")";

            $stmt = $this->conn->prepare($sqlQ);
            foreach ($parameters['values'] as $key => $value) {
                $stmt->bindValue($key + 1, $value);
            }

            $stmt->execute();

            $count = $stmt->rowCount();

            return $count;
        } else {
            throw new \Exception(
                'Number of fields and values don\'t match or table name invalid.'
            );
        }
    }
}