<?php

class CommonSandbox_model extends MY_Model
{

    function getAppConfig()
    {

        $this->db->select([
            "username",
            "password",
            "paymentURL",
            "inquiryURL",
            "refundURL",
            "loginURL",
            "secret"
        ])
            ->from("appConfig");

        return $this->db->get();
    }

    function getDefaultParameters()
    {

        $this->db->select([
            "partnerID",
            "partnerName",
            "merchantID",
            "merchantName",
            "merchantCategoryCode"
        ])
            ->from("defaultParameters");

        return $this->db->get();
    }
}
