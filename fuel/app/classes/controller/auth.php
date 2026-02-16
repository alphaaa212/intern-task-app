<?php

class Controller_Auth extends Controller
{
    // 登録画面表示(GET)
    public function action_register()
    {
      // // セッションからエラーメッセージを取得（なければ空配列）
      // $flash_errors = Session::get_flash('error',[]);
      // $errors = [];

      // // Validationオブジェクトが渡ってきた場合
      // if ($flash_errors instanceof \Validation) {
      //   // フィールド名をキーにして、エラーメッセージを格納する
      //   foreach($flash_errors->error()as $field => $error){
      //     // $error->get_message() で設定したメッセージ文字列を取得
      //     $errors[$field] = $error->get_message();
      //   }
      // }

      // // 文字列または配列で直接エラーが渡ってきた場合
      // elseif (!empty($flash_errors)) {
      //   $errors = is_array($flash_errors) ? $flash_errors : ['general' => $flash_errors];
      // }

      // // View::forgeで'views/auth/register.php'を読み込み、Responseオブジェクトとして返却（表示）
      // // 2. View側がループ（foreach）を期待しているため、文字列の場合は配列に包む
      return Response::forge(View::forge('auth/register', ['errors' => []]));
    }

    // 登録処理(POST)
    public function post_register()
    {
      // 登録処理時もトークンチェックを追加
      if (!Security::check_token()) {
        return Response::forge(View::forge('auth/register', ['errors' => ['CSRF' => 'セッションが切れました。']]));
      }

      $errors = [];
      $val = Validation::forge();

      // 入力チェックのルール
      $val->add('username', 'ユーザー名')->add_rule('required');
      $val->add('email', 'メールアドレス')->add_rule('required')->add_rule('valid_email');;
      $val->add('password', 'パスワード')->add_rule('required')->add_rule('min_length', 8);
      $val->add('password_confirm', 'パスワード（確認）')->add_rule('required')->add_rule('match_field', 'password'); // パスワード欄と一致するかチェック

      $val->set_message('min_length', ':labelは8文字以上で入力してください。');
      $val->set_message('valid_email', '正しいメールアドレスの形式で入力してください。');
      $val->set_message('match_field', ':labelが:param:1と一致しません。');

      // バリデーションを実行し、失敗した（false）場合の処理。
      if (!$val->run()) {
        foreach($val->error() as $field => $error) {
          $errors[$field] = $error->get_message();
        }
      }

      // 2. 重複チェックを「必ず」試みる
      // ただし、パスワード未入力などの重大な不備がある状態でAuthを叩くと
      // 別のエラーが出るため、最低限必要な項目が入力されている時だけ実行
      if (Input::post('username') && Input::post('password') && Input::post('email')) {
        try {
            Auth::create_user(
                Input::post('username'),
                Input::post('password'),
                Input::post('email')
            );
            // 成功かつバリデーションエラーもなければログイン画面へ
            if (empty($errors)) {
                Session::set_flash('success', 'ユーザー登録が完了しました。');
                return Response::redirect('auth/login');
            }
        } catch (\Auth\SimpleUserUpdateException $e) {
            $code = $e->getCode();
            // 重複エラーメッセージを追加
            $errors['database'] = ($code == 2 || $code == 3) 
                ? "そのユーザー名またはメールアドレスは既に使われています。" 
                : "登録処理中にエラーが発生しました。";
            
            if ($code != 2 && $code != 3) {
                Log::error("User registration failed: Code {$code}, Message: " . $e->getMessage());
            }
        }
      }

      // 3. バリデーションエラーと重複エラーをすべて持って再表示
      return Response::forge(View::forge('auth/register', ['errors' => $errors]));
    }

    // ログイン画面表示(GET)
    public function action_login()
    {
        // ログイン済みならトップへ飛ばす（任意）
        if (Auth::check()) {
            Response::redirect('ideas/index');
        }

        // views/auth/login.php を表示
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
      if (Auth::login(Input::post('username'), Input::post('password')))
      {
        // ログイン時に強制的にセッションIDを変更する
        Session::instance()->rotate();
        return Response::redirect('/ideas/index');
      }

      // 認証失敗：エラーメッセージと共にログイン画面を再表示する。
      return Response::forge(View::forge('auth/login', ['error' => 'ユーザー名またはパスワードが正しくありません。']));
    }

  /**
   * ログアウト
   */
    public function action_logout()
    {
      // Authパッケージのログアウト処理を実行（セッションの破棄など）。
      Auth::logout();
      // ログアウト時もセッションを完全に破棄することを推奨
      Session::destroy();
      return Response::redirect('auth/login');
    }
}