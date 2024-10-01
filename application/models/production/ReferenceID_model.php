<?php

class ReferenceID_model extends MY_Model
{

    function getLoginReferenceID($referenceID = "")
    {

        $this->prodDB->select('referenceID')
            ->from('loginReferenceIDs')
            ->where('referenceID', $referenceID);

        return $this->prodDB->get();
    }

    function createLoginReferenceID($referenceID = "")
    {

        $this->_transStart('prodDB');

        $data = ['referenceID' => $referenceID];

        $this->prodDB->insert('loginReferenceIDs', $data);

        return $this->_transEnd('prodDB');
    }

    function getPaymentReferenceID($referenceID = "")
    {

        $this->prodDB->select('referenceID')
            ->from('paymentReferenceIDs')
            ->where('referenceID', $referenceID);

        return $this->prodDB->get();
    }

    function createPaymentReferenceID($referenceID = "")
    {

        $this->_transStart('prodDB');

        $data = ['referenceID' => $referenceID];

        $this->prodDB->insert('paymentReferenceIDs', $data);

        return $this->_transEnd('prodDB');
    }

    function getRefundReferenceID($referenceID = "")
    {

        $this->prodDB->select('referenceID')
            ->from('refundReferenceIDs')
            ->where('referenceID', $referenceID);

        return $this->prodDB->get();
    }

    function createRefundReferenceID($referenceID = "")
    {

        $this->_transStart('prodDB');

        $data = ['referenceID' => $referenceID];

        $this->prodDB->insert('refundReferenceIDs', $data);

        return $this->_transEnd('prodDB');
    }

    function getInquiryReferenceID($referenceID = "")
    {

        $this->prodDB->select('referenceID')
            ->from('inquiryReferenceIDs')
            ->where('referenceID', $referenceID);

        return $this->prodDB->get();
    }

    function createInquiryReferenceID($referenceID = "")
    {

        $this->_transStart('prodDB');

        $data = ['referenceID' => $referenceID];

        $this->prodDB->insert('inquiryReferenceIDs', $data);

        return $this->_transEnd('prodDB');
    }
}
