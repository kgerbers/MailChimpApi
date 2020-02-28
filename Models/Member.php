<?php

namespace App\Http\Helpers\MailChimpApi\Models;

use App\Http\Helpers\MailChimpApi\ApiClient;
use App\Http\Helpers\MailChimpApi\ApiRouter;

class Member extends BaseModel
{
    public const STATUS_SUBSCRIBED = 'subscribed';
    public const STATUS_UNSUBSCRIBED = 'unsubscribed';
    public const STATUS_CLEANED = 'cleaned';
    public const STATUS_PENDING = 'pending';
    public const STATUS_TRANSACTIONAL = 'transactional';

    /** @var string $email_address */
    public $email_address;

    /** @var string $email_type */
    protected $email_type;

    /** @var string $email_status */
    protected $email_status;

    /** @var array $merge_fields */
    public $merge_fields = [];

    /** @var array $interests */
    protected $interests;

    /** @var string $language */
    public $language;

    /** @var boolean $vip */
    public $vip = false;

    /** @var array $location */
    protected $location;

    /** @var string $marketing_permissions */
    protected $marketing_permissions;

    /** @var string $ip_signup */
    protected $ip_signup;

    /** @var string $timestamp_signup */
    protected $timestamp_signup;

    /** @var string $ip_opt */
    protected $ip_opt;

    /** @var string $timestamp_opt */
    protected $timestamp_opt;

    /** @var Tags $tags */
    protected $tags;

    /** @var string $status */
    public $status;

    /** @var string $subscriber_hash */
    protected $subscriber_hash;

    protected $required = ['email_address', 'status', 'merge_fields'];

    protected $routes = [
        'get-all' => 'lists/{list_id}/members',
        'get-one' => 'lists/{list_id}/members/{subscriber_hash}',
        'post' => 'lists/{list_id}/members',
        'put' => 'lists/{list_id}/members/{subscriber_hash}',
        'patch' => 'lists/{list_id}/members/{subscriber_hash}',
        'delete' => 'lists/{list_id}/members/{subscriber_hash}',
        'force-delete' => 'lists/{list_id}/members/{subscriber_hash}/actions/delete-permanent',
    ];

    public function __construct ($list = NULL, $params = [])
    {
        parent::__construct($list, $params);

        if (!$this->status) {
            $this->status = Member::STATUS_SUBSCRIBED;
        }

        if (!$this->merge_fields) {
            $fname = explode('@', $this->email_address);
            $this->merge_fields = [
                'FNAME' => reset($fname),
                'LNAME' => '',
            ];
        }

        if (!$this->tags instanceof Tags) {
            $this->tags = new Tags($this->list, $this->getSubscriberHash());
        }
    }

    public function getSubscriberHash ($emailAddress = NULL)
    {
        $emailAddress = $emailAddress ?? trim($this->email_address);

        return $this->subscriber_hash = ($emailAddress) ? md5(mb_strtolower($emailAddress)) : NULL;
    }

    public function get ()
    {
        if ($hash = $this->getSubscriberHash()) {
            $uri = (new ApiRouter())->prepareURI($this->routes['get-one'], [
                'list_id' => $this->list,
                'subscriber_hash' => $hash,
            ]);

            $member = (new ApiClient($uri, [], 'GET'))->getResponse();

            if ($member) {
                $member->new = false;
                $this->new = false;
            }
            if ($member instanceof Member) {
                $member->tags = new Tags($this->list, $this->subscriber_hash, $member->tags);
                $member->list = $this->list;
                $member->new = false;

                // overwrite current class data with fetched data
                if ($member !== $this) {
                    $this->__construct($this->list, $member);
                    $this->new = false;
                }

                return $this;
            }

            return NULL;
        }

        $uri = (new ApiRouter())->prepareURI($this->routes['get-all'], [
            'list_id' => $this->list,
        ]);
        $members = [];
        $response = (new ApiClient($uri, [], 'GET'))->getResponse();

        if ($response && $response->members) {
            foreach ($response->members as $member) {
                $member->tags = new Tags($member->list, $member->subscriber_hash, $member->tags);
                $member->new = false;
                $members[] = new self($this->list, $member);
            }
        }

        return $members;
    }

    public function has ($emailAddress = NULL)
    {
        $uri = (new ApiRouter())->prepareURI($this->routes['get-one'], [
            'list_id' => $this->list,
            'subscriber_hash' => $this->getSubscriberHash($emailAddress),
        ]);

        return (new ApiClient($uri, ['id'], 'GET'))->getResponse() ? true : false;
    }

    public function save ($fields = [], $method = 'put')
    {
        $this->routes['apiCallUrl'] = (new ApiRouter())->prepareURI($this->routes[$method], [
            'list_id' => $this->list,
            'subscriber_hash' => $this->getSubscriberHash(),
        ]);

        // will stop with exception if failing
        parent::save($fields, $method);

        // write tags for member after saving member
        if ($this->tags && $this->tags instanceof Tags) {
            $this->tags->save();
        }
    }

    public function softDelete ($method = 'delete')
    {
        $response = (new ApiRouter())->prepareURI($this->routes[$method], [
            'list_id' => $this->list,
            'subscriber_hash' => $this->subscriber_hash,
        ]);

        return (new ApiClient($response, [], $method))->getResponse();
    }

    public function forceDelete ($method = 'delete')
    {
        $response = (new ApiRouter())->prepareURI($this->routes['force-delete'], [
            'list_id' => $this->list,
            'subscriber_hash' => $this->subscriber_hash,
        ]);

        return (new ApiClient($response, [], 'post'))->getResponse();
    }

    // Relation objects
    public function tags ()
    {
        return $this->tags;
    }

}
