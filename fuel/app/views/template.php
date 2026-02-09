<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo isset($title) ? e($title) : 'ネタ管理アプリ'; ?></title>
    <style>
    :root {
      --primary-color: #3498db;
      --secondary-color: #2c3e50;
      --accent-color: #d96666;
      --bg-color: #f8fafc;
      --card-bg: #ffffff;
      --text-main: #334155;
      --text-sub: #64748b;
      --border-color: #e2e8f0;
    }

    body {
      font-family: "Helvetica Neue", Arial, "Hiragino Kaku Gothic ProN", "Hiragino Sans", Meiryo, sans-serif;
      margin: 0;
      padding: 0;
      line-height: 1.6;
      background-color: var(--bg-color);
      color: var(--text-main);
    }

    /* ヘッダー */
    .site-header {
      background-color: var(--card-bg);
      box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
      position: sticky;
      top: 0;
      z-index: 1000;
    }

    .main-navigation {
      max-width: 1000px;
      margin: 0 auto;
      padding: 0 20px;
      height: 64px;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }

    .app-branding {
      font-weight: 800;
      font-size: 1.25rem;
      text-decoration: none;
      color: var(--primary-color);
    }

    .nav-links {
      display: flex;
      gap: 10px;
      list-style: none;
      margin: 0;
      padding: 0;
    }

    .nav-item {
      text-decoration: none;
      color: var(--text-main);
      padding: 8px 16px;
      border-radius: 6px;
      font-size: 0.95rem;
      font-weight: 500;
      transition: all 0.2s;
    }

    .nav-item:hover {
      background-color: #f1f5f9;
      color: var(--primary-color);
    }

    .user-actions {
      display: flex;
      align-items: center;
      gap: 15px;
    }

    .user-name {
      font-size: 0.85rem;
      color: var(--text-sub);
    }

    .btn-logout {
      font-size: 0.85rem;
      color: var(--accent-color);
      text-decoration: none;
      border: 1px solid var(--accent-color);
      padding: 5px 12px;
      border-radius: 4px;
      transition: all 0.2s;
    }

    .btn-logout:hover {
      background-color: var(--accent-color);
      color: #ffffff;
    }

    /* メインエリア */
    .content-container {
      max-width: 1000px;
      margin: 30px auto;
      padding: 0 20px;
      min-height: calc(100vh - 200px);
    }

    /* フッター */
    .site-footer {
      background-color: var(--card-bg);
      margin-top: 60px;
      padding: 30px 20px;
      border-top: 1px solid var(--border-color);
      text-align: center;
      color: var(--text-sub);
      font-size: 0.85rem;
    }
    .copyright-text { margin: 0; }

    /* --- template.php に追記 --- */

    /* フォーム下部のボタンエリア共通設定 */
    .form-actions {
      margin-top: 25px;       /* 入力欄とボタンの間に適切な距離を作る */
      display: flex;          /* ボタンを並べるための設定 */
      gap: 12px;              /* ボタンが複数ある場合の隙間 */
      flex-direction: column; /* 基本はボタンを縦に積む（スマホ・ログイン用） */
    }

    /* もし横並びにしたいページ（作成画面の保存・キャンセルなど）があれば */
    .form-actions.row-layout {
      flex-direction: row;
      justify-content: flex-end; /* 右寄せにする */
    }

    /* --- 共通ボタンデザイン --- */
    .btn {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      padding: 12px 24px;
      border-radius: 10px; /* 少し丸みを強くしてモダンに */
      font-size: 1rem;
      font-weight: 700;    /* 文字を太くして視認性アップ */
      cursor: pointer;
      transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
      border: none;
      text-decoration: none;
      box-sizing: border-box;
    }

    /* メインの青ボタン（立体感と影を追加） */
    .btn-primary {
      background: linear-gradient(135deg, var(--primary-color), #2980b9);
      color: #ffffff;
      box-shadow: 0 4px 6px -1px rgba(52, 152, 219, 0.3), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
    }

    .btn-primary:hover {
      transform: translateY(-2px); /* 少し浮き上がる演出 */
      box-shadow: 0 10px 15px -3px rgba(52, 152, 219, 0.4);
      opacity: 0.95;
    }

    .btn-primary:active {
      transform: translateY(0); /* クリックした時に沈む演出 */
    }

    /* 枠線のみのボタン（キャンセル等用） */
    .btn-outline {
      background-color: transparent;
      border: 2px solid #e2e8f0;
      color: var(--text-sub);
    }

    .btn-outline:hover {
      background-color: #f8fafc;
      border-color: #cbd5e1;
      color: var(--text-main);
    }

    /* 成功・保存ボタン（緑） */
    .btn-success {
      background: linear-gradient(135deg, #2ecc71, #27ae60);
      color: #fff;
      box-shadow: 0 4px 6px -1px rgba(46, 204, 113, 0.3);
    }

    /* ログイン・登録画面などで使う「横いっぱい」設定 */
    .btn-full {
      width: 100%;
      display: flex;
    }
  </style>
</head>
<body>

  <header class="site-header">
    <nav class="main-navigation">
      <div class="nav-left">
        <?php echo Html::anchor('ideas/index', 'NetaLog', ['class' => 'app-branding']); ?>
      </div>

      <ul class="nav-links">
        <li><?php echo Html::anchor('ideas/index', 'ネタ一覧', ['class' => 'nav-item']); ?></li>
        <li><?php echo Html::anchor('ideas/generate', '思考整理', ['class' => 'nav-item']); ?></li>
      </ul>

      <div class="user-actions">
        <?php if (Auth::check()): ?>
          <span class="user-name"><?php echo e($user->username); ?> さん</span>
          <?php echo Html::anchor('auth/logout', 'ログアウト', ['class' => 'btn-logout']); ?>
        <?php endif; ?>
      </div>
    </nav>
  </header>

  <main class="content-container">
    <?php echo isset($content) ? $content : ''; ?>
  </main>

  <footer class="site-footer">
    <p class="copyright-text">&copy; <?php echo date('Y'); ?> ネタ管理アプリ開発プロジェクト</p>
  </footer>

</body>
</html>