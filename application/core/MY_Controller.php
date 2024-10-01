<?php

require(APPPATH . '/libraries/REST_Controller.php');
include_once(APPPATH . 'core/ErrorHandler.php');

class MY_Controller extends REST_Controller
{

    protected $responseArray;
    protected $appConfig;
    protected $defaultParameter;

    function __construct()
    {

        parent::__construct();

        /** SANDBOX */

        $this->appConfigSandbox = $this->CommonSandbox_model->getAppConfig()->row();
        $this->defaultParameterSandbox = $this->CommonSandbox_model->getDefaultParameters()->row();


        /** PRODUCTION */

        $this->appConfigProduction = $this->CommonProduction_model->getAppConfig()->row();
        $this->defaultParameterProduction = $this->CommonProduction_model->getDefaultParameters()->row();
    }

    protected function _response($statusCode = 0, $message = "", $additionalInfo = "")
    {

        if ($statusCode == 1) {

            $this->responseArray = [
                "response" => [
                    "status" => 0,
                    "details" => [
                        "code" => "SUCCESS",
                        "code_message" => ucfirst(strtolower($message))
                    ]
                ],
                "additionalInfo" => $additionalInfo
            ];
        } else {

            $this->responseArray = [
                "response" => [
                    "status" => 1,
                    "details" => [
                        "error" => "ERROR",
                        "error_description" => ucfirst(strtolower($message))
                    ]
                ],
                "additionalInfo" =>  $additionalInfo
            ];
        }
    }

    private function _validateType($type)
    {

        $allowedTypes = [
            'LOGIN',
            'PAYMENT',
            'REFUND',
            'INQUIRY'
        ];

        return (bool)in_array($type, $allowedTypes);
    }

    protected function _generateReferenceID($interface, $type = '')
    {

        $referenceID = null;
        $try = 0;

        $this->_response(0, ErrorHandler::UNKNOWN_ERROR);

        if ($this->_validateType($type) === true) {

            do {

                if ($try == 100) {

                    $this->_response(0, ErrorHandler::UNABLE_TO_CREATE_REFERECE_ID);

                    break;
                }

                $referenceID = uniqid(strtotime(date('Y-m-d h:i:s')));

                $try++;
            } while ($this->_validateReferenceID($interface, $type, $referenceID) === false);

            $this->_createReferenceID($interface, $type, $referenceID);
        } else {

            $this->_response(0, ErrorHandler::INVALID_REFERECE_ID_TYPE);
        }

        return $referenceID;
    }

    private function _createReferenceID($interface = "sandbox", $type = "", $referenceID = "")
    {

        $this->load->model($interface . "/ReferenceID_model");

        switch ($type) {

            case "LOGIN":

                $createLoginReferenceID = $this->ReferenceID_model->createLoginReferenceID($referenceID);

                if ($createLoginReferenceID === false) {

                    $this->_response(0, ErrorHandler::UNABLE_TO_CREATE_REFERECE_ID);
                }

                break;

            case "PAYMENT":

                $createPaymentReferenceID = $this->ReferenceID_model->createPaymentReferenceID($referenceID);

                if ($createPaymentReferenceID === false) {

                    $this->_response(0, ErrorHandler::UNABLE_TO_CREATE_REFERECE_ID);
                }

                break;

            case "REFUND":

                $createRefundReferenceID = $this->ReferenceID_model->createRefundReferenceID($referenceID);

                if ($createRefundReferenceID === false) {

                    $this->_response(0, ErrorHandler::UNABLE_TO_CREATE_REFERECE_ID);
                }

                break;

            case "INQUIRY":

                $createInquiryReferenceID = $this->ReferenceID_model->createInquiryReferenceID($referenceID);

                if ($createInquiryReferenceID === false) {

                    $this->_response(0, ErrorHandler::UNABLE_TO_CREATE_REFERECE_ID);
                }

                break;
        }
    }

    private function _validateReferenceID($interface = "sandbox", $type = "", $referenceID = "")
    {

        $this->load->model($interface . "/ReferenceID_model");

        $referenceStatus = false;

        switch ($type) {

            case "LOGIN":

                $getLoginReferenceID = $this->ReferenceID_model->getLoginReferenceID($referenceID);

                $referenceStatus = ($getLoginReferenceID->num_rows() == 0) && true;

                break;

            case "PAYMENT":

                $getPaymentReferenceID = $this->ReferenceID_model->getPaymentReferenceID($referenceID);

                $referenceStatus = ($getPaymentReferenceID->num_rows() == 0) && true;

                break;

            case "REFUND":

                $getRefundReferenceID = $this->ReferenceID_model->getRefundReferenceID($referenceID);

                $referenceStatus = ($getRefundReferenceID->num_rows() == 0) && true;

                break;

            case "INQUIRY":

                $getInquiryReferenceID = $this->ReferenceID_model->getInquiryReferenceID($referenceID);

                $referenceStatus = ($getInquiryReferenceID->num_rows() == 0) && true;

                break;
        }

        return $referenceStatus;
    }

