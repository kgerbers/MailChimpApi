<?php
// List is a little different in naming because List/list is a php reserved keyword
use Kgerbers\MailChimpApi\Models\Lists;

$lists = new Lists();

// search for key = value
$lists->where('name', '=', 'Audience name');

// get first item from the results
$lists->first();
