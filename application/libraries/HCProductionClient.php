<?php

class HCProductionClient
{

    private $parsedString;

    private $params;

    private function header($token = "")
    {

        $header = ['Content-Type: application/json; charset=utf-8'];

        ($token !== "") && array_push($header, 'Authorization: Bearer ' . $token);

        /* Header */
        return $header;
    }

    private function _sortArrayKeysAscending($param)
    {

        if (is_array($param)) {

            ksort($param, SORT_REGULAR);

            foreach ($param as $key => $value) {

                if (is_array($param[$key])) {

                    ksort($param[$key], SORT_REGULAR);

                    $param[$key] = $this->_sortArrayKeysAscending($param[$key]);
                }
            }
        }

        return $param;
    }

    private function _concatenateArrayValues($param)
    {

        if (is_array($param)) {

            foreach ($param as $key => $value) {

                if (is_array($param[$key])) {

                    $this->_concatenateArrayValues($param[$key]);
                } else {

                    if (is_object($value)) {

                        foreach ($value as $property => $objValue) {

                            $this->parsedString .= $objValue;
                        }
                    } else {

                        $this->parsedString .= $value;
                    }
                }
            }
        }
    }

    private function generateSignature($body = [], $secret = "")
    {

        $bodySort = $this->_sortArrayKeysAscending($body);

        $this->_concatenateArrayValues($bodySort);

        $signature = hash_hmac('sha256', utf8_encode($this->parsedString), $secret);

        $this->parsedString = "";

        return $signature;
    }

    public function sendRequest($loginUrl = "", $url = "", $loginPayload = [], $payload = [], $timeout = 0, $method = 'POST', $secret = "")
    {

        $this->params = $payload;

        $token = $this->loginRequest($loginUrl, $loginPayload, $timeout, $method, "N");

        if ($token['status'] === true) {

            $resp = $this->completeRequest($url, $payload, $timeout, $method, "Y", $secret, $token["response"]->accessToken);

            $responseMerged = array_merge($resp, [
                'requestBody' => $this->params
            ]);
        } else {

            $responseMerged = array_merge($token, [
                'requestBody' => $this->params
            ]);
        }

        return $responseMerged;
    }

    private function _requestBody($url = "", $payload = [], $timeout = 0, $method = "POST", $withSign = "N", $secret = "", $token = "")
    {

        if ($withSign == 'Y') {

            $signature = $this->generateSignature($payload, $secret);

            $postFields = array_merge($payload, ['signature' => $signature]);
        } else {

            $signature = "";

            $postFields = $payload;
        }

        $curlHeader = $this->header($token);

        $body = array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => $timeout,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_POSTFIELDS => json_encode($postFields),
            CURLOPT_HTTPHEADER => $curlHeader
        );

        return $this->curlExec($body, $token, $signature);
    }

    private function curlExec($body, $token = "", $signature = "")
    {
        $curl = curl_init();

        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

        curl_setopt_array($curl, $body);

        $response = json_decode(curl_exec($curl));

        $responseInfo = curl_getinfo($curl);

        $error = curl_error($curl);

        curl_close($curl);

        $resp = [
            'status' => false,
            'errors' => [
                'code' => "Undefined",
                'message' => "Undefined",
                'severity' => null,
                'ticketID' => null
            ]
        ];

        if ($responseInfo['http_code'] == 200) {

            $resp = [
                'status' => true
            ];
        } else {

            if (property_exists($response, 'errors')) {

                if (is_array($response->errors)) {

                    $resp = [
                        'status' => false,
                        'errors' => [
                            'code' => $response->errors[0]->code,
                            'message' => $response->errors[0]->message,
                            'severity' => $response->errors[0]->severity,
                            'ticketID' => $response->errors[0]->ticketId
                        ]
                    ];
                } else {

                    $resp = [
                        'status' => false,
                        'errors' => [
                            'code' => $response->errors->code,
                            'message' => $response->errors->message,
                            'severity' => $response->errors->severity,
                            'ticketID' => $response->errors->ticketId
                        ]
                    ];
                }
            } else if (property_exists($response, 'severity') && $response->severity == "ERROR") {

                $resp = [
                    'status' => false,
                    'errors' => [
                        'code' => $response->code,
                        'message' => $response->message,
                        'severity' => $response->severity,
                        'ticketID' => $response->ticketId
                    ]
                ];
            } else {

                if (property_exists($response, 'error_description')) {

                    $resp = [
                        'status' => false,
                        'errors' => [
                            'code' => $response->error,
                            'message' => $response->error_description,
                            'severity' => null,
                            'ticketID' => null
                        ]
                    ];
                } else {

                    $resp = [
                        'status' => false,
                        'errors' => [
                            'code' => "Undefined",
                            'message' => "Undefined",
                            'severity' => null,
                            'ticketID' => null
                        ]
                    ];
                }
            }
        }

        $respMerged = array_merge($resp, [
            'token' => $token,
            'signature' => $signature,
            'response' => $response,
            'responseInfo' => $responseInfo,
            'cURLError' => $error
        ]);

        return $respMerged;
    }

    private function loginRequest($url = "", $payload = [], $timeout = 0, $method = "POST", $withSign = "N")
    {

        return $this->_requestBody($url, $payload, $timeout, $method, $withSign);
    }

    private function completeRequest($url = "", $payload = [], $timeout = 0, $method = "POST", $withSign = "Y", $secret = "", $token = "")
    {

        return $this->_requestBody($url, $payload, $timeout, $method, $withSign, $secret, $token);
    }
}
