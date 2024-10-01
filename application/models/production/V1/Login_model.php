<?php

class Login_model extends MY_Model
{

    function createRequestLogin($param = [])
    {

        $this->_transStart('prodDB');

        $this->prodDB->set([
            'referenceID' => $param['referenceID'],
            'credentials_username' => $param['username'],
            'credentials_password' => $param['password']
        ]);

        $this->prodDB->insert('loginRequests');

        return $this->_transEnd('prodDB');
    }

    function createSuccessResponseLogin($param = [])
    {

        $this->_transStart('prodDB');

        $this->prodDB->set([
            'referenceID' => $param['referenceID'],
            'accessToken' => $param['accessToken'],
            'expiresIn' => $param['expiresIn'],
            'stat' => $param['status']
        ]);

        $this->prodDB->insert('loginResponses');

        return $this->_transEnd('prodDB');
    }
    
    function createFailedResponseLogin($param = [])
    {

        $this->_transStart('prodDB');

        $this->prodDB->set([
            'referenceID' => $param['referenceID'],
            'errors_code' => $param['code'],
            'errors_message' => $param['message'],
            'errors_severity' => $param['severity'],
            'errors_ticketID' => $param['ticketID'],
            'stat' => $param['status']
        ]);

        $this->prodDB->insert('loginResponses');

        return $this->_transEnd('prodDB');
    }
}
