<?php

class Controller_Auth extends Controller
{
    // 登録画面表示
    public function action_register()
    {
        $data = array();
        $data['errors'] = array();

        if (Input::method() == 'POST') {
    // 1. 必須チェックなどの「絶対に動く基本ルール」だけ書く
    $val = Validation::forge();
    $val->add('username', 'ユーザー名')->add_rule('required');
    $val->add('email', 'メールアドレス')->add_rule('required');
    $val->add('password', 'パスワード')->add_rule('required')->add_rule('min_length', 8);


    $val->set_message('min_length', ':labelは8文字以上で入力してください。');

    if ($val->run()) {
        try {
            // 2. とりあえず保存を試みる
            $user = Model_User::forge([
                'username'      => Input::post('username'),
                'email'         => Input::post('email'),
                'password_hash' => password_hash(Input::post('password'), PASSWORD_DEFAULT),
            ]);
            $user->save();
            return Response::redirect('auth/login');

        } catch (\Database_Exception $e) {
            // 3. もしDBで「重複エラー(1062)」が起きたら、ここで捕まえる
            if ($e->getCode() == 1062) {
                // エラーメッセージを画面に渡す
                $data['errors']['database'] = "そのユーザー名またはメールアドレスは既に使われています。";
            } else {
                $data['errors']['database'] = "登録に失敗しました。";
            }
        }
    } else {
        $data['errors'] = $val->error();
    }
}

        // 表示処理（GETの時も、バリデーション失敗の時もここを通る）
        return Response::forge(View::forge('auth/register', $data));
    }

    public function action_login()
    {
        $data = array();
        
        if (Input::method() == 'POST'){
            if(! Security::check_token()){
                $data['error'] = 'ページが無効です。もう一度やり直してください。';
            }else {
                $username = Input::post('username');
                $password = Input::post('password');

                // DBクラス（またはORM）を使ってユーザーを取得
                $user = Model_User::query()
                    ->where('username', '=', $username)
                    ->get_one();

                if ($user && password_verify($password, $user->password_hash)){
                    // パスワード一致：セッション保存
                    Session::set('user_id', $user->id);
                    Session::set('username', $user->username);
                
                    // ログイン後のTOPへ（例: dashboard）
                    return Response::redirect('ideas/index');
                } else {
                    $data['error'] = 'ユーザー名またはパスワードが正しくありません。';
                }
            }
        }

        return Response::forge(View::forge('auth/login', $data));
    }

    public function action_logout()
    {
        Session::destroy();
        return Response::redirect('auth/login');
    }
}
?>