<?php

/**
 * Массив POST-запросов, в которых можно НЕ использовать token'ы при отправке запроса
 */

namespace Middleware;

class VerifyCsrfToken extends CsrfMiddleware
{
    protected $verify = [];
}