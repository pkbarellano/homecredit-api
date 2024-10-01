<?php

class Inquiry_model extends MY_Model
{

    function createRequestInquiry($referenceID = "", $param = [], $signature = "", $token = "", $json = "")
    {

        $this->_transStart();

        $this->db->set([
            'referenceID' => $referenceID,
            'barcode' => $param['barcode'],
            'partnerID' => $param['partnerId'],
            'partnerName' => $param['partnerName'],
            'merchantID' => (isset($param['merchantId'])) ? $param['merchantId'] : null,
            'merchantName' => (isset($param['merchantName'])) ? $param['merchantName'] : null,
            'signature' => $signature,
            'token' => $token,
            'json' => $json
        ]);

        $this->db->insert('inquiryRequests');

        return $this->_transEnd();
    }

    function createSuccessResponseInquiry($param = [])
    {

        $this->_transStart();

        $this->db->set([
            'referenceID' => $param['referenceID'],
            'code' => $param['code'],
            'data_barcode' => $param['barcode'],
            'data_amount_currency' => $param['currency'],
            'data_amount_amount' => $param['amount'],
            'data_transactionDateTime' => $param['transactionDateTime'],
            'data_transactionStatus' => $param['transactionStatus'],
            'data_signature' => $param['signature'],
            'stat' => $param['status'],
            'json' => $param['json']
        ]);

        $this->db->insert('inquiryResponses');

        return $this->_transEnd();
    }

    function createFailedResponseInquiry($param = [])
    {

        $this->_transStart();

        $this->db->set([
            'referenceID' => $param['referenceID'],
            'errors_code' => $param['code'],
            'errors_message' => $param['message'],
            'errors_severity' => $param['severity'],
            'errors_ticketID' => $param['ticketID'],
            'stat' => $param['status'],
            'json' => $param['json']
        ]);

        $this->db->insert('inquiryResponses');

        return $this->_transEnd();
    }
}
