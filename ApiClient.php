<?php

namespace Kgerbers\MailChimpApi;

use Kgerbers\MailChimpApi\Exception\MailchimpBadRequestException;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Psr7\Response;
use Psr\Log\LoggerAwareTrait;

class ApiClient
{
    /** @var Response $response */
    protected $response;
    protected $errors = [];
    protected static $apiCount = 0;

    use LoggerAwareTrait;

    public function __construct ($uri, $params, $method)
    {
        $api_key = env('MAILCHIMP_API_KEY');

        $client = new Client(['base_uri' => 'https://us4.api.mailchimp.com/3.0/']);

        $params = ['json' => $params];

        $this->logger->info('[' . ++self::$apiCount . '/100] Calling ' . $method . ':' . $uri . ' with params: ' . json_encode($params));

        $params = array_merge($params, ['auth' => ['portal', $api_key]]);

        try {
            $this->response = $client->request($method, $uri, $params);
        }
        catch (ClientException $ce) {
            if ($ce->getCode() === 400) {
                $this->logger->error('[' . $ce->getCode() . '] response: ' . (string) $ce->getMessage());
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

            $this->logger->debug('[' . $ce->getCode() . '] response: ' . (string) $ce->getResponse()->getBody());

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
