<?php

require_once __DIR__.'/_bootstrap.php';
dc_json(array('loggedIn' => true, 'userId' => (int) $user->id));

