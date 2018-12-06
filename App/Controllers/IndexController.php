<?php

namespace App\Controllers;


class IndexController
{
    public function page404()
    {
        return redirect(404);
    }
}