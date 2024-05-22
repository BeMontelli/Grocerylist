<?php

namespace App\Repository;

trait ConfigRepositoryTrait
{
    protected static $perPage = 10;

    public static function getPerPage()
    {
        return self::$perPage;
    }
}