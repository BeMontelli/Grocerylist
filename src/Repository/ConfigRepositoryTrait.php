<?php

namespace App\Repository;

trait ConfigRepositoryTrait
{
    protected static $perPage = 20;
    protected static $perPageGrid = 12;

    public static function getPerPage()
    {
        return self::$perPage;
    }
    public static function getPerPageGrid()
    {
        return self::$perPageGrid;
    }
}