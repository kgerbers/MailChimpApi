<?php

namespace App\Http\Helpers\MailChimpApi;

use App\Http\Helpers\MailChimpApi\Exception\MailchimpRoutingException;

class ApiRouter
{
    public function prepareURI ($uri_identifier, $options = [])
    {
        $uri = $uri_identifier;

        $possibleParams = [
            'list_id',
            'subscriber_hash',
        ];

        foreach ($possibleParams as $param) {
            $needle = '{' . $param . '}';
            if (strpos($uri, $needle) !== false) {
                if (!array_key_exists($param, $options)) {
                    throw new MailchimpRoutingException('Parameter ' . $param . ' is required for uri ' . $uri);
                }
                $uri = str_replace($needle, $options[$param], $uri);
            }
        }

        return $uri;
    }

}
