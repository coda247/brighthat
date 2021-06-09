<?php
declare(strict_types=1);
namespace  App\Template\Builder;

class Code 
{
    public static function generate($limit = 6){
        return substr(sha1("".time()), 0, $limit);
    }
}