    private function _sysLog(
        $interface = 'sandbox',
        $referenceID = '',
        $httpMethod = '',
        $scriptClass = '',
        $scriptMethod = '',
        $description = '',
        $terminalID = '',
        $terminalIPAddress = '',
        $terminalMACAddress = '',
        $hcccurlURL = '',
        $hcccurlContentType = '',
        $hccurlHttpCode = '',
        $hccurlHeaderSize = '',
        $hccurlRequestSize = '',
        $hccurlFiletime = '',
        $hccurlSSLVerifyResult = '',
        $hccurlRedirectCount = '',
        $hccurlTotalTime = '',
        $hccurlNameLookupTime = '',
        $hccurlConnectTime = '',
        $hccurlPreTransferTime = '',
        $hccurlSizeUpload = '',
        $hccurlSizeDownload = '',
        $hccurlSpeedDownload = '',
        $hccurlSpeedUpload = '',
        $hccurlDownloadContentLength = '',
        $hccurlUploadContentLength = '',
        $hccurlStartTransferTime = '',
        $hccurlRedirectTime = '',
        $hccurlRedirectUrl = '',
        $hccurlPrimaryIP = '',
        $hccurlPrimaryPort = '',
        $hccurlLocalIp = '',
        $hccurlLocalPort = '',
        $json
    ) {

        $this->load->model($interface . '/SysLog_model');

        $params = [
            'referenceID' => $referenceID,
            'httpMethod' => $httpMethod,
            'scriptClass' => $scriptClass,
            'scriptMethod' => $scriptMethod,
            'description' => $description,
            'terminalID' => $terminalID,
            'terminalIPAddress' => $terminalIPAddress,
            'terminalMACAddress' => $terminalMACAddress,
            'hccurl_URL' => $hcccurlURL,
            'hccurl_contentType' => $hcccurlContentType,
            'hccurl_httpCode' => $hccurlHttpCode,
            'hccurl_headerSize' => $hccurlHeaderSize,
            'hccurl_requestSize' => $hccurlRequestSize,
            'hccurl_filetime' => $hccurlFiletime,
            'hccurl_sslVerifyResult' => $hccurlSSLVerifyResult,
            'hccurl_redirectCount' => $hccurlRedirectCount,
            'hccurl_totalTime' => $hccurlTotalTime,
            'hccurl_namelookupTime' => $hccurlNameLookupTime,
            'hccurl_connectTime' => $hccurlConnectTime,
            'hccurl_preTransferTime' => $hccurlPreTransferTime,
            'hccurl_sizeUpload' => $hccurlSizeUpload,
            'hccurl_sizeDownload' => $hccurlSizeDownload,
            'hccurl_speedDownload' => $hccurlSpeedDownload,
            'hccurl_speedUpload' => $hccurlSpeedUpload,
            'hccurl_downloadContentLength' => $hccurlDownloadContentLength,
            'hccurl_uploadContentLength' => $hccurlUploadContentLength,
            'hccurl_startTransferTime' => $hccurlStartTransferTime,
            'hccurl_redirectTime' => $hccurlRedirectTime,
            'hccurl_redirectUrl' => $hccurlRedirectUrl,
            'hccurl_primaryIp' => $hccurlPrimaryIP,
            'hccurl_primaryPort' => $hccurlPrimaryPort,
            'hccurl_localIp' => $hccurlLocalIp,
            'hccurl_localPort' => $hccurlLocalPort,
            'json' => $json
        ];

        $this->SysLog_model->createLog($params);
    }

    protected function createSysLog($interface = '', $referenceID = '', $httpMethod = '', $scriptClass = '', $scriptMethod = '', $description = '', $terminalID = '', $terminalIPAddress = '', $terminalMACAddress = '', $hccurl = [])
    {

        $this->_sysLog(
            $interface,
            $referenceID,
            $httpMethod,
            $scriptClass,
            $scriptMethod,
            $description,
            $terminalID,
            $terminalIPAddress,
            $terminalMACAddress,
            (isset($hccurl['url'])) ? $hccurl['url'] : '',
            (isset($hccurl['content_type'])) ? $hccurl['content_type'] : '',
            (isset($hccurl['http_code'])) ? $hccurl['http_code'] : '',
            (isset($hccurl['header_size'])) ? $hccurl['header_size'] : '',
            (isset($hccurl['request_size'])) ? $hccurl['request_size'] : '',
            (isset($hccurl['filetime'])) ? $hccurl['filetime'] : '',
            (isset($hccurl['ssl_verify_result'])) ? $hccurl['ssl_verify_result'] : '',
            (isset($hccurl['redirect_count'])) ? $hccurl['redirect_count'] : '',
            (isset($hccurl['total_time'])) ? $hccurl['total_time'] : '',
            (isset($hccurl['namelookup_time'])) ? $hccurl['namelookup_time'] : '',
            (isset($hccurl['connect_time'])) ? $hccurl['connect_time'] : '',
            (isset($hccurl['pretransfer_time'])) ? $hccurl['pretransfer_time'] : '',
            (isset($hccurl['size_upload'])) ? $hccurl['size_upload'] : '',
            (isset($hccurl['size_download'])) ? $hccurl['size_download'] : '',
            (isset($hccurl['speed_download'])) ? $hccurl['speed_download'] : '',
            (isset($hccurl['speed_upload'])) ? $hccurl['speed_upload'] : '',
            (isset($hccurl['download_content_length'])) ? $hccurl['download_content_length'] : '',
            (isset($hccurl['upload_content_length'])) ? $hccurl['upload_content_length'] : '',
            (isset($hccurl['starttransfer_time'])) ? $hccurl['starttransfer_time'] : '',
            (isset($hccurl['redirect_time'])) ? $hccurl['redirect_time'] : '',
            (isset($hccurl['redirect_url'])) ? $hccurl['redirect_url'] : '',
            (isset($hccurl['primary_ip'])) ? $hccurl['primary_ip'] : '',
            (isset($hccurl['primary_port'])) ? $hccurl['primary_port'] : '',
            (isset($hccurl['local_ip'])) ? $hccurl['local_ip'] : '',
            (isset($hccurl['local_port'])) ? $hccurl['local_port'] : '',
            json_encode($hccurl)
        );
    }
}
