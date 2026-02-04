<?php
// ネームスペースを使う要件がある場合、ここに namespace Controller; などを検討
class Controller_Base extends Controller_Template 
{

    // プロパティを宣言
    protected $current_user = null;

    public function before()
    {
        parent::before();

        // セッションからユーザーIDを取得
        $user_id = Session::get('user_id');

        // ログインしているなら、データベースからユーザー情報を取得してセット
        if ($user_id) {
            $this->current_user = Model_User::find($user_id);
        }

        // 未ログイン状態で、ログイン/登録画面以外にアクセスしたらリダイレクト
        $current_controller = Request::active()->controller;
        if ($current_controller !== 'Controller_Auth' && !$this->current_user) {
            Response::redirect('auth/login');
        }

        if($this->current_user) {
            $this->template->set_global('user',$this->current_user);
        }
    }
}