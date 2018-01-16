<?php
/**
 * Created by PhpStorm.
 * User: pcw1
 * Date: 01/11/2017
 * Time: 13:06
 */

use \Toolkit\Database;

class DatabaseTest extends PHPUnit_Framework_TestCase {

    private $db;
    private $dbh;

    protected function setUp()
    {
        $cfg['db']['host'] = 'localhost';
        $cfg['db']['db'] = 'test';
        $cfg['db']['user'] = 'test';
        $cfg['db']['pass'] = 'test';

        $this->dbh = new PDO(
            'mysql:host=' . $cfg['db']['host'] .
            ';dbname=' . $cfg['db']['db'],
            $cfg['db']['user'],
            $cfg['db']['pass']
        );

        $sqlTable = <<<CREATETABLE
            CREATE TABLE IF NOT EXISTS `testtable` (
              `Id` int(4) NOT NULL AUTO_INCREMENT,
              `FirstName` varchar(50) NOT NULL,
              `Surname` varchar(50) NOT NULL,
              PRIMARY KEY (`Id`)
            ) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=3 ;
CREATETABLE;

        $sqlInsert = <<<CREATEQUERY
            INSERT INTO `testtable` (`Id`, `FirstName`, `Surname`) VALUES
            (1, 'Philip', 'Windridge'),
            (2, 'Robin', 'Oldham');
CREATEQUERY;

        $this->dbh->query($sqlTable);
        $this->dbh->query($sqlInsert);

        $this->db = new Database($cfg);
    }

    public function tearDown()
    {
        $this->dbh->query('DROP TABLE `testtable`');
    }

    /**
     * @test/
     */
    public function QueryReturnsNoRecords()
    {
        $parameters = array (
            'fields' => array ('*'),
            'table' => 'No table'
        );
        $this->assertNull($this->db->select($parameters));
    }

    /**
     * @test
     * @expectedException PDOException
     */
    public function BadConnectionInformation()
    {
        $cfg['db']['host'] = 'Badlocalhost';
        $cfg['db']['db'] = 'SomeDatabase';
        $cfg['db']['user'] = 'SomeUser';
        $cfg['db']['pass'] = 'SomePassword';

        $db = new Database($cfg);
    }

    /**
     * @test
     */
    public function SimpleSelect()
    {
        $parameters = array (
            'fields' => array ('*'),
            'table' => 'testtable'
        );
        $expected = array (
            array ('Id' => 1, 'FirstName' => 'Philip', 'Surname' => 'Windridge'),
            array ('Id' => 2, 'FirstName' => 'Robin', 'Surname' => 'Oldham')
        );
        $this->assertEquals($expected, $this->db->select($parameters));
    }

    /**
     * @test
     */
    public function SelectWithTwoFields()
    {
        $parameters = array (
            'fields' => array ('FirstName', 'Surname'),
            'table' => 'testtable'
        );
        $expected = array (
            array ('FirstName' => 'Philip', 'Surname' => 'Windridge'),
            array ('FirstName' => 'Robin', 'Surname' => 'Oldham')
        );
        $this->assertEquals($expected, $this->db->select($parameters));
    }

    /**
     * @test
     */
    public function SelectWithSimpleCondition()
    {
        $parameters = array (
            'fields' => array ('FirstName', 'Surname'),
            'table' => 'testtable',
            'conditions' => array (
                array ('FirstName' => 'Robin', 'join' => '=')
            )
        );
        $expected = array (
            array ('FirstName' => 'Robin', 'Surname' => 'Oldham')
        );
        $this->assertEquals($expected, $this->db->select($parameters));
    }

    /**
     * @test
     */
    public function SelectWithSimpleOtherThanEqualsCondition()
    {
        $parameters = array (
            'fields' => array ('FirstName', 'Surname'),
            'table' => 'testtable',
            'conditions' => array (
                array ('Id' => '2', 'join' => '<=')
            )
        );
        $expected = array (
            array ('FirstName' => 'Philip', 'Surname' => 'Windridge'),
            array ('FirstName' => 'Robin', 'Surname' => 'Oldham')
        );
        $this->assertEquals($expected, $this->db->select($parameters));
    }

    /**
     * @test
     */
    public function SelectWithLikeCondition()
    {
        $parameters = array (
            'fields' => array ('FirstName', 'Surname'),
            'table' => 'testtable',
            'conditions' => array (
                array ('FirstName' => '%obi%', 'join' => 'LIKE')
            )
        );
        $expected = array (
            array ('FirstName' => 'Robin', 'Surname' => 'Oldham')
        );
        $this->assertEquals($expected, $this->db->select($parameters));
    }

    /**
     * @test
     */
    public function SelectWithTwoConditions()
    {
        $parameters = array (
            'fields' => array ('FirstName', 'Surname'),
            'table' => 'testtable',
            'conditions' => array (
                array ('FirstName' => 'Philip', 'join' => '='),
                array ('Surname' => 'Windridge', 'join' => '=')
            )
        );
        $expected = array (
            array ('FirstName' => 'Philip', 'Surname' => 'Windridge')
        );
        $this->assertEquals($expected, $this->db->select($parameters));
    }

    /**
     * @test
     */
    public function InsertOneRecord()
    {
        $parameters = array (
            'fields' => array ('FirstName', 'Surname'),
            'table' => 'testtable',
            'records' => array (
                array ('Fiona', 'Knight')
            )
        );
        $this->assertEquals(1, $this->db->insert($parameters));
    }

    /**
     * @test
     */
    public function InsertTwoRecords()
    {
        $parameters = array (
            'fields' => array ('FirstName', 'Surname'),
            'table' => 'testtable',
            'records' => array (
                array ('Fiona', 'Knight'),
                array ('Jan', 'Lawton')
            )
        );
        $this->assertEquals(2, $this->db->insert($parameters));
    }

    /**
     * @test
     */
    public function InsertNoRecords()
    {
        $parameters = array (
            'fields' => array ('NotAField', 'NotAField2'),
            'table' => 'testtable',
            'records' => array (
                array ('Fiona', 'Knight')
            )
        );
        $this->assertEquals(0, $this->db->insert($parameters));
    }

    /**
     * @test
     */
    public function InsertDuplicateRecordReturnsZero()
    {
        $parameters = array (
            'fields' => array ('Id', 'FirstName', 'Surname'),
            'table' => 'testtable',
            'records' => array (
                array (1, 'SomeFirstName', 'SomeLastName')
            )
        );
        $this->assertEquals(0, $this->db->insert($parameters));
    }

    /**
     * @test
     */
    public function UpdateOneFieldOneRecord()
    {
        $parameters = array (
            'table' => 'testtable',
            'fieldValues' => array ('Surname' => 'Spartacus'),
            'conditions' => array (
                array ('FirstName' => 'Philip', 'join' => '=')
            )
        );
        $this->assertEquals(1, $this->db->update($parameters));
    }

    /**
     * @test
     */
    public function UpdateOneFieldAllRecords()
    {
        $parameters = array (
            'table' => 'testtable',
            'fieldValues' => array ('Surname' => 'Spartacus')
        );
        $this->assertEquals(2, $this->db->update($parameters));
    }

    /**
     * @test
     */
    public function DeleteAllRecords()
    {
        $parameters = array(
            'table' => 'testtable'
        );
        $this->assertEquals(2, $this->db->delete($parameters));
    }

    /**
     * @test
     */
    public function DeleteOneRecord()
    {
        $parameters = array(
            'table' => 'testtable',
            'conditions' => array(
                array ('Id' => 1, 'join' => '=')
            )
        );
    }
}
