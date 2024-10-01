<?php

class CommonProduction_model extends MY_Model
{

    function getAppConfig()
    {

        $this->prodDB->select([
            "username",
            "password",
            "paymentURL",
            "inquiryURL",
            "refundURL",
            "loginURL",
            "secret"
        ])
            ->from("appConfig");

        return $this->prodDB->get();
    }

    function getDefaultParameters()
    {

        $this->prodDB->select([
            "partnerID",
            "partnerName",
            "merchantID",
            "merchantName",
            "merchantCategoryCode"
        ])
            ->from("defaultParameters");

        return $this->prodDB->get();
    }
}
