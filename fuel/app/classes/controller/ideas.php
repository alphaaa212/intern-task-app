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

    foreach ($post_ideas as $idea_text) {
      $insert_data = [
        'user_id'     => $user_id,
        'idea_text'   => (string) $idea_text,
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
    $idea_record = Model_IdeaSelection::getIdea_by_ideaId($target_id);
    $user_auth_info = \Auth::get_user_id();
    $user_id = $user_auth_info[1];

    // 本人のデータかチェック
      // $idea_recordが存在しないか、ログインユーザーとアイデアのユーザーが一致しない場合
    if (!$idea_record || (int) $idea_record['user_id'] !== (int) $user_id) {
      return $this->ajax_response(['status' => 'error'], 403);
    }

    $update_params = [
      'idea_text'   => (string) \Input::post('idea_text'),

      // is_favoriteが1かtrueなら
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
  /**
   * Gemini AIによるネタ生成 (AJAX)
   * POST: /ideas/api_generate
   */
  public function post_api_generate()
  {
    // 1. セキュリティチェック（CSRFトークン）
    if (!\Security::check_token()) {
      return $this->ajax_response(['status' => 'error', 'message' => 'セッションがタイムアウトしました。ページを再読み込みしてください。'], 400);
    }

    // 2. 入力値の取得とバリデーション
    $user_input = \Input::post('user_input');
    if (empty(trim($user_input))) {
      return $this->ajax_response(['status' => 'error', 'message' => '入力が空です'], 400);
    }

    // 3. ConfigからAPIキーを取得（development/config.phpから読み込まれます）
    $api_key = \Config::get('gemini.api_key');
    if (empty($api_key)) {
        return $this->ajax_response(['status' => 'error', 'message' => 'APIキーが設定されていません。'], 500);
    }

    // 4. Gemini API 設定 (最新の2.5-flashモデルを使用)
    $model = "gemini-2.5-flash";
    $url = "https://generativelanguage.googleapis.com/v1/models/{$model}:generateContent?key={$api_key}";

    // 5. プロンプト（命令文）の構築
    $prompt = "あなたは、執筆者の可能性を広げる『多角的な視点を持つ編集者』です。
    ユーザーの入力内容をテーマにして、**それぞれ全く異なる記事の中身になるような**、バリエーション豊かなタイトル案を5件提案してください。

    【ユーザーの入力内容】:
    「{$user_input}」

    【思考プロセス：5つの異なる役割から提案する】:
    1. 【内省エッセイ】個人的な体験や、心の機微を深掘りする切り口。
    2. 【実用・Tips】読者の悩みを解決したり、新しいやり方を提案したりする実用的な切り口。
    3. 【批評・社会】その言葉を社会現象や文化、価値観のレベルで鋭く分析する切り口。
    4. 【if・物語】「もしも〜だったら？」という仮定から、想像力を膨らませるフィクションや実験的切り口。
    5. 【Q&A・対話】読者との対話や、自分への問いかけを軸にした参加型の切り口。

    【出力ルール】:
    - 5件がそれぞれ「書きたい内容（構成）」が明確に異なるようにすること。
    - 似たような方向性のタイトルは即座に却下し、1件ごとに別のジャンルとして成立させること。
    - 執筆者が「これなら違う内容が書ける！」と納得できる多様性を持たせること。
    - 余計な説明は省き、タイトルのみを5行、行頭「- 」形式で出力してください。";

    $data = [
      "contents" => [
        [
          "parts" => [
            ["text" => $prompt]
          ]
        ]
      ]
    ];

    // 6. cURLによるAPIリクエスト
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // ローカル開発環境用

    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curl_error = curl_error($ch);
    curl_close($ch);

    // 7. 通信エラー判定
    if ($http_code !== 200) {
      return $this->ajax_response([
        'status' => 'error',
        'message' => 'AIとの通信に失敗しました。',
        'debug_info' => [
          'http_code' => $http_code,
          'curl_error' => $curl_error,
          'google_response' => json_decode($response, true) ?: $response
        ]
      ], 500);
    }

    // 8. レスポンスの解析と整形
    $result = json_decode($response, true);
    $ai_text = $result['candidates'][0]['content']['parts'][0]['text'] ?? '';

    // テキストから5件のリストを抽出（空行を除外）
    $lines = explode("\n", $ai_text);
    $clean_ideas = [];
    
    foreach ($lines as $line) {
      $trimmed = trim($line);
      if (empty($trimmed)) continue;

      // 行頭の記号（- や * や 数字. ）を削除
      $cleaned = ltrim($trimmed, "- *1234567890. \t\n\r\0\x0B");
      
      if (!empty($cleaned)) {
        $clean_ideas[] = $cleaned;
      }
    }

    // 最大5件を返却
    return $this->ajax_response([
      'status' => 'success',
      'ideas' => array_slice($clean_ideas, 0, 5)
    ]);
  }
}