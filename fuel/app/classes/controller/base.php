<?php

/**
 * 全コントローラの基底クラス
 */
class Controller_Base extends Controller_Template 
{
  // 現在ログイン中のユーザー情報を保持
    protected $current_user = null;

    public function before()
    {
        parent::before();

    // Authパッケージからユーザー情報を一度に取得
    if (\Auth::check()) {
      $user_id = \Auth::get_user_id()[1];
      $this->current_user = (object) [
        'id'       => $user_id,
        'username' => \Auth::get_screen_name(),
      ];
      
      // 全Viewで共通して $user を使えるようにグローバルセット
      $this->template->set_global('user', $this->current_user);
        }

    // 認証ガード：未ログイン時のリダイレクト処理
    $this->auth_guard();
  }

  /**
   * 未ログインユーザーのアクセス制限
   */
  private function auth_guard()
  {
    $current_controller = \Request::active()->controller;

    // ログイン・登録画面（Controller_Auth）以外へのアクセスで未ログインならリダイレクト
        if ($current_controller !== 'Controller_Auth' && !$this->current_user) {
      \Response::redirect('auth/login');
        }
    }
}