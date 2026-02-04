<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
  <title><?php echo isset($title) ? e($title) : 'ネタ管理アプリ'; ?></title>
    <style>
    /* 基本レイアウト */
    body {
      font-family: sans-serif;
      margin: 20px;
      line-height: 1.6;
    }

    /* 役割に基づいたクラス命名 */
    .main-navigation {
      background-color: #f4f4f4;
      padding: 10px;
      margin-bottom: 20px;
    }

    .app-branding {
      font-weight: bold;
    }

    .site-footer {
      margin-top: 50px;
      font-size: 0.8em;
      color: #888888;
    }

    .copyright-text {
      margin: 0;
    }
    </style>
</head>
<body>
  <nav class="main-navigation">
    <span class="app-branding">思考整理・ネタ提案アプリ</span>
    </nav>

  <main class="content-container">
        <?php echo $content; ?>
    </main>

  <footer class="site-footer">
    <p class="copyright-text">&copy; <?php echo date('Y'); ?> ネタ管理アプリ開発プロジェクト</p>
    </footer>
</body>
</html>