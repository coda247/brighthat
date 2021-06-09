<?php
declare(strict_types=1);
namespace  App\Template\Builder;
class Builder
{
    public static function build($uri_name = null, $data = []){
        $loader = new \Twig\Loader\FilesystemLoader(__DIR__."/views");
        $twig = new \Twig\Environment($loader, []);
        return $twig->render($uri_name, $data);
    }
}


