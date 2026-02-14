<?php
class Controller_Ideas extends Controller_Base
{
  protected $user_id;

/**
 * アクション実行前の共通処理
 */
  public function before()
  {
    parent::before();
    // 親クラス(Base)で取得済みのIDを利用
    $this->user_id = $this->current_user ? $this->current_user->id : null;
  }

  /**
   * JSONレスポンス共通処理
   */
  private function ajax_response($data = [], $status = 200)
  {
    // 次の送信で用いるトークンを生成
    $data['new_token'] = \Security::fetch_token();
  // データをJSON形式に変換し、フロントエンド(JS)でも読み込めるようにする
    return \Response::forge(json_encode($data), $status)
      ->set_header('Content-Type', 'application/json');// 返信データのラベルにJSONデータであると伝える
  }

  /**
   * 所有権チェック共通処理
   */
  private function get_owned_idea($id)
  {
    $idea = Model_IdeaSelection::get_idea_by_ideaId((int)$id);
    if (!$idea || (int)$idea['user_id'] !== (int)$this->user_id) {
      return null;
    }
    return $idea;
  }

  public function action_index()
  {
    $ideas = Model_IdeaSelection::get_ideas_by_user_id($this->user_id);
    $this->template->title = 'マイネタ一覧';
    $this->template->content = \View::forge('ideas/index');

    // JSが読めるJSON形式に変換した$ideas_jsonをView(index.php)に渡す
    $this->template->content->set('ideas_json', json_encode($ideas), false);
  }

  /**
   * ネタの手動追加画面
   * GET: /ideas/create
   */
  public function action_create()
  {
    $this->template->title = 'ネタの追加';
    $this->template->content = \View::forge('ideas/create');
  }

  /**
   * ネタの手動追加処理
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

    $res = Model_IdeaSelection::insert_idea([
      'user_id'     => $this->user_id,
      // バリデートに成功したフィールド(idea_text)と値(手動で追加しようとしているネタの名前)を取得する
      'idea_text'   => $val->validated('idea_text'),
      // お気に入りはデフォルトでoffにする
      'is_favorite' => 0,
    ]);

    if (!$res) {
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
    if (!\Security::check_token()) return $this->ajax_response(['status' => 'error'], 400);

    $post_ideas = \Input::post('ideas');
    if (!is_array($post_ideas)) return $this->ajax_response(['status' => 'error', 'message' => 'データが不正です'], 400);

    $success_count = 0;
    foreach ($post_ideas as $text) {
      if (Model_IdeaSelection::insert_idea([
        'user_id'     => $this->user_id,
        'idea_text'   => (string)$text,
        'is_favorite' => 0,
      ])) $success_count++;
    }

    return $this->ajax_response(['status' => 'success', 'count' => $success_count]);
  }

  /**
   * 更新処理 (AJAX)
   * POST: /ideas/save
   */
  public function post_save()
  {
      // CSRFチェック
      if (!\Security::check_token()) {
          return $this->ajax_response(['status' => 'error', 'message' => 'CSRF fail'], 400);
      }

      $id = \Input::post('id');
      $text = \Input::post('idea_text');
      $fav = \Input::post('is_favorite');

      // 所有権チェック
      $idea = $this->get_owned_idea($id);
      if (!$idea) {
          return $this->ajax_response(['status' => 'error', 'message' => 'Forbidden'], 403);
      }

      $update_params = [
          'idea_text'   => (string)$text,
          'is_favorite' => ($fav == 1) ? 1 : 0,
      ];

      // Model側でDB更新
      Model_IdeaSelection::update_idea($id, $update_params);

      return $this->ajax_response(['status' => 'success']);
  }

  /**
   * 削除処理 (AJAX)
   * POST: /ideas/delete
   */
  public function post_delete()
  {
    if (!\Security::check_token()) return $this->ajax_response(['status' => 'error'], 400);

    $id = \Input::post('id');
    if (!$this->get_owned_idea($id)) return $this->ajax_response(['status' => 'error'], 403);

    Model_IdeaSelection::delete_idea($id);
    return $this->ajax_response(['status' => 'success']);
  }

  /**
   * 思考整理画面
   * GET: /ideas/generate
   */
  public function action_generate()
  {
    $this->template->title = 'ネタ生成';
    $this->template->content = \View::forge('ideas/generate');
  }

  /**
   * Gemini AIによるネタ生成 (AJAX)
   * POST: /ideas/api_generate
   */
  public function post_api_generate()
  {
    if (!\Security::check_token()) return $this->ajax_response(['status' => 'error', 'message' => 'セッション切れ'], 400);

    $input = trim((string)\Input::post('user_input'));
    if (empty($input)) return $this->ajax_response(['status' => 'error', 'message' => '入力が空です'], 400);

    try {
      $ideas = Service_Gemini::generate_ideas($input);
      return $this->ajax_response(['status' => 'success', 'ideas' => $ideas]);
    } catch (\Exception $e) {
      \Log::error('Gemini API Error: ' . $e->getMessage());
      return $this->ajax_response(['status' => 'error', 'message' => 'AI通信に失敗しました'], 500);
    }
  }
}