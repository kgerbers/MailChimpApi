<?php

namespace App\Http\Helpers\MailChimpApi;

use App\Http\Helpers\MailChimpApi\Exception\MailchimpBadRequestException;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Psr7\Response;
use Log;

class ApiClient
{
    /** @var Response $response */
    protected $response;
    protected $errors = [];
    protected static $apiCount = 0;

    public function __construct ($uri, $params, $method)
    {
        $api_key = env('MAILCHIMP_API_KEY');

        $client = new Client(['base_uri' => 'https://us4.api.mailchimp.com/3.0/']);

        $params = ['json' => $params];

        Log::channel('api')->info('[' . ++self::$apiCount . '/100] Calling ' . $method . ':' . $uri . ' with params: ' . json_encode($params));

        $params = array_merge($params, ['auth' => ['portal', $api_key]]);

        try {
            $this->response = $client->request($method, $uri, $params);
        }
        catch (ClientException $ce) {
            if ($ce->getCode() === 400) {
                Log::channel('api')->error('[' . $ce->getCode() . '] response: ' . (string) $ce->getMessage());
                // There is a mistake in the code, so returning it:
                throw new MailchimpBadRequestException($ce->getMessage(), $ce->getCode());
            }
            $this->errors[] = [
                'code' => $ce->getCode(),
                'request' => ['URI: ' => $method . ' ' . $uri, 'params: ' => $params],
                'ExceptionClass' => $ce->getFile(),
                'apiResponse' => json_decode((string) $ce->getResponse()->getBody()),
                'apiCount' => self::$apiCount,
            ];

            Log::channel('api')->debug('[' . $ce->getCode() . '] response: ' . (string) $ce->getResponse()->getBody());

            return NULL;
        }
    }

    public function getLastErrors ()
    {
        return $this->errors;
    }

    public function __toString ()
    {
        return (string) $this->getResponse();
    }

    public function getResponse ()
    {
        if ($this->response && trim((string) $this->response->getBody()) === '') {
            return '';
        }

        if ($this->response) {
            return json_decode((string) $this->response->getBody());
        }

        return NULL;
    }
}
