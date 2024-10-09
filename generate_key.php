<?php

use Defuse\Crypto\Key;

require_once 'vendor/autoload.php';

var_dump(Key::createNewRandomKey()->saveToAsciiSafeString());
