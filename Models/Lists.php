<?php

namespace Kgerbers\MailChimpApi\Models;

use Kgerbers\MailChimpApi\ApiClient;
use Kgerbers\MailChimpApi\ApiRouter;

class Lists extends BaseModel
{
    public const VISIBILITY_PRIV = 'priv';
    public const VISIBILITY_PUB = 'pub';

    public const LIST_RATING_0 = 0;
    public const LIST_RATING_1 = 1;
    public const LIST_RATING_2 = 2;
    public const LIST_RATING_3 = 3;
    public const LIST_RATING_4 = 4;
    public const LIST_RATING_5 = 5;

    /** @var string $id */
    public $id;

    /** @var string $list_id */
    public $list_id;

    /** @var int $web_id */
    public $web_id;

    /** @var string $name */
    public $name;

    /** @var string $contact */
    public $contact;

    /** @var string $permission_reminder */
    public $permission_reminder;

    /** @var boolean $use_archive_bar */
    public $use_archive_bar;

    /** @var array $campaign_defaults */
    public $campaign_defaults;

    /** @var string $notify_on_subscribe */
    public $notify_on_subscribe;

    /** @var string $notify_on_unsubscribe */
    public $notify_on_unsubscribe;

    /** @var string $date_created (ISO 8601 = YYYY-MM-DDThh:mm:ss) */
    public $date_created;

    /** @var integer $list_rating (0-5) */
    public $list_rating;

    /** @var boolean $email_type_option */
    public $email_type_option;

    /** @var string $subscribe_url_short */
    public $subscribe_url_short;

    /** @var string $subscribe_url_long */
    public $subscribe_url_long;

    /** @var string $beamer_address */
    public $beamer_address;

    /** @var string $visibility (pub - priv) */
    public $visibility;

    /** @var boolean $double_optin */
    public $double_optin = false;

    /** @var string $has_welcome */
    public $has_welcome;

    /** @var boolean $marketing_permissions */
    public $marketing_permissions;

    /** @var array $modules */
    public $modules;

    /** @var object $stats */
    public $stats = [];

    protected $fillable = ['name'];

    protected $routes = [
        'get' => 'lists',
        'get-one' => 'lists/{list_id}',
    ];

    public function get ()
    {
        if ($this->list) {
            $uri = (new ApiRouter())->prepareURI(
                $this->routes['get-one'],
                [
                    'list_id' => $this->list,
                ]);

            $lists = (new ApiClient($uri, [], 'GET'))->getResponse();

            // overwrite current class data with fetched data
            $this->__construct($this->list, $lists);

            return $this;
        }

        $uri = (new ApiRouter())->prepareURI($this->routes['get'], [

        ]);

        $response = (new ApiClient($uri, [], 'GET'))->getResponse();

        if ($response && $response->lists) {
            foreach ($response->lists as $list) {
                $members[] = new self($this, $list);
            }
        }

        return $members ?? NULL;
    }

    public function save ($fields = [], $method = 'put')
    {
        $this->routes['apiCallUrl'] = (new ApiRouter())->prepareURI($this->routes[$method], [
            'list_id' => $this,
            'subscriber_hash' => $this->subscriber_hash,
        ]);

        return parent::save($fields, $method);
    }

    public function __toString ()
    {
        return $this->id ?? '';
    }

    public function serialize ()
    {
        return json_encode($this);
    }

}
