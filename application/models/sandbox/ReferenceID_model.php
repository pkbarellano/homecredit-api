<?php

class ReferenceID_model extends MY_Model
{

    function getLoginReferenceID($referenceID = "")
    {

        $this->db->select('referenceID')
            ->from('loginReferenceIDs')
            ->where('referenceID', $referenceID);

        return $this->db->get();
    }

    function createLoginReferenceID($referenceID = "")
    {

        $this->_transStart();

        $data = ['referenceID' => $referenceID];

        $this->db->insert('loginReferenceIDs', $data);

        return $this->_transEnd();
    }

    function getPaymentReferenceID($referenceID = "")
    {

        $this->db->select('referenceID')
            ->from('paymentReferenceIDs')
            ->where('referenceID', $referenceID);

        return $this->db->get();
    }

    function createPaymentReferenceID($referenceID = "")
    {

        $this->_transStart();

        $data = ['referenceID' => $referenceID];

        $this->db->insert('paymentReferenceIDs', $data);

        return $this->_transEnd();
    }

    function getRefundReferenceID($referenceID = "")
    {

        $this->db->select('referenceID')
            ->from('refundReferenceIDs')
            ->where('referenceID', $referenceID);

        return $this->db->get();
    }

    function createRefundReferenceID($referenceID = "")
    {

        $this->_transStart();

        $data = ['referenceID' => $referenceID];

        $this->db->insert('refundReferenceIDs', $data);

        return $this->_transEnd();
    }

    function getInquiryReferenceID($referenceID = "")
    {

        $this->db->select('referenceID')
            ->from('inquiryReferenceIDs')
            ->where('referenceID', $referenceID);

        return $this->db->get();
    }

    function createInquiryReferenceID($referenceID = "")
    {

        $this->_transStart();

        $data = ['referenceID' => $referenceID];

        $this->db->insert('inquiryReferenceIDs', $data);

        return $this->_transEnd();
    }
}
