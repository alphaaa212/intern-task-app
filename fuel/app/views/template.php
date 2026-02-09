<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <?php echo Asset::css('style.css'); ?>
  <!-- 三項演算子 -->
  <title><?php echo isset($title) ? e($title) : 'ネタ管理アプリ'; ?></title>
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