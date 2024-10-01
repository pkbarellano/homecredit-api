<?php

class Payment_model extends MY_Model
{

    function createRequestPayment($referenceID = "", $param = [], $signature = "", $token = "", $json = "")
    {

        $this->_transStart();

        $this->db->set([
            'referenceID' => $referenceID,
            'barcode' => $param['barcode'],
            'paymentType' => $param['paymentType'],
            'partnerID' => $param['partnerId'],
            'partnerName' => $param['partnerName'],
            'merchantID' => $param['merchantId'],
            'merchantName' => $param['merchantName'],
            'merchantCategoryCode' => $param['merchantCategoryCode'],
            'amount_currency' => $param['amount']['currency'],
            'amount_amount' => $param['amount']['amount'],
            'productDetails_name' => (isset($param['productDetails']) && property_exists($param['productDetails'][0], 'name')) ? $param['productDetails'][0]->name : '',
            'productDetails_description' => (isset($param['productDetails']) && property_exists($param['productDetails'][0], 'description')) ? $param['productDetails'][0]->description : '',
            'productDetails_quantity' => (isset($param['productDetails']) && property_exists($param['productDetails'][0], 'quantity')) ? $param['productDetails'][0]->quantity : '',
            'productDetails_itemPrice' => (isset($param['productDetails']) && property_exists($param['productDetails'][0], 'itemPrice')) ? $param['productDetails'][0]->itemPrice : '',
            'cashier' => $param['cashier'],
            'terminal' => $param['terminal'],
            'location' => $param['location'],
            'partnerReferenceNumber' => $param['partnerReferenceNumber'],
            'requestDateTime' => $param['requestDateTime'],
            'signature' => $signature,
            'token' => $token,
            'json' => $json
        ]);

        $this->db->insert('paymentRequests');

        return $this->_transEnd();
    }

    function createSuccessResponsePayment($param = [])
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

        $this->db->insert('paymentResponses');

        return $this->_transEnd();
    }

    function createFailedResponsePayment($param = [])
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

        $this->db->insert('paymentResponses');

        return $this->_transEnd();
    }

    function getBranchName($storeCode = 0)
    {

        return $this->db->select(["CONCAT(RTRIM(storeCode COLLATE DATABASE_DEFAULT), ' - ', UPPER(RTRIM(storeName COLLATE DATABASE_DEFAULT))) AS branchName"], false)
            ->from("stores")
            ->where("storeCode", $storeCode)
            ->get();
    }
}
