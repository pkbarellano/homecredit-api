<?php

class Inquiry_model extends MY_Model
{

    function createRequestInquiry($referenceID = "", $param = [], $signature = "", $token = "", $json = "")
    {

        $this->_transStart('prodDB');

        $this->prodDB->set([
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

        $this->prodDB->insert('inquiryRequests');

        return $this->_transEnd('prodDB');
    }

    function createSuccessResponseInquiry($param = [])
    {

        $this->_transStart('prodDB');

        $this->prodDB->set([
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

        $this->prodDB->insert('inquiryResponses');

        return $this->_transEnd('prodDB');
    }

    function createFailedResponseInquiry($param = [])
    {

        $this->_transStart('prodDB');

        $this->prodDB->set([
            'referenceID' => $param['referenceID'],
            'errors_code' => $param['code'],
            'errors_message' => $param['message'],
            'errors_severity' => $param['severity'],
            'errors_ticketID' => $param['ticketID'],
            'stat' => $param['status'],
            'json' => $param['json']
        ]);

        $this->prodDB->insert('inquiryResponses');

        return $this->_transEnd('prodDB');
    }
}
