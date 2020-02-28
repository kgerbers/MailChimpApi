<?php

namespace App\Http\Helpers\MailChimpApi\Exception;

use Exception;

class MailchimpBadRequestException extends MailchimpException
{
    private $response;

    public function __construct ($message = "", $code = 0, Exception $previous = NULL, $response = NULL)
    {
        parent::__construct($message, $code, $previous);
        $this->response = $response;
    }

    public function response (): array
    {
        return json_decode($this->response, true) ?? [];
    }
}
