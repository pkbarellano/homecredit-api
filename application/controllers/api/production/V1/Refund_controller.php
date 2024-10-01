<?php

class Refund_controller extends MY_Controller
{

    private $HCUrl = '/barcode-payment/transaction/refund';
    private $client;

    function __construct()
    {

        parent::__construct();

        $this->client = new HCProductionClient();

        $this->load->model('production/V1/Refund_model');
    }

    private function _HCClientPayment($body = [])
    {

        $loginBody = [
            'credentials' => [
                'userName' => $this->appConfigProduction->username,
                'password' => $this->appConfigProduction->password
            ]
        ];

        $body = [
            'barcode' => $body['barcode'],
            'partnerId' => $this->defaultParameterProduction->partnerID,
            'partnerName' => $this->defaultParameterProduction->partnerName,
            'reason' => $body['reason'],
            'amount' => [
                'currency' => $body['currency'],
                'amount' => number_format($body['amount'], 2, ".", "")
            ]
        ];

        return $this->client->sendRequest(
            $this->appConfigProduction->loginURL,
            $this->appConfigProduction->refundURL,
            $loginBody,
            $body,
            0,
            'POST',
            $this->appConfigProduction->secret
        );
    }

    private function _createRequestRefund($referenceID = "", $body = [], $signature = "", $token = "")
    {

        if ($this->Refund_model->createRequestRefund($referenceID, $body, $signature, $token, json_encode($body)) === false) {

            $this->_response(0, "", "Warning: Query failed saving refund request.");

            $this->response($this->responseArray, 422);
        }
    }

    private function _createSuccessResponseRefund($referenceID, $body, $json)
    {

        $bodyArray = [
            'referenceID' => $referenceID,
            'code' => $body->code,
            'barcode' => $body->data->barcode,
            'currency' => $body->data->amount->currency,
            'amount' => $body->data->amount->amount,
            'refundReferenceNo' => $body->data->refundReferenceNo,
            'transactionDateTime' => $body->data->transactionDateTime,
            'transactionStatus' => $body->data->transactionStatus,
            'signature' => $body->data->signature,
            'status' => 'SUCCESS',
            'json' => $json
        ];

        if ($this->Refund_model->createSuccessResponseRefund($bodyArray) === false) {

            $this->_response(0, "", "Warning: Query failed saving refund response.");

            $this->response($this->responseArray, 422);
        }
    }

    private function _createFailedResponseRefund($referenceID, $body, $json)
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

        if ($this->Refund_model->createFailedResponseRefund($bodyArray) === false) {

            $this->_response(0, "", "Warning: Query failed saving refund response.");

            $this->response($this->responseArray, 422);
        }
    }

    public function create_post()
    {

        $payload = $this->request->body;

        $referenceID = $this->_generateReferenceID("production", "REFUND");

        if ($referenceID !== null) {

            $resp = $this->_HCClientPayment($payload);

            $this->createSysLog('production', $referenceID, $_SERVER['REQUEST_METHOD'], __CLASS__, __METHOD__, 'REFUND', $payload['terminalID'], $payload['terminalIPAddress'], '', $resp['responseInfo']);

            $this->_createRequestRefund($referenceID, $resp['requestBody'], $resp['signature'], $resp['token']);

            if ($resp['status'] === true) {

                $this->_createSuccessResponseRefund($referenceID, $resp['response'], json_encode([
                    'response' => $resp['response'],
                    'cURLError' => $resp['cURLError']
                ]));

                $this->_response(1, "Refund was successful.");

                $this->response($this->responseArray, 200);
            } else {

                $this->_createFailedResponseRefund($referenceID, $resp['errors'], json_encode([
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
