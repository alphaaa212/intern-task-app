<?php

class Controller_Testdb extends Controller
{
  public function action_index()
  {
    // ユーザー作成
    $user = Model_User::forge(array(
      'username' => 'taro_' . rand(1,1000),
      'email' => 'taro' . rand(1,1000) . '@example.com',
      'password_hash' => password_hash('1234', PASSWORD_DEFAULT),
    ));
    $user->save();

    // ネタ保存
    $idea = Model_IdeaSelection::forge(array(
      'user_id' => $user->id,
      'idea_text' => 'これは保存テストネタ',
      'is_favorite' => 1,
    ));
    $idea->save();

    // ネタ一覧取得（ユーザー情報も一緒に）
    $ideas = Model_IdeaSelection::find('all', array(
      'related' => array('user')
    ));

    $data = array();

    foreach ($ideas as $idea)
    {
      $data[] = array(
        'idea_id' => $idea->id,
        'idea_text' => $idea->idea_text,
        'is_favorite' => $idea->is_favorite,
        'username' => $idea->user->username,
        'email' => $idea->user->email,
      );
    }

    return json_encode($data);
  }
}
