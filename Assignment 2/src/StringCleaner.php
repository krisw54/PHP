<?php
/**
 * Created by PhpStorm.
 * User: Kris
 * Date: 20/12/2017
 * Time: 09:15
 */

namespace Toolkit;


class StringCleaner
{
    private $str = '';
    private $cleanStr='';

    public function __construct($toClean)
    {
        $this->str = $toClean;
        $this->cleanString();
    }

    public function cleanString() {
        $this->cleanStr = $this->test_input($this->str);
        return $this->cleanStr;
    }

    private function test_input($data) {
        $data = trim($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data);
        $data = htmlentities($data, ENT_QUOTES);
        echo $data;
        return $data;
    }


}