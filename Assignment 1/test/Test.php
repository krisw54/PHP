<?php
/**
 * Created by PhpStorm.
 * User: Kris
 * Date: 09/11/2017
 * Time: 15:34
 */
use \toolkit\Database;

include "../src/cfg/cfg.php";
class Test extends PHPUnit_Framework_TestCase
{
    private $conn;
    private $database;

    protected function tearDown()
    {
        $sql = "DROP TABLE car";
        $this->conn->exec($sql);
    }

    protected function setUp()
    {
        global $cfg;

        $servername = $cfg['db']['host'];
        $username = $cfg['db']['user'];
        $password = $cfg['db']['pass'];
        $myDB = $cfg['db']['db'];

        $this->conn = new PDO("mysql:host=$servername;dbname=$myDB", $username, $password);
        $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        //Create test table
        $sql = "CREATE TABLE car ( id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY, make VARCHAR(30), model VARCHAR(30), colour VARCHAR(20), doors INT(10)) ";

        $this->conn->exec($sql);
        //Add in test data
        $sql1 = "INSERT INTO `car`(`make`, `model`, `colour`, `doors`) VALUES ('VW','Golf','Yellow',5)";
        $sql2 = "INSERT INTO `car`(`make`, `model`, `colour`, `doors`) VALUES ('VW','Polo','Blue',5);";
        $sql3 = "INSERT INTO `car`(`make`, `model`, `colour`, `doors`) VALUES ('Renault','Zoe','White',3);";
        $sql4 = "INSERT INTO `car`(`make`, `model`, `colour`, `doors`) VALUES ('Mini','Cooper','Blue',5);";

        $this->conn->exec($sql1);
        $this->conn->exec($sql2);
        $this->conn->exec($sql3);
        $this->conn->exec($sql4);

        $this->database = new Database($cfg);
    }

    /**
     * @test
     * @expectedException PDOException
     */
    public function WrongConnectionInformation() {
        $cfg['db']['host'] = 'localhost';
        $cfg['db']['db'] = 'db';
        $cfg['db']['user'] = 'username';
        $cfg['db']['pass'] = 'password';

        $db = new Database($cfg);
    }

    /**
     * @test
     */
    public function insertDataIntoDatabase() {
        $parameters = array( 'fields'=>array('make', 'model'),
                             'table'=>'car',
                             'values'=>array('TestMake','TestModel'));
        $result = $this->database-> insert($parameters);
        $this->assertEquals(1,$result);

        //Check actually added to the database
        $parameters = array( 'fields'=>array('make', 'model'),
                             'table'=>'car',
                             'operator'=>'AND',
                             'conditions'=>array('make'=>'TestMake'));
        $result = $this->database-> select($parameters);
        $expected = array(array('make'=>'TestMake','model'=>'TestModel'));
        $this->assertEquals($expected,$result);
    }

    /**
     * @test
     */
    public function deleteDataFromDatabase() {
        $parameters = array('table'=>'car',
                            'operator'=>'AND',
                            'conditions'=>array('id'=>1));
        $result = $this->database-> delete($parameters);
        $this->assertEquals(1,$result);

        //Check data has actually been deleted!
        $parameters = array( 'fields'=>array('id'),
                             'table'=>'car');
        $result = $this->database-> select($parameters);
        $expected = array(array('id'=>'2',),
                          array('id'=>'3'),
                          array('id'=>'4'));
        $this->assertEquals($expected,$result);
    }

    /**
     * @test
     */
    public function deleteDataFromDatabaseWrongTableName() {
        $parameters = array('table'=>'cars',
                            'operator'=>'AND',
                            'conditions'=>array('id'=>1));
        $result = $this->database-> delete($parameters);
        $this->assertEquals(0,$result);
    }

    /**
     * @test
     */
    public function deleteDataFromDatabaseMultiple() {
        $parameters = array('table'=>'car',
                            'operator'=>'AND',
                            'conditions'=>array('make'=>'VW'));
        $result = $this->database-> delete($parameters);
        $this->assertEquals(2,$result);
    }

    /**
     * @test
     */
    public function selectWithStar () {
        $parameters = array( 'fields'=>array('*'),
                             'table'=>'car');
        $expected = array(array('id'=>'1','make'=>'VW','model'=>'Golf','colour'=>'Yellow','doors'=>5),
                          array('id'=>'2','make'=>'VW','model'=>'Polo','colour'=>'Blue','doors'=>5),
                          array('id'=>'3','make'=>'Renault','model'=>'Zoe','colour'=>'White','doors'=>3),
                          array('id'=>'4','make'=>'Mini','model'=>'Cooper','colour'=>'Blue','doors'=>5));
        $result = $this->database-> select($parameters);
        $this->assertEquals($expected,$result);
    }

    /**
     * @test
     */
    public function selectFields () {
        $parameters = array('fields'=>array('make','model'),
                            'table'=>'car');
        $expected = array(array('make'=>'VW','model'=>'Golf'),
                          array('make'=>'VW','model'=>'Polo'),
                          array('make'=>'Renault','model'=>'Zoe'),
                          array('make'=>'Mini','model'=>'Cooper'));
        $result = $this->database-> select($parameters);
        $this->assertEquals($expected,$result);
    }

    /**
     * @test
     */
    public function selectFieldsWithWhereCondition () {
        $parameters = array('fields'=>array('make','model'),
                            'table'=>'car',
                            'operator'=>'AND',
                            'conditions'=>array('make'=>'VW','doors'=>5));
        $expected = array(array('make'=>'VW','model'=>'Golf'),
                          array('make'=>'VW','model'=>'Polo'));
        $result = $this->database-> select($parameters);
        $this->assertEquals($expected,$result);
    }

