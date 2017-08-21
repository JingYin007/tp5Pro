<?php
namespace app\index\controller;


class Index
{
    public function index()
    {
        $build = include APP_PATH.'build.php';
        \think\Build::run($build);
        return '首页';
    }
}
