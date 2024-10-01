<?php

class Payment_controller extends MY_Controller
{

    private $client;

    function __construct()
    {

        parent::__construct();

        $this->client = new HCProductionClient();

        $this->load->model('production/V1/Payment_model');
        $this->load->model('production/V1/Inquiry_model');
    }

    private function _getBranchName($storeCode = 0)
    {

        return $this->Payment_model->getBranchName($storeCode)->row_array();
    }

    private function _HCClientPayment($body = [])
    {

        $productDetails = new stdClass;

        if (isset($body['productDetails'][0])) {

            $productDetails->name = $body['productDetails'][0]['name'];
            $productDetails->description = $body['productDetails'][0]['description'];
            $productDetails->quantity = $body['productDetails'][0]['quantity'];
            $productDetails->itemPrice = number_format($body['productDetails'][0]['itemPrice'], 2, ".", "");
        }

        $loginBody = [
            'credentials' => [
                'userName' => $this->appConfigProduction->username,
                'password' => $this->appConfigProduction->password
            ]
        ];

        $currentDate = date(DATE_W3C);

        $locationName = $this->_getBranchName($body['location'])['branchName'];

        $body = [
            'amount' => [
                'currency' => $body['currency'],
                'amount' => number_format($body['amount'], 2, ".", "")
            ],
            'barcode' => $body['barcode'],
            'cashier' => "NA",
            'location' => $locationName,
            'merchantCategoryCode' => $this->defaultParameterProduction->merchantCategoryCode,
            'merchantId' => $this->defaultParameterProduction->merchantID,
            'merchantName' => $this->defaultParameterProduction->merchantName,
            'partnerId' => $this->defaultParameterProduction->partnerID,
            'partnerName' => $this->defaultParameterProduction->partnerName,
            'partnerReferenceNumber' => $body['transactionNo'],
            'requestDateTime' => strrev(substr_replace(strrev($currentDate), '', 0, strpos(strrev($currentDate), '+') + 1)),
            'terminal' => $body['terminalID'],
            'paymentType' => "purchase"
        ];

        return $this->client->sendRequest(
            $this->appConfigProduction->loginURL,
            $this->appConfigProduction->paymentURL,
            $loginBody,
            $body,
            0,
            'POST',
            $this->appConfigProduction->secret
        );
    }

    private function _createRequestPayment($referenceID = "", $body = [], $signature = "", $token = "")
    {

        if ($this->Payment_model->createRequestPayment($referenceID, $body, $signature, $token, json_encode($body)) === false) {

            $this->_response(0, "", "Warning: Query failed saving payment request.");

            $this->response($this->responseArray, 422);
        }
    }

    private function _createSuccessResponsePayment($referenceID, $body, $json)
    {

        $bodyArray = [
            'referenceID' => $referenceID,
            'code' => $body->code,
            'barcode' => $body->data->barcode,
            'currency' => $body->data->amount->currency,
            'amount' => $body->data->amount->amount,
            'transactionDateTime' => $body->data->transactionDateTime,
            'transactionStatus' => $body->data->transactionStatus,
            'signature' => $body->data->signature,
            'status' => 'SUCCESS',
            'json' => $json
        ];

        if ($this->Payment_model->createSuccessResponsePayment($bodyArray) === false) {

            $this->_response(0, "", "Warning: Query failed saving payment response.");

            $this->response($this->responseArray, 422);
        }
    }

    private function _createFailedResponsePayment($referenceID, $body, $json)
    {

        $bodyArray = [
            'referenceID' => $referenceID,
            'code' => $body['code'],
            'message' => $body['message'],
            'severity' => $body['severity'],
            'ticketID' => $body['ticketID'],
            'status' => 'FAILED',
            'json' => $json
        ];

        if ($this->Payment_model->createFailedResponsePayment($bodyArray) === false) {

            $this->_response(0, "", "Warning: Query failed saving payment response.");

            $this->response($this->responseArray, 422);
        }
    }

    private function _HCClientInquiry($body = [])
    {

        $loginBody = [
            'credentials' => [
                'userName' => $this->appConfigProduction->username,
                'password' => $this->appConfigProduction->password
            ]
        ];

        $inquiryBody = [
            'barcode' => $body['barcode'],
            'partnerId' => $this->defaultParameterProduction->partnerID,
            'partnerName' => $this->defaultParameterProduction->partnerName
        ];

        return $this->client->sendRequest(
            $this->appConfigProduction->loginURL,
            $this->appConfigProduction->inquiryURL,
            $loginBody,
            $inquiryBody,
            0,
            'POST',
            $this->appConfigProduction->secret
        );
    }

