<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

define('BASEURL','https://ote-api.swhosting.com/v1/');
define('TOKEN','YOUR-BEARER-TOKEN');

// GET /domains/{domain}/available
function available($domain = null)
{
    if (is_null($domain) || trim($domain) == '') {
        throw new Exception("Domain is required");
    }

    $endpoint = 'domains/'.$domain.'/available';
    return makeRequest($endpoint);
}

// POST /v1/domains/{domain}/register
function register($domain, $data)
{
    if (is_null($domain) || trim($domain) == '') {
        throw new Exception("Domain is required");
    }

    $endpoint = 'domains/'.$domain.'/register';
    return makeRequest($endpoint, 'POST', $data);
}

// Manage REST requests with the Bearer Token
function makeRequest($endpoint, $method = 'GET', $data = null)
{
    $url = BASEURL . $endpoint;

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Authorization: Bearer '.TOKEN,
            'Content-Type: application/json',
        ));
    if ($method != 'HEAD' && $method != 'GET') {
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    }
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
    curl_setopt($ch, CURLOPT_TIMEOUT, 100);

    $responseAPI = curl_exec($ch);
    if (curl_errno($ch)) {
       die('Connection Error: ' . curl_errno($ch) . ' - ' . curl_error($ch));
    }

    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $response = json_decode($responseAPI);

    if ($http_code < 200 || $http_code >= 300) {
        $message = (isset($response->message)) ? $response->message : '';
        if (is_object($message)) {
            $message = implode(" ",(array)$message);
        }

        die('Response Error: ' . $http_code . ' - ' . $message);
    }

    return $response;
}

// Main
try {

    // Details for domain registration
    $domain  = 'swhostingapitest.com';
    $contact = [
        'nombre' => 'Juan',
        'apellidos' => 'Palomo',
        'empresa' => 'Mi gran empresa',
        'direccion' => 'C/Rue del Perceve, 13',
        'email' => 'user@example.com',
        'nif' => '40404040D',
        'poblacion' => 'Hospitalet',
        'provincia' => 'Barcelona',
        'cpostal' => '08080',
        'pais' => 'Spain',
        'telefono' => '034931234567',
        'fax' => '34937654321'
    ];
    $data = [
        'contactRegistrant' => $contact,
        'contactAdmin' => $contact,
        'contactTech' => $contact,
        'nameservers' => ['ns1.swregistrar.com','ns2.swregistrar.com'],
        'renewAuto' => false
    ];

    // Check domain availability
    $response = available($domain);
    if ($response->available) {
        // Register domain
        $result = register($domain,$data);

    } else {
        $result = 'Sorry, the domain '.$response->domain.' is not available';
    }

    echo $result;

} catch (Exception $e) {
    echo 'ERROR: '.$e->getMessage();
}
