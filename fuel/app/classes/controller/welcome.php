<?php
/**
 * アイデア管理コントローラー
 * クライアントからのリクエストを受け付け
 * ideasディレクトリ内のViewを表示する処理を行う。
 */
class Controller_Welcome extends Controller
{
  /**
   * アイデア一覧画面の表示
   */
  public function action_index()
  {
    return Response::forge(View::forge('ideas/index'));
  }

  /**
   * 404エラー画面の表示
   */
  public function action_404()
  {
    return Response::forge(View::forge('welcome/404'), 404);
  }
}