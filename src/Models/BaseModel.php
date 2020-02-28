<?php

namespace Kgerbers\MailChimpApi\Models;

use Kgerbers\MailChimpApi\ApiClient;
use Kgerbers\MailChimpApi\Exception\MailchimpRoutingException;
use ReflectionObject;
use ReflectionProperty;

class BaseModel
{
    protected $new = true;
    protected $required = [];
    protected $fillable = [];

    /** @var string $list_id */
    protected $list;

    protected $routes = [
        'get' => NULL,
        'post' => NULL,
        'put' => NULL,
        'patch' => NULL,
        'apiCallUrl' => NULL,
    ];

    public function __construct ($list = NULL, $params = [])
    {
        if (!$this instanceof Lists) {
            $this->list = new Lists($list);
        } else {
            $this->list = $list;
        }

        $params = (array) $params;
        foreach ($params as $key => $value) {
            if (property_exists($this, $key)) {
                $this->$key = $value;
            }
        }
    }

    public function first ()
    {
        $items = $this->get();
        if (!$items instanceof $this) {
            return reset($items);
        }

        return $items;
    }

    public function where ($key, $operator, $value)
    {
        $items = $this->get();

        if (array_key_exists($key, get_object_vars($this))) {
            foreach ($items as $item) {
                if ($item->$key === $value) {
                    return $item;
                }
            }
        }

        return $this;
    }

    public function save ($fields = [], $method = 'put')
    {
        $empty_fields = [];

        foreach ($this->required as $required_field) {
            if (empty($this->$required_field)) {
                $empty_fields[] = $required_field;
            }
        }

        if ($empty_fields !== []) {
            throw new MailchimpRoutingException('Following mandatory parameters are missing: ' . implode(',', $empty_fields));
        }

        // Only send public properties back to the api, protected and private are only for getting / class workings
        $reflection = new ReflectionObject($this);
        $properties = $reflection->getProperties(ReflectionProperty::IS_PUBLIC);

        foreach ($properties as $property) {
            $name = $property->getName();
            $value = $this->$name; //$property->getValue($this);
            if (in_array($name, $this->fillable)) {
                $fields[$name] = $value;
            }
        }

        return (new ApiClient($this->routes['apiCallUrl'], $fields, $method))->getResponse();
    }

    public function isNew ()
    {
        return $this->new;
    }

}
