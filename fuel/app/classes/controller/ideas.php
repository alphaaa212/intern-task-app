<?php
class Controller_Ideas extends Controller_Base
{
  private function ajax_response($data = [], $status = 200)
  {
    $data['new_token'] = \Security::fetch_token();
    return \Response::forge(json_encode($data), $status)
      ->set_header('Content-Type', 'application/json');
  }

  public function action_index()
  {
    $ideas = Model_IdeaSelection::get_by_user_id($this->current_user->id);
    $this->template->title = 'マイネタ一覧';
    $this->template->content = \View::forge('ideas/index');
    $this->template->content->set('ideas', $ideas);
    $this->template->content->set('ideas_json', json_encode($ideas), false);
  }

  public function action_create()
  {
    $this->template->title = 'ネタの追加';
    $this->template->content = \View::forge('ideas/create');
  }

  public function post_create()
  {
    if (! \Security::check_token()) {
      \Session::set_flash('error', 'セッションが切れました。');
      \Response::redirect('ideas/create');
    }

    $val = \Validation::forge();
    $val->add_field('idea_text', '内容', 'required|max_length[255]');

    if ($val->run()) {
      $data = [
        'user_id'     => $this->current_user->id,
        'idea_text'   => $val->validated('idea_text'),
        'is_favorite' => 0,
      ];
      if (Model_IdeaSelection::insert_idea($data)) {
        \Session::set_flash('success', '保存しました！');
        \Response::redirect('ideas/index');
      }
    }
    \Session::set_flash('error', '保存失敗');
    \Response::redirect('ideas/create');
  }

  /**
   * 生成されたネタの一括保存
   */
  public function post_add_bulk()
  {
    if (! \Security::check_token()) {
      return $this->ajax_response(['status' => 'error'], 400);
    }

    $ideas = \Input::post('ideas');

    if (!is_array($ideas) || empty($ideas)) {
      return $this->ajax_response(['status' => 'error', 'message' => 'No data'], 400);
    }

    $success_count = 0;
    foreach ($ideas as $text) {
      $data = [
        'user_id'     => $this->current_user->id,
        'idea_text'   => (string)$text,
        'is_favorite' => 0,
      ];
      
      if (Model_IdeaSelection::insert_idea($data)) {
        $success_count++;
      }
    }

    return $this->ajax_response(['status' => 'success', 'count' => $success_count]);
  }

  public function post_save()
  {
    if (! \Security::check_token()) {
      return $this->ajax_response(['status' => 'error', 'message' => 'CSRF fail'], 400);
    }

    $id = (int)\Input::post('id');
    $idea = Model_IdeaSelection::get_by_id($id);

    if (!$idea || (int)$idea['user_id'] !== (int)$this->current_user->id) {
      return $this->ajax_response(['status' => 'error'], 403);
    }

    $update_data = [
      'idea_text'   => (string)\Input::post('idea_text'),
      'is_favorite' => (\Input::post('is_favorite') == '1' || \Input::post('is_favorite') == 'true') ? 1 : 0,
    ];

    Model_IdeaSelection::update_idea($id, $update_data);
    return $this->ajax_response(['status' => 'success']);
  }

  public function post_delete()
  {
    if (! \Security::check_token()) {
      return $this->ajax_response(['status' => 'error'], 400);
    }

    $id = (int)\Input::post('id');
    $idea = Model_IdeaSelection::get_by_id($id);

    if ($idea && (int)$idea['user_id'] === (int)$this->current_user->id) {
      Model_IdeaSelection::delete_idea($id);
      return $this->ajax_response(['status' => 'success']);
    }
    return $this->ajax_response(['status' => 'error'], 403);
  }

  public function action_generate()
  {
    $this->template->title = 'ネタ生成';
    $this->template->content = \View::forge('ideas/generate');
  }
}