    /**
     * @test
     */
    public function updateExisitingRecordWithOneCondition() {
        $parameters = array('fields'=>array('make', 'doors'),
                            'table'=>'car',
                            'updates'=>array('newCarMake',6),
                            'operator'=>'AND',
                            'conditions'=>array('id'=>1));
        $result = $this->database-> update($parameters);
        $this->assertEquals(1,$result);
    }

    /**
     * @test
     */
    public function updateExisitingRecordWithNoCondition() {
        $parameters = array('fields'=>array('make', 'doors'),
                            'table'=>'car',
                            'updates'=>array('newCarMake',6));
        $result = $this->database-> update($parameters);
        $this->assertNull($result);
    }

    /**
     * @test
     */
    public function updateExisitingRecordWithTwoConditions() {
        $parameters = array('fields'=>array('make', 'doors'),
                            'table'=>'car',
                            'updates'=>array('newCarMake',6),
                            'operator'=>'AND',
                            'conditions'=>array('colour'=>'Blue','doors'=>5));
        $result = $this->database-> update($parameters);
        $this->assertEquals(2,$result);
    }

    /**
     * @test
     */
    public function selectFieldsWithWhereORCondition () {
        $parameters = array('fields'=>array('make','model'),
                            'table'=>'car',
                            'operator'=>'OR',
                            'conditions'=>array('make'=>'VW','doors'=>5));
        $expected = array(array('make'=>'VW','model'=>'Golf'),
                          array('make'=>'VW','model'=>'Polo'),
                          array('make'=>'Mini','model'=>'Cooper'));
        $result = $this->database-> select($parameters);
        $this->assertEquals($expected,$result);
    }

    /**
     * @test
     */
    public function selectFieldsWithWhereLikeCondition () {
        $parameters = array(
                    'fields'=>array('make','model'),
                    'table'=>'car',
                    'operator'=>'LIKE',
                    'conditions'=>array('make' => 'v%')
        );
        $expected = array(array('make'=>'VW','model'=>'Golf'),
                          array('make'=>'VW','model'=>'Polo'),
        );
        $result = $this->database-> select($parameters);
        $this->assertEquals($expected,$result);
    }

    /**
     * @test
     */
    public function selectFieldsWithWhereGreaterThanCondition () {
        $parameters = array(
            'fields'=>array('make','model'),
            'table'=>'car',
            'operator'=>'<',
            'conditions'=>array('doors' => '4')
        );
        $expected = array(array('make'=>'Renault','model'=>'Zoe'));
        $result = $this->database-> select($parameters);
        $this->assertEquals($expected,$result);
    }

    /**
     * @test
     */
    public function selectFieldsWithWhereGreaterThanEqualToCondition () {
        $parameters = array(
            'fields'=>array('make','model'),
            'table'=>'car',
            'operator'=>'>=',
            'conditions'=>array('doors' => '4')
        );
        $expected = array(array('make'=>'VW','model'=>'Golf'),
                          array('make'=>'VW','model'=>'Polo'),
                          array('make'=>'Mini','model'=>'Cooper'));
        $result = $this->database-> select($parameters);
        $this->assertEquals($expected,$result);
    }

    /**
     * @test
     */
    public function selectFieldsWithWhereLessEqualToThanCondition () {
        $parameters = array(
                    'fields'=>array('make','model'),
                    'table'=>'car',
                    'operator'=>'<=',
                    'conditions'=>array('doors' => '4')
        );
        $expected = array(array('make'=>'Renault','model'=>'Zoe'));
        $result = $this->database-> select($parameters);
        $this->assertEquals($expected,$result);
    }

    /**
     * @test
     * @expectedException Exception
     * @expectedExceptionMessage No operator found
     */
    public function selectFieldsWithWhereLikeConditionNoOperator () {
        $parameters = array(
                    'fields'=>array('make','model'),
                    'table'=>'car',
                    'conditions'=>array('make' => 'v%')
        );
        $this->database->select($parameters);
        $this->expectExceptionMessage('No operator found');
    }

    /**
     * @test
     * @expectedException Exception
     * @expectedExceptionMessage Invalid operator or not supported
     */
    public function selectFieldsWithWhereLikeConditionInvalidOperator () {
        $parameters = array(
                    'fields'=>array('make','model'),
                    'table'=>'car',
                    'operator'=>'Unsupported Operator',
                    'conditions'=>array('make' => 'v%')
        );
        $this->database->select($parameters);
        $this->expectExceptionMessage('Invalid operator or not supported');
    }

    /**
     * @test
     * @expectedException Exception
     * @expectedExceptionMessage Number of fields and values don't match
     */
    public function insertDataIntoDatabaseMissingField() {
        $parameters = array( 'fields'=>array('make'),
                             'table'=>'car',
                             'values'=>array('TestMake','TestModel'));
        $this->database-> insert($parameters);
        $this->expectExceptionMessage('Number of fields and values don\'t match');
    }

    /**
     * @test
     * @expectedException Exception
     * @expectedExceptionMessage Number of fields and values don't match or table name invalid
     */
    public function insertWithWrongInvalidTableName() {
        $parameters = array( 'fields'=>array('make'),
                             'table'=>'cars',
                             'values'=>array('TestMake','TestModel'));
        $this->database-> insert($parameters);
        $this->expectExceptionMessage('Number of fields and values don\'t match or table name invalid');
    }
}