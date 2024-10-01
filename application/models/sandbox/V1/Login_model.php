<?php

class Login_model extends MY_Model
{

    function createRequestLogin($param = [])
    {

        $this->_transStart();

        $this->db->set([
            'referenceID' => $param['referenceID'],
            'credentials_username' => $param['username'],
            'credentials_password' => $param['password']
        ]);

        $this->db->insert('loginRequests');

        return $this->_transEnd();
    }

    function createSuccessResponseLogin($param = [])
    {

        $this->_transStart();

        $this->db->set([
            'referenceID' => $param['referenceID'],
            'accessToken' => $param['accessToken'],
            'expiresIn' => $param['expiresIn'],
            'stat' => $param['status']
        ]);

        $this->db->insert('loginResponses');

        return $this->_transEnd();
    }
    
    function createFailedResponseLogin($param = [])
    {

        $this->_transStart();

        $this->db->set([
            'referenceID' => $param['referenceID'],
            'errors_code' => $param['code'],
            'errors_message' => $param['message'],
            'errors_severity' => $param['severity'],
            'errors_ticketID' => $param['ticketID'],
            'stat' => $param['status']
        ]);

        $this->db->insert('loginResponses');

        return $this->_transEnd();
    }
}
