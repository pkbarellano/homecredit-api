<?php

class SysLog_model extends MY_Model
{

    function createLog($params = [])
    {

        $this->_transStart();

        $this->db->set($params);

        $this->db->insert('sysLogs');

        return $this->_transEnd();
    }
}
