<?php

class Controller_Auth extends Controller
{
    // 登録画面表示(GET)
    public function action_register()
    {
      // View::forgeで'views/auth/register.php'を読み込み、Responseオブジェクトとして返却（表示）
      return Response::forge(View::forge('auth/register'));
    }

    // 登録処理(POST)
    public function post_register()
    {
      // バリデーション（入力チェック）オブジェクトを生成
      $val = Validation::forge();

      // 入力チェックのルールを追加
      $val->add('username', 'ユーザー名')->add_rule('required');
      $val->add('email', 'メールアドレス')->add_rule('required');
      $val->add('password', 'パスワード')->add_rule('required')->add_rule('min_length', 8);
      $val->add('password_confirm', 'パスワード（確認）')->add_rule('required')->add_rule('match_field', 'password'); // パスワード欄と一致するかチェック
      $val->set_message('min_length', ':labelは8文字以上で入力してください。');

    // バリデーションを実行し、失敗した（false）場合の処理。
    if (!$val->run()) {
      return Response::forge(View::forge('auth/register', ['errors' => $val->error()]));
      }

      try {
        // Authパッケージのcreate_userメソッドでデータベースにユーザーを保存します。
        // Input::post('name')でPOST送信された値を取得します。
        Auth::create_user(
          Input::post('username'),
          Input::post('password'),
          Input::post('email')
        );
        // 登録成功後、ログイン画面（/auth/login）へリダイレクトします。
        return Response::redirect('auth/login');
      } catch (\Auth\SimpleUserUpdateException $e) {
        // データベース保存時に例外（エラー）が発生した場合の処理。
            // getCode() == 2 は、FuelPHPのAuthにおいて「重複（既に存在する）」を指すことが多いです。
      $msg = ($e->getCode() == 2) ? "そのユーザー名またはメールアドレスは既に使われています。" : "登録に失敗しました。";

      // エラーメッセージを配列に詰め、登録画面を再表示します。
      return Response::forge(View::forge('auth/register', ['errors' => ['database' => $msg]]));
      }
    }

    // ログイン画面表示(GET)
    public function action_login()
    {
      return Response::forge(View::forge('auth/login'));
    }

    // ログイン処理
    public function post_login()
    {
      // CSRF（サイトを跨いだ不正操作）対策のトークンをチェック。
      // フォームから送られたトークンが、セッションのものと一致するか確認します。
    if (!Security::check_token()) {
      return Response::forge(View::forge('auth/login', ['error' => 'ページが無効です。']));
    }

    // Auth::loginメソッドにユーザー名とパスワードを渡し、認証を試みます。
    // 成功すればtrue、失敗すればfalseが返ります。
    if (Auth::login(Input::post('username'), Input::post('password'))) {
      return Response::redirect('/ideas/index');
    }

    // 認証失敗：エラーメッセージと共にログイン画面を再表示します。
    return Response::forge(View::forge('auth/login', ['error' => 'ユーザー名またはパスワードが正しくありません。']));
    }

  /**
   * ログアウト
   */
    public function action_logout()
    {
    // Authパッケージのログアウト処理を実行（セッションの破棄など）。
    Auth::logout();
        return Response::redirect('auth/login');
    }
}