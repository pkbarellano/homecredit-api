<?php

class Login_controller extends MY_Controller
{

    private $HCUrl = '/partners/login';
    private $client;

    function __construct()
    {

        parent::__construct();

        $this->client = new HCSandboxClient();

        $this->load->model('sandbox/V1/Login_model');
    }

    /**
     * Method: Login_controller->_HCClientLogin
     * Payload:
     *      (string) username
     *      (string) password
     */

    private function _HCClientLogin($body)
    {

        $body = [
            'credentials' => [
                'userName' => $body['username'],
                'password' => $body['password']
            ]
        ];

        return $this->client->sendRequest(
            $this->appConfigSandbox->loginURL,
            $body,
            0,
            'POST',
            $this->appConfigSandbox->username,
            $this->appConfigSandbox->password,
            $this->appConfigSandbox->secret
        );
    }

    private function _createRequestLogin($referenceID, $body)
    {

        $bodyArray = array_merge($body, ['referenceID' => $referenceID]);

        if ($this->Login_model->createRequestLogin($bodyArray) === false) {

            $this->_response(0, "", "Warning: Query failed saving login request.");

            $this->response($this->responseArray, 422);
        }
    }

    private function _createSuccessResponseLogin($referenceID, $body)
    {

        $bodyArray = [
            'referenceID' => $referenceID,
            'accessToken' => $body->accessToken,
            'expiresIn' => $body->expiresIn,
            'status' => 'SUCCESS'
        ];

        if ($this->Login_model->createSuccessResponseLogin($bodyArray) === false) {

            $this->_response(0, "", "Warning: Query failed saving login response.");

            $this->response($this->responseArray, 422);
        }
    }

    private function _createFailedResponseLogin($referenceID, $body)
    {

        $bodyArray = [
            'referenceID' => $referenceID,
            'code' => $body['code'],
            'message' => $body['message'],
            'severity' => $body['severity'],
            'ticketID' => $body['ticketID'],
            'status' => 'FAILED'
        ];

        if ($this->Login_model->createFailedResponseLogin($bodyArray) === false) {

            $this->_response(0, "", "Warning: Query failed saving login response.");

            $this->response($this->responseArray, 422);
        }
    }

    /**
     * Method: Login_controller->create_post
     * Payload:
     *      (string) username
     *      (string) password
     */

    public function create_post()
    {

        $payload = $this->request->body;

        $referenceID = $this->_generateReferenceID("sandbox", "LOGIN");

        if ($referenceID !== null) {

            $resp = $this->_HCClientLogin($payload);

            $this->_createRequestLogin($referenceID, $payload);

            if ($resp['status'] === true) {

                $this->_createSuccessResponseLogin($referenceID, $resp['response']);

                $this->_response(1, "Successfully logged-in.");

                $this->response($this->responseArray, 200);
            } else {

                $this->_createFailedResponseLogin($referenceID, $resp['errors']);

                $this->_response(0, $resp['errors']['message']);

                $this->response($this->responseArray, 200);
            }

            $this->createSysLog('sandbox', $referenceID, $_SERVER['REQUEST_METHOD'], __CLASS__, __METHOD__, 'LOGIN', '', '', '', $resp['responseInfo']);
        } else {

            $this->response($this->responseArray, 422);
        }
    }
}
