<?php

namespace App\Http\Helpers\MailChimpApi;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;

class ApiClient
{
    /** @var Response $response */
    protected $response;

    public function __construct ($uri, $params, $method)
    {
        $api_key = env('MAILCHIMP_API_KEY');
        $client = new Client(['base_uri' => 'https://us4.api.mailchimp.com/3.0/']);

        $params = ['json' => $params];
        $params = array_merge($params, ['auth' => ['portal', $api_key]]);

        $this->response = $client->request($method, $uri, $params);
    }

    public function __toString ()
    {
        return (string) $this->getResponse();
    }

    public function getResponse ()
    {
        if (trim((string) $this->response->getBody()) === '') {
            return '';
        }

        return \GuzzleHttp\json_decode((string) $this->response->getBody());
    }
}
