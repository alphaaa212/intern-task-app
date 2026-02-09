<?php

/**
 * 全コントローラの基底クラス
 * 【修正ポイント】
 * ・独自セッション管理から Auth クラスによる管理へ統合（バグ修正）
 * ・インデントを半角2文字に修正
 * ・早期リターンによるロジックの整理
 */
class Controller_Base extends Controller_Template 
{
  // 現在ログイン中のユーザー情報を保持
    protected $current_user = null;

    public function before()
    {
        parent::before();

    // 1. Authパッケージからログイン状態を確認
    if (\Auth::check()) {
      // ユーザー情報をオブジェクト形式で取得（Model_User相当のデータをセット）
      // SimpleAuthの場合、get_screen_nameやget_email等が使えますが、
      // 規約に合わせプロパティとして扱いやすい形にします
      $this->current_user = (object) [
        'id'       => \Auth::get_user_id()[1],
        'username' => \Auth::get_screen_name(),
      ];
      
      // 全Viewで共通して $user を使えるようにグローバルセット
      $this->template->set_global('user', $this->current_user);
        }

    // 2. 認証ガード：未ログイン時のリダイレクト処理
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