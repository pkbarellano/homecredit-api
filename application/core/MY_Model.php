<?php

class MY_Model extends CI_Model
{

    protected $prodDB;

    function __construct()
    {

        parent::__construct();

        $this->prodDB = $this->load->database('prodDB', TRUE, 'prodDB');
    }

    protected function _transStart($db = 'db')
    {

        $this->$db->trans_start();
    }

    protected function _transEnd($db = 'db')
    {

        $this->$db->trans_complete();

        if ($this->$db->trans_status() === true) {

            $this->$db->trans_commit();

            return true;
        } else {

            $this->$db->trans_commit();

            return false;
        }
    }
}
