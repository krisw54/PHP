<?php

namespace Toolkit;

class DbSession implements \SessionHandlerInterface {

    private $db;

    public function __construct(Database $db)
    {
        $this->db = $db;
        session_set_save_handler($this);
    }

    public function open($save_path, $name)
    {
        return true;
    }

    public function close()
    {
        return true;
    }

    public function read($session_id)
    {
        $parameters = array (
            'table' => 'sessions',
            'fields' => array ('sess_data'),
            'conditions' => array (
                array ('sess_id' => $session_id, 'join' => '=')
            )
        );
        $result = $this->db->select($parameters)[0]['sess_data'];

        return is_null($result) ? '' : $this->db->select($parameters)[0]['sess_data'];
    }

    public function write($session_id, $session_data)
    {
        $parameters = array (
            'table' => 'sessions',
            'fields' => array ('sess_id', 'sess_data', 'modified'),
            'records' => array (
                array ($session_id, $session_data, time())
            )
        );

        if (! $this->db->insert($parameters)) {
            $updateParameters = array(
                'table' => 'sessions',
                'fieldValues' => array(
                    'sess_data' => $session_data, 'modified' => time()
                ),
                'conditions' => array (
                    array ('sess_id' => $session_id, 'join' => '=')
                )
            );
            $this->db->update($updateParameters);
        }
        return true;
    }

    public function destroy($session_id)
    {
        $parameters = array (
            'table' => 'sessions',
            'conditions' => array (
                array ('sess_id' => $session_id, 'join' => '=')
            )
        );
        return $this->db->delete($parameters) == 1 ? true : false;
    }

    public function gc($maxlifetime)
    {
        $parameters = array (
            'table' => 'sessions',
            'conditions' => array (
                array ('modified' => time() - $maxlifetime, 'join' => '<')
            )
        );
        $this->db->delete($parameters);
        return true;
    }
}