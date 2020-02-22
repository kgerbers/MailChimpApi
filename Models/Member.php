<?php

namespace App\Http\Helpers\MailChimpApi\Models;

use App\Http\Helpers\MailChimpApi\ApiClient;
use App\Http\Helpers\MailChimpApi\ApiRouter;
use App\Http\Helpers\MailChimpApi\Exception\MailchimpRoutingException;

class Member
{

    public $list_id;
    public $email_address;
    public $email_type;
    public $email_status;
    public $merge_fields;
    public $interests;
    public $language;
    public $vip = false;
    public $location;
    public $marketing_permissions;
    public $ip_signup;
    public $timestamp_signup;
    public $ip_opt;
    public $timestamp_opt;
    public $tags;

    public function __construct ($list_id, $params = [])
    {
        $this->list_id = $list_id;
        $params = (array) $params;
        foreach ($params as $key => $value) {
            if (property_exists($this, $key)) {
                $this->$key = $value;
            }
        }
    }

    public function get ()
    {
        if ($hash = $this->getSubscriberHash()) {
            $uri = (new ApiRouter())->prepareURI('lists-member', [
                'list_id' => $this->list_id,
                'subscriber_hash' => $hash,
            ]);

            return new self($this->list_id, (new ApiClient($uri, [], 'GET'))->getResponse());
        }

        $uri = (new ApiRouter())->prepareURI('lists', [
            'list_id' => $this->list_id,
        ]);

        $response = (new ApiClient($uri, [], 'GET'))->getResponse();

        if ($response->members) {
            foreach ($response->members as $member) {
                $members[] = new self($this->list_id, $member);
            }
        }

        return $members;
    }

    public function save ($fields = [])
    {
        $errors = false;
        $uri = (new ApiRouter())->prepareURI('lists', [
            'list_id' => $this->list_id,
        ]);

        $required = ['email_address', 'status'];

        if ($errors) {
            throw new MailchimpRoutingException('following mandatory parameters are missing: ' . implode(',', $errors));
        }

        return (new ApiClient($uri, $fields, 'POST'))->getResponse();
    }

    public function getSubscriberHash ()
    {
        $emailAddress = trim($this->email_address);

        return ($emailAddress) ? md5(mb_strtolower($emailAddress)) : NULL;
    }
}
