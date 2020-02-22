<?php

namespace App\Http\Helpers\MailChimpApi;

use App\Http\Helpers\MailChimpApi\Exception\MailchimpRoutingException;

class ApiRouter
{
    protected $uris = [
        'lists' => 'lists/{list_id}/members',
        'lists-member' => 'lists/{list_id}/members/{subscriber_hash}',
        'lists-member-tags' => 'lists/{list_id}/members/{subscriber_hash}/tags',
    ];

    public function prepareURI ($uri_identifier, $options = [])
    {
        $uri = $this->uris[$uri_identifier] ?? NULL;

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
