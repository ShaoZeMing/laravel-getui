<?php

namespace Shaozeming\GeTui;



use Illuminate\Support\Facades\Facade as GeTuiFacade;


/**
 * Class Facade
 * @package Shaozeming\GeTui
 */
class Facade extends GeTuiFacade
{
    public static function getFacadeAccessor()
    {
        return GeTuiService::class;
    }
}