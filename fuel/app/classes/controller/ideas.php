<?php
class Controller_Ideas extends Controller_Base
{
  private function ajax_response($data = [], $status = 200)
  {
    $data['new_token'] = \Security::fetch_token();
    return \Response::forge(json_encode($data), $status)
      ->set_header('Content-Type', 'application/json');
  }

  /**
   * 一覧表示
   */
  public function action_index()
  {
    // Authクラスから現在ログイン中のユーザーIDを取得
    $user_id = \Auth::get_user_id()[1];
    $ideas = Model_IdeaSelection::get_by_user_id($user_id);

    $this->template->title = 'マイネタ一覧';
    $this->template->content = \View::forge('ideas/index');
    
    // Viewへのセット（データの整形はコントローラで行う）
    $this->template->content->set('ideas', $ideas);
    $this->template->content->set('ideas_json', json_encode($ideas), false);
  }

  /**
   * 新規作成画面
   */
  public function action_create()
  {
    $this->template->title = 'ネタの追加';
    $this->template->content = \View::forge('ideas/create');
  }

  /**
   * 新規作成処理
   */
  public function post_create()
  {
    // 1. セキュリティチェック（早期リターン）
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

    // 3. データ保存
      $data = [
      'user_id'     => \Auth::get_user_id()[1],
        'idea_text'   => $val->validated('idea_text'),
        'is_favorite' => 0,
      ];

    if (!Model_IdeaSelection::insert_idea($data)) {
      \Session::set_flash('error', '保存に失敗しました。');
      return \Response::redirect('ideas/create');
      }

    \Session::set_flash('success', '保存しました！');
    return \Response::redirect('ideas/index');
  }

  /**
   * ネタの一括保存 (AJAX)
   */
  public function post_add_bulk()
  {
    if (!\Security::check_token()) {
      return $this->ajax_response(['status' => 'error'], 400);
    }

    $ideas = \Input::post('ideas');
    if (!is_array($ideas) || empty($ideas)) {
      return $this->ajax_response(['status' => 'error', 'message' => 'データが空です'], 400);
    }

    $success_count = 0;
    $user_id = \Auth::get_user_id()[1];

    foreach ($ideas as $text) {
      $data = [
        'user_id'     => $user_id,
        'idea_text'   => (string)$text,
        'is_favorite' => 0,
      ];
      
      if (Model_IdeaSelection::insert_idea($data)) {
        $success_count++;
      }
    }

    return $this->ajax_response(['status' => 'success', 'count' => $success_count]);
  }

  /**
   * 更新処理 (AJAX)
   */
  public function post_save()
  {
    if (!\Security::check_token()) {
      return $this->ajax_response(['status' => 'error', 'message' => 'CSRF fail'], 400);
    }

    $id = (int)\Input::post('id');
    $idea = Model_IdeaSelection::get_by_id($id);
    $user_id = \Auth::get_user_id()[1];

    // 本人のデータかチェック（早期リターン）
    if (!$idea || (int)$idea['user_id'] !== (int)$user_id) {
      return $this->ajax_response(['status' => 'error'], 403);
    }

    $update_data = [
      'idea_text'   => (string)\Input::post('idea_text'),
      'is_favorite' => (\Input::post('is_favorite') === '1' || \Input::post('is_favorite') === 'true') ? 1 : 0,
    ];

    Model_IdeaSelection::update_idea($id, $update_data);
    return $this->ajax_response(['status' => 'success']);
  }

  /**
   * 削除処理 (AJAX)
   */
  public function post_delete()
  {
    if (!\Security::check_token()) {
      return $this->ajax_response(['status' => 'error'], 400);
    }

    $id = (int)\Input::post('id');
    $idea = Model_IdeaSelection::get_by_id($id);
    $user_id = \Auth::get_user_id()[1];

    if (!$idea || (int)$idea['user_id'] !== (int)$user_id) {
      return $this->ajax_response(['status' => 'error'], 403);
    }

      Model_IdeaSelection::delete_idea($id);
      return $this->ajax_response(['status' => 'success']);
  }

  /**
   * 生成画面
   */
  public function action_generate()
  {
    $this->template->title = 'ネタ生成';
    $this->template->content = \View::forge('ideas/generate');
  }
}