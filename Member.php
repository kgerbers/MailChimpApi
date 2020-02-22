<?php

namespace App\Http\Helpers\MailChimpApi;

use App\Http\Helpers\MailChimpApi\Exception\MailchimpRoutingException;

class Member
{

    protected $subscriber;
    protected $listId;

    public function update ($fields = [])
    {
        $uri = (new ApiRouter())->prepareURI('lists-member', [
            'list_id' => $this->listId,
            'subscriber_hash' => $this->getSubscriberHash(),
        ]);

        return (new ApiClient($uri, $fields, 'PATCH'))->getResponse();
    }

    public function create ($fields = [])
    {
        $errors = false;
        $uri = (new ApiRouter())->prepareURI('lists', [
            'list_id' => $this->listId,
        ]);

        $member = new Models\Member();
        $member->
        $required = ['email_address', 'status'];

        if ($errors) {
            throw new MailchimpRoutingException('following mandatory parameters are missing: ' . implode(',', $errors));
        }

        return (new ApiClient($uri, $fields, 'POST'))->getResponse();
    }

    public function setTags ($fields = [])
    {
        $uri = (new ApiRouter())->prepareURI('lists-member-tags', [
            'list_id' => $this->listId,
            'subscriber_hash' => $this->getSubscriberHash(),
        ]);

        $fields = ['tags' => $fields];

        return (new ApiClient($uri, $fields, 'POST'))->getResponse();
    }

    public function getTags ($fields = [])
    {
        $uri = (new ApiRouter())->prepareURI('lists-member-tags', [
            'list_id' => $this->listId,
            'subscriber_hash' => $this->getSubscriberHash(),
        ]);

        return (new ApiClient($uri, $fields, 'GET'))->getResponse();
    }

    public $allowed_statusses = ['subscribed', 'unsubscribed', 'cleaned', 'pending', 'transactional', 'archived'];

    protected function memberStatusIsValid ($fields): bool
    {
        if (!isset($member['status'])) {
            return false;
        }

        return in_array($fields['status'], $this->allowed_statusses);
    }

    public function __construct ($listId = NULL, $subscriber = NULL)
    {
        if ($subscriber) {
            $this->setSubscriber($subscriber);
        }
        if ($listId) {
            $this->setListId($listId);
        }
    }

    public function setSubscriber (string $subscriber)
    {
        $this->subscriber = $subscriber;

        return $this;
    }

    public function setListId (string $listId)
    {
        $this->listId = $listId;

        return $this;
    }

    public function getSubscriberHash ()
    {
        return md5(mb_strtolower(trim($this->subscriber)));
    }
}
