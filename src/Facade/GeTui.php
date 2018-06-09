<?php

namespace Shaozeming\GeTui\Facade;



use Illuminate\Support\Facades\Facade;
use Shaozeming\GeTui\GeTuiService;


/**
 * Class Facade
 * @package Shaozeming\GeTui
 */
class GeTui extends Facade
{
    public static function getFacadeAccessor()
    {
        return GeTuiService::class;
    }
}