<?php

require 'vendor/autoload.php';

require 'bootstrap.php';

use App\Controllers\{Router, Request};

Router::load('routes.php')
    ->direct(Request::uri(), Request::method());
