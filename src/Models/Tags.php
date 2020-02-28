<?php

namespace Kgerbers\MailChimpApi\Models;

use Kgerbers\MailChimpApi\ApiClient;
use Kgerbers\MailChimpApi\ApiRouter;

class Tags extends BaseModel
{
    public const ACTIVE = 'active';
    public const INACTIVE = 'inactive';

    /** @var string $subscriber_hash */
    protected $subscriber_hash;

    /** @var array $tags */
    public $tags = [];

    protected $required = ['tags'];

    protected $routes = [
        'get' => 'lists/{list_id}/members/{subscriber_hash}/tags',
        'post' => 'lists/{list_id}/members/{subscriber_hash}/tags',
    ];

    public function __construct ($list, $subscriber_hash, $params = [])
    {
        $this->list = $list;
        $this->subscriber_hash = $subscriber_hash;

        if ($params) {
            foreach ($params as $id => $tag) {
                $this->add($tag->name, $tag->id);
            }
        }
    }

    public function add ($name, $id = NULL)
    {
        $this->tags[$name] = (object) [
            'id' => $id,
            'name' => $name,
            'status' => self::ACTIVE,
        ];

        return $this;
    }

    public function remove ($name)
    {
        if (array_key_exists($name, $this->tags)) {
            $this->tags[$name]->status = self::INACTIVE;
        }

        return $this;
    }

    public function has ($name)
    {
        return array_key_exists($name, $this->tags);
    }

    public function get ()
    {
        $uri = (new ApiRouter())->prepareURI($this->routes['get'], [
            'list' => $this->list,
            'subscriber_hash' => $hash,
        ]);

        $response = (new ApiClient($uri, [], 'GET'))->getResponse();

        if ($response->tags) {
            $this->new = false;
            foreach ($response->tags as $id => $tag) {
                $this->add($tag->name, $tag->id);
            }
        }

        return $this;
    }

    public function save ($fields = [], $method = 'post')
    {
        $this->routes['apiCallUrl'] = (new ApiRouter())->prepareURI($this->routes[$method], [
            'list_id' => $this->list,
            'subscriber_hash' => $this->subscriber_hash,
        ]);

        $fields['tags'] = $this->tags;

        return parent::save($fields, $method);
    }

}
