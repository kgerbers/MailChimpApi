<?php

use App\Http\Helpers\MailChimpApi\Models\Lists;
use App\Http\Helpers\MailChimpApi\Models\Member;

// First define the list to which the users belong
$lists = new Lists('efbadeb5d2');

$member = new Member($lists->get(), ['email_address' => 'keesgerbers+apicreated2@gmail.com']);

// Execute the api request for finding the member based on the current set properties
$member->get();

// Properties which can be set through the api are public, others are protected and cannot be set/changed directly
$member->language = 'nl';

// where there is a restriction of valid choices the values are defined as constants with the field name as prefix
$member->status = Member::STATUS_SUBSCRIBED;

// fields can be arrays
$member->merge_fields = [
    'FNAME' => 'Firstname',
    'LNAME' => 'Lastname'];

// adds a tag to this member and setting it to active:
$member->tags()->add('Tag-name');

// removes a tag from this member by setting it to in-active:
$member->tags()->remove('Tag-name');

// saves or updates the member
$member->save();

// Softdeletes the user, user can still be seen in mailchimp and re-activated
$member->softDelete();

// Force delete the user, user is fully removed from mailchimp
$member->forceDelete();
