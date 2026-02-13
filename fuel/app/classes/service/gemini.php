<?php
/**
 * Gemini APIとの通信を担当するサービス
 */
class Service_Gemini
{
  /**
   * 入力テキストからアイデアを5件生成する
   * @param string $user_input
   * @return array 整形済みのアイデアリスト
   * @throws \Exception API通信エラー
   */
  public static function generate_ideas($user_input)
  {
    $api_key = \Config::get('gemini.api_key');
    if (empty($api_key)) {
      throw new \Exception('APIキーが設定されていません。');
    }

    $model = "gemini-2.5-flash";
    $url = "https://generativelanguage.googleapis.com/v1/models/{$model}:generateContent?key={$api_key}";

    // プロンプト構築
    $prompt = self::build_prompt($user_input);

    $data = [
      "contents" => [
        [
          "parts" => [
            ["text" => $prompt]
          ]
        ]
      ]
    ];

    // APIリクエスト
    $response = self::execute_curl($url, $data);
    
    // レスポンス解析
    return self::parse_response($response);
  }

  /**
   * プロンプトの組み立て
   */
  private static function build_prompt($input)
  {
    return "あなたは執筆者の潜在的なエピソードを引き出し、毎日投稿の継続を支援する『良き執筆パートナー兼、凄腕のnote編集者』です。
    ユーザーから与えられた【入力内容】を基に、執筆者自身が「これについて語りたい」「これなら今日書けそうだ」とワクワクするようなタイトル案を5件提案してください。

    【入力内容】: 「{$input}」

    【タイトル生成の指針】:
    1. **最適な切り口の自律選定**: 入力内容の本質や意外な一面に光を当ててください。「感情・分析・if・比較・裏側・本質」など、そのテーマが最もnoteらしく輝く切り口を自ら選定してください。
    2. **執筆を誘発する『余白』と『リズム』**: 執筆者が自分の記憶を当てはめやすいよう、あえて少し抽象度を残してください。また、「〜のこと」「〜について」といった単調な形式を避け、執筆者が続きを書き出したくなるようなリズムのある言葉選びをしてください。
    3. **過去の資産とリサーチで完結**: 今すぐデスクで完結するテーマに限定します。ユーザーの経験や思考、あるいは簡単な調べ学習で「今日中に1,000文字書ける」という安心感を与えてください。
    4. **一部分へのフォーカス**: 入力内容のすべてを網羅しようとして凡庸なタイトルになるのを避けてください。一部分の鋭い着眼点に絞った提案も大歓迎です。
    5. **「私」を主語にしやすい構成**: noteの特性上、執筆者が「私」という主語で書き始めやすい、個人的かつ普遍的な視点を含めてください。

    【出力ルール】:
    - 5件はそれぞれ、切り口が重複しないように、全く異なる読後感を想像させるものにすること。
    - 余計な挨拶や解説は一切不要です。タイトルのみを5行、行頭「- 」形式で出力してください。";
    }

  /**
   * cURLリクエストの実行
   */
  private static function execute_curl($url, $data)
  {
    // セッションハンドラの取得
    $ch = curl_init($url);

    // オプションの設定
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']); //ヘッダーの設定。データの形式を伝える。
    curl_setopt($ch, CURLOPT_POST, true); //送信方式をPOSTに指定
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));// 送信データのJSON化
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); //レスポンスを文字列として返す(レスポンスを変数に代入できるようにする)
    
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

    curl_setopt($ch, CURLOPT_TIMEOUT, 15);// タイムアウトを設定

    // 実行
    $response = curl_exec($ch);

    // 通信失敗チェック
    if ($response === false) {
      $error = curl_error($ch);
      curl_close($ch);
      throw new \Exception('通信自体に失敗しました: ' . $error);
    }

    // サーバーの反応を取得
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    // セッションの終了
    curl_close($ch);

        if ($http_code !== 200) {
            throw new \Exception('AI通信エラー(HTTP:' . $http_code . ') レスポンス:' . $response);
        }

        return $response;
    }

  /**
   * APIレスポンスを配列に整形
   */
  private static function parse_response($response)
  {
    $result = json_decode($response, true);
    $ai_text = $result['candidates'][0]['content']['parts'][0]['text'] ?? '';
    
    // 改行コードの統一
    $lines = explode("\n", str_replace(["\r\n", "\r"], "\n", $ai_text));
    $clean_ideas = [];
    
    foreach ($lines as $line) {
      // 行頭の記号や数字、空白を削ってクリーンにする
      $cleaned = ltrim(trim($line), "- *1234567890. \t\n\r\0\x0B");
      if (!empty($cleaned)) {
        $clean_ideas[] = $cleaned;
      }
    }

    return array_slice($clean_ideas, 0, 5);
  }
}