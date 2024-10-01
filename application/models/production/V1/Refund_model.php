<?php

class Refund_model extends MY_Model
{

    function createRequestRefund($referenceID = "", $param = [], $signature = "", $token = "", $json = "")
    {

        $this->_transStart('prodDB');

        $this->prodDB->set([
            'referenceID' => $referenceID,
            'barcode' => $param['barcode'],
            'partnerID' => $param['partnerId'],
            'partnerName' => $param['partnerName'],
            'reason' => $param['reason'],
            'amount_currency' => $param['amount']['currency'],
            'amount_amount' => $param['amount']['amount'],
            'signature' => $signature,
            'token' => $token,
            'json' => $json
        ]);

        $this->prodDB->insert('refundRequests');

        return $this->_transEnd('prodDB');
    }

    function createSuccessResponseRefund($param = [])
    {

        $this->_transStart('prodDB');

        $this->prodDB->set([
            'referenceID' => $param['referenceID'],
            'code' => $param['code'],
            'data_barcode' => $param['barcode'],
            'data_amount_currency' => $param['currency'],
            'data_amount_amount' => $param['amount'],
            'data_refundReferenceNo' => $param['refundReferenceNo'],
            'data_transactionDateTime' => $param['transactionDateTime'],
            'data_transactionStatus' => $param['transactionStatus'],
            'data_signature' => $param['signature'],
            'stat' => $param['status'],
            'json' => $param['json']
        ]);

        $this->prodDB->insert('refundResponses');

        return $this->_transEnd('prodDB');
    }

    function createFailedResponseRefund($param = [])
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

        $this->prodDB->insert('refundResponses');

        return $this->_transEnd('prodDB');
    }
}
