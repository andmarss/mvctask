<?php

namespace Middleware;

class CsrfMiddleware
{
    protected $verify = [];

    /**
     * @param $request
     * @throws \Exception
     *
     * Проверяет входящий запрос на наличие токена
     */

    public function handle($request)
    {
        if(!$this->isVerify($request->uri()) && !$request->session()->token()->check($request->token)) {
            throw new \Exception('Ошибка запроса');
        }
    }

    /**
     * @param string $uri
     * @return bool
     *
     * Проверяет, разрешенный ли это запрос
     */

    protected function isVerify($uri = '')
    {
        $tokensRouts = collect($this->verify)->map(function ($route){

            return trim( parse_url( $route, PHP_URL_PATH), '/' );

        })->get();

        if(!in_array($uri, $tokensRouts)) {
            foreach ($tokensRouts as $tokenRoute) {
                $tokenRoute = preg_replace('/\//', '\/', trim($tokenRoute));

                if(preg_match("/{$tokenRoute}/", $uri)) {
                    return true;
                } else {
                    continue;
                }
            }

            return false;
        } else {
            return true;
        }
    }
}