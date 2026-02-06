<?php
class Controller_Ideas extends Controller_Base
{
  /**
   * JSONレスポンスの共通処理
   * @param array $data ブラウザへ返したいデータ本体
   * @param int $status HTTPステータスコード（成功:200、エラー:400など）
   * @return \Response 整形済みのレスポンスオブジェクト
   */
  private function ajax_response($data = [], $status = 200)
  {
    // 次の送信で用いるトークンを生成
    $data['new_token'] = \Security::fetch_token();
      // データをJSON形式に変換し、フロントエンド(JS)でも読み込めるようにする
    return \Response::forge(json_encode($data), $status)
      ->set_header('Content-Type', 'application/json');
      // 返信データのラベルにJSONデータであると伝える
  }

  /**
   * 一覧表示
   * GET: /ideas/index
   */
  public function action_index()
  {
    // Authクラスを使用してログインユーザーIDを取得
    $user_auth_info = \Auth::get_user_id();
    $user_id = $user_auth_info[1];

    // ideaselection.phpで返された$resultを新しく定義した変数$ideasに代入
    $ideas = Model_IdeaSelection::getIdeas_by_user_id($user_id);


    $this->template->title = 'マイネタ一覧';
    $this->template->content = \View::forge('ideas/index');
    
    // データの整形
    $ideas_json = json_encode($ideas);

    // エンコーディングはoff（そのままideas/index.phpに読み込ませる）
    $this->template->content->set('ideas_json', $ideas_json, false);
  }

  /**
   * 新規作成画面
   * GET: /ideas/create
   */
  public function action_create()
  {
    $this->template->title = 'ネタの追加';
    $this->template->content = \View::forge('ideas/create');
  }

  /**
   * 新規作成処理
   * POST: /ideas/create
   * action_xxxではなくpost_xxxで処理を分離
   */
  public function post_create()
  {
    // CSRFチェック（早期リターン）
    if (!\Security::check_token()) {
      \Session::set_flash('error', 'セッションが切れました。');
      return \Response::redirect('ideas/create');
    }

    // 2. バリデーション
    $val = \Validation::forge();
    $val->add_field('idea_text', '内容', 'required|max_length[255]');

    if (!$val->run()) {
      \Session::set_flash('error', '入力内容に不備があります。');
      return \Response::redirect('ideas/create');
    }

    $user_auth_info = \Auth::get_user_id();
    $insert_data = [
      'user_id'     => $user_auth_info[1],
      // バリデートに成功したフィールド(idea_text)と値(手動で追加しようとしているネタの名前)を取得する
        'idea_text'   => $val->validated('idea_text'),
        // お気に入りはデフォルトでoffにする
        'is_favorite' => 0,
      ];

    // Modelのメソッド（insert）を使用して保存
    if (!Model_IdeaSelection::insert_idea($insert_data)) {
      \Session::set_flash('error', '保存に失敗しました。');
      return \Response::redirect('ideas/create');
      }

    \Session::set_flash('success', '保存しました！');
    return \Response::redirect('ideas/index');
  }

  /**
   * ネタの一括保存 (AJAX)
   * POST: /ideas/add_bulk
   */
  public function post_add_bulk()
  {
    if (!\Security::check_token()) {
      return $this->ajax_response(['status' => 'error'], 400);
    }

    $post_ideas = \Input::post('ideas');
    if (!is_array($post_ideas) || empty($post_ideas)) {
      return $this->ajax_response(['status' => 'error', 'message' => 'データが空です'], 400);
    }

    $success_count = 0;
    $user_auth_info = \Auth::get_user_id();
    $user_id = $user_auth_info[1];

    foreach ($post_ideas as $text) {
      $insert_data = [
        'user_id'     => $user_id,
        'idea_text'   => (string) $text,
        'is_favorite' => 0,
      ];
      
      if (Model_IdeaSelection::insert_idea($insert_data)) {
        $success_count++;
      }
    }

    return $this->ajax_response(['status' => 'success', 'count' => $success_count]);
  }

  /**
   * 更新処理 (AJAX)
   * POST: /ideas/save
   */
  public function post_save()
  {
    if (!\Security::check_token()) {
      return $this->ajax_response(['status' => 'error', 'message' => 'CSRF fail'], 400);
    }

    $target_id = (int) \Input::post('id');
    $idea_record = Model_IdeaSelection::get_by_ideaId($target_id);
    $user_auth_info = \Auth::get_user_id();
    $user_id = $user_auth_info[1];

    // 本人のデータかチェック（早期リターン）
    if (!$idea_record || (int) $idea_record['user_id'] !== (int) $user_id) {
      return $this->ajax_response(['status' => 'error'], 403);
    }

    $update_params = [
      'idea_text'   => (string) \Input::post('idea_text'),
      'is_favorite' => (\Input::post('is_favorite') === '1' || \Input::post('is_favorite') === 'true') ? 1 : 0,
    ];

    Model_IdeaSelection::update_idea($target_id, $update_params);
    return $this->ajax_response(['status' => 'success']);
  }

  /**
   * 削除処理 (AJAX)
   * POST: /ideas/delete
   */
  public function post_delete()
  {
    if (!\Security::check_token()) {
      return $this->ajax_response(['status' => 'error'], 400);
    }

    $target_id = (int) \Input::post('id');
    $idea_record = Model_IdeaSelection::getIdea_by_ideaId($target_id);
    $user_auth_info = \Auth::get_user_id();
    $user_id = $user_auth_info[1];

    if (!$idea_record || (int) $idea_record['user_id'] !== (int) $user_id) {
      return $this->ajax_response(['status' => 'error'], 403);
    }

    Model_IdeaSelection::delete_idea($target_id);
      return $this->ajax_response(['status' => 'success']);
  }

  /**
   * 生成画面
   * GET: /ideas/generate
   */
  public function action_generate()
  {
    $this->template->title = 'ネタ生成';
    $this->template->content = \View::forge('ideas/generate');
  }
}