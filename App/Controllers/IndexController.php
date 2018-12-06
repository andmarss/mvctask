<?php
/**
 * Created by PhpStorm.
 * User: delux
 * Date: 26.11.2018
 * Time: 15:18
 */

namespace App\Controllers;


class IndexController
{
    public function page404()
    {
        return redirect(404);
    }
}