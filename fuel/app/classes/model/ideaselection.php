<?php
/**
 * ネタデータの操作を行うモデル（DBクラス使用）
 */
class Model_IdeaSelection extends \Model
{
  /**
   * ユーザーIDに紐づくネタ一覧を取得
   */
  public static function get_ideas_by_user_id($user_id)
  {
    $result = \DB::select()
      ->from('idea_selections')
      ->where('user_id', '=', $user_id)
      ->order_by('created_at', 'desc')
      ->execute()
      ->as_array();
    
    return $result;
  }

  /**
   * 単一のネタを取得（編集・削除権限チェック用）
   */
  public static function get_idea_by_id($id)
  {
    return \DB::select()
      ->from('idea_selections')
      ->where('id', '=', (int)$id)
      ->execute()
      ->current();
  }

  /**
   * 新規保存
   */
  public static function insert_idea($data)
  {
    $now = date('Y-m-d H:i:s');
    $data['created_at'] = $now;
    $data['updated_at'] = $now;

    // executeにより返却される配列を入れるために2つの変数（insertされたIDのリストと、insertされたレコード数）を定義している
    list($insert_id, $rows_affected) = \DB::insert('idea_selections')
      ->set($data)
      ->execute();
    
    return $insert_id;
  }

  /**
   * 更新（編集・お気に入り）
   */
  public static function update_idea($id, $data)
  {
    $data['updated_at'] = date('Y-m-d H:i:s');

    return \DB::update('idea_selections')
      ->set($data)
      ->where('id', '=', (int)$id)
      ->execute();
  }

  /**
   * 削除
   */
  public static function delete_idea($id)
  {
    return \DB::delete('idea_selections')
      ->where('id', '=', (int)$id)
      ->execute();
  }
}