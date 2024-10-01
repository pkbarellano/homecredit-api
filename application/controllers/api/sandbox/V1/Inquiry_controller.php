<?php

class Inquiry_controller extends MY_Controller
{

    private $client;

    function __construct()
    {

        parent::__construct();

        $this->client = new HCSandboxClient();

        $this->load->model('sandbox/V1/Inquiry_model');
    }

    private function _HCClientInquiry($body = [])
    {

        $loginBody = [
            'credentials' => [
                'userName' => $this->appConfigSandbox->username,
                'password' => $this->appConfigSandbox->password
            ]
        ];

        $body = [
            'barcode' => $body['barcode'],
            'partnerId' => $this->defaultParameterSandbox->partnerID,
            'partnerName' => $this->defaultParameterSandbox->partnerName
        ];

        return $this->client->sendRequest(
            $this->appConfigSandbox->loginURL,
            $this->appConfigSandbox->inquiryURL,
            $loginBody,
            $body,
            0,
            'POST',
            $this->appConfigSandbox->secret
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

    public function create_post()
    {

        $payload = $this->request->body;

        $referenceID = $this->_generateReferenceID("sandbox", "INQUIRY");

        if ($referenceID !== null) {

            $resp = $this->_HCClientInquiry($payload);
            
            $this->createSysLog('sandbox', $referenceID, $_SERVER['REQUEST_METHOD'], __CLASS__, __METHOD__, 'INQUIRY', $payload['terminalID'], $payload['terminalIPAddress'], '', $resp['responseInfo']);

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

        $this->response($this->responseArray, 200);
    }
}
