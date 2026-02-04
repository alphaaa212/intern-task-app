<?php

class Controller_Ideas extends Controller_Base
{
    // Ajaxレスポンス用の共通メソッド
    // トークンの付与漏れを防ぎ、コードの重複を削る
    private function ajax_response($data = [], $status = 200)
    {
        $data['new_token'] = \Security::fetch_token();
        return \Response::forge(json_encode($data),$status)->set_header('Content-Type','application/json');
    }

    // ネタ一覧ページ
    public function action_index()
    {
        $ideas = \Model_IdeaSelection::find('all', [
            'where' => [['user_id', '=', $this->current_user->id]],
            'order_by' => ['created_at' => 'desc'],
        ]);

        $this->template->title = 'マイネタ一覧';
        $this->template->content = View::forge('ideas/index');
        
        // Viewの中で $ideas として使えるようにセット
        $this->template->content->set('ideas', $ideas);

        // Knockout.jsに渡すためのJSONデータ
        $ideas_array = array_values(array_map(function($i){
            return $i->to_array();
        },$ideas));

        // 第3引数falseでエスケープを無効化し、JSON文字列をそのまま渡す
        $this->template->content->set('ideas_json',json_encode($ideas_array),false);
    }

    // 投稿画面の表示
    public function action_create()
    {
        // template.phpを通して表示
        $this->template->title = 'ネタの追加';
        $this->template->content = \View::forge('ideas/create');
    }

    // 手動登録の保存処理
    public function post_create() //POSTリクエストを受け取る
    {
        // セキュリティ：CSRFトークンのチェック
        if (! Security::check_token()) {
            \Session::set_flash('error', 'セッションがタイムアウトしました。もう一度やり直してください。');
            Response::redirect('ideas/create');
        }

        $val = Validation::forge();
        $val->add_field('idea_text', 'ネタの内容', 'required|max_length[255]');

        if ($val->run()) {
            // バリデーション成功：モデルのインスタンスを作成
            $idea = \Model_IdeaSelection::forge([
                'user_id'    => $this->current_user->id, // ログイン中のIDを自動紐付け
                'idea_text'  => $val->validated('idea_text'),
                'is_favorite' => 0, // デフォルトは「お気に入りなし」
            ]);

            if ($idea->save()) {
                \Session::set_flash('success', '新しいネタを保存しました！');
                \Response::redirect('ideas/index');
            } else {
                \Session::set_flash('error', 'DBの保存に失敗しました。');
            }
        } else {
            // バリデーション失敗
            Session::set_flash('error', $val->show_errors());
        }

        \Response::redirect('ideas/create');
    }

    // 編集・お気に入り登録(Ajax用)
    public function post_save()
    {
        if(! \Security::check_token()){
            return $this->ajax_response(['status' => 'error','message' => 'CSRF fail'],400);
        }
        

        try {
            $id = (int)Input::post('id');
            $idea = \Model_IdeaSelection::find($id);

            if (!$idea || (int)$idea->user_id !== (int)$this->current_user->id){
                throw new \Exception("権限がないか、データが見つかりません");
            }

            $idea->idea_text = \Input::post('idea_text');
            $fav = \Input::post('is_favorite');
            $idea->is_favorite = ($fav === 'true' || $fav === true || $fav === '1') ? 1 : 0;

            if ($idea->save()) {
                return $this->ajax_response(['status' => 'success']);
            }
            throw new \Exception("DB保存失敗");

        } catch (\Exception $e) {
            // 500エラーの正体をJSONで返します
            return $this->ajax_response(['status' => 'error','message' => $e->getMessage()],500);
        }
    }

    // 削除処理
    public function post_delete($id = null)
    {
        if(! \Security::check_token()) {
            return $this->ajax_response(['status' => 'error', 'message' => 'CSRF fail'], 400);
        }

        try {
            $target_id = $id ?: Input::post('id');
            $idea = \Model_IdeaSelection::find($target_id);
            
            if (!$target_id) {
                throw new Exception("IDが指定されていません");
            }

            if ($idea && (int)$idea->user_id === (int)$this->current_user->id) {
                $idea->delete();
                return $this->ajax_response(['status' => 'success']);
            }
            throw new \Exception("削除対象が見つからないか権限がありません");
        }catch (\Exception $e) {
            return $this->ajax_response(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }
}