    private function _createRequestInquiry($referenceID = "", $body = [], $signature = "", $token = "")
    {

        if ($this->Inquiry_model->createRequestInquiry($referenceID, $body, $signature, $token, json_encode($body)) === false) {

            $this->_response(0, "", "Warning: Query failed saving inquiry request.");

            $this->response($this->responseArray, 422);
        }
    }

    private function _createSuccessResponseInquiry($referenceID, $body, $json)
    {

        $bodyArray = [
            'referenceID' => $referenceID,
            'code' => $body->code,
            'barcode' => $body->data->barcode,
            'currency' => $body->data->amount->currency,
            'amount' => $body->data->amount->amount,
            'transactionDateTime' => $body->data->transactionDateTime,
            'transactionStatus' => $body->data->transactionStatus,
            'signature' => $body->data->signature,
            'status' => 'SUCCESS',
            'json' => $json
        ];

        if ($this->Inquiry_model->createSuccessResponseInquiry($bodyArray) === false) {

            $this->_response(0, "", "Warning: Query failed saving inquiry response.");

            $this->response($this->responseArray, 422);
        }
    }

    private function _createFailedResponseInquiry($referenceID, $body, $json)
    {

        $bodyArray = [
            'referenceID' => $referenceID,
            'code' => $body['code'],
            'message' => $body['message'],
            'severity' => $body['severity'],
            'ticketID' => $body['ticketID'],
            'status' => 'FAILED',
            'json' => $json
        ];

        if ($this->Inquiry_model->createFailedResponseInquiry($bodyArray) === false) {

            $this->_response(0, "", "Warning: Query failed saving inquiry response.");

            $this->response($this->responseArray, 422);
        }
    }

    private function _inquiryFallback($payload)
    {

        $referenceID = $this->_generateReferenceID("production", "INQUIRY");

        if ($referenceID !== null) {

            $resp = $this->_HCClientInquiry($payload);

            $this->createSysLog('production', $referenceID, $_SERVER['REQUEST_METHOD'], __CLASS__, __METHOD__, 'INQUIRY', $payload['terminalID'], $payload['terminalIPAddress'], '', $resp['responseInfo']);

            $this->_createRequestInquiry($referenceID, $resp['requestBody'], $resp['signature'], $resp['token']);

            if ($resp['status'] === true) {

                $this->_createSuccessResponseInquiry($referenceID, $resp['response'], json_encode([
                    'response' => $resp['response'],
                    'cURLError' => $resp['cURLError']
                ]));

                if ($resp['response']->data->transactionStatus == "SUCCESS") {

                    $this->_response(1, "Transaction was successful.");
                } else {

                    $this->_response(0, "Transaction failed.");
                }

                $this->response($this->responseArray, 200);
            } else {

                $this->_createFailedResponseInquiry($referenceID, $resp['errors'], json_encode([
                    'response' => $resp['response'],
                    'cURLError' => $resp['cURLError']
                ]));

                $this->_response(0, $resp['errors']['message']);

                $this->response($this->responseArray, 200);
            }
        }
    }

    public function create_post()
    {

        $payload = $this->request->body;

        $referenceID = $this->_generateReferenceID("production", "PAYMENT");

        if ($referenceID !== null) {

            $resp = $this->_HCClientPayment($payload);

            $this->createSysLog('production', $referenceID, $_SERVER['REQUEST_METHOD'], __CLASS__, __METHOD__, 'PAYMENT', $payload['terminalID'], $payload['terminalIPAddress'], '', $resp['responseInfo']);

            $this->_createRequestPayment($referenceID, $resp['requestBody'], $resp['signature'], $resp['token']);

            if ($resp['status'] === true) {

                $this->_createSuccessResponsePayment($referenceID, $resp['response'], json_encode([
                    'response' => $resp['response'],
                    'cURLError' => $resp['cURLError']
                ]));

                $this->_response(1, "Payment was successful.");

                $this->response($this->responseArray, 200);
            } else {

                if ($resp['errors']['code'] == "Undefined" && $resp['errors']['message'] == "Undefined") {

                    $this->_inquiryFallback($payload);
                } else {
                    $this->_createFailedResponsePayment($referenceID, $resp['errors'], json_encode([
                        'response' => $resp['response'],
                        'cURLError' => $resp['cURLError']
                    ]));

                    $this->_response(0, $resp['errors']['message']);

                    $this->response($this->responseArray, 200);
                }
            }
        }

        $this->response($this->responseArray, 200);
    }
}
