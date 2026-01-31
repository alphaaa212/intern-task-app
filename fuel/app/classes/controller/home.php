<?php
class Controller_Home extends Controller
{
    public function action_index()
    {
        return View::forge('home/index');
    }
}