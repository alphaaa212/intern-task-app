<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>新規登録</title>
  <?php echo Asset::css('auth.css'); ?>
</head>
<body>
  <div class="page-header">
    <h2 class="page-title">ユーザー登録</h2>
    <p class="page-subtitle">NetaLogを始めるためにアカウントを作成しましょう。</p>
  </div>

  <div class="card-container">
    <div class="form-card">
      <?php if (!empty($errors)): ?>
        <div class="error-summary">
          <ul class="error-list">
            <?php foreach ($errors as $error): ?>
              <li><?php echo e($error); ?></li>
            <?php endforeach; ?>
          </ul>
        </div>
      <?php endif; ?>

      <!-- <form action="http://localhost(開発段階)/auth/register" accept-charset="utf-8" method="post"> を返す -->
      <?php echo Form::open('auth/register'); ?>

        <!-- <input type="hidden" name="fuel_csrf_token" value="一回限りの合言葉（ランダムな文字列）" />を自動で生成し、valueがサーバーで発行したものと一致するかをチェックする -->
        <?php echo Form::csrf(); ?>

        <div class="form-group">
          <label class="form-label">
            ユーザー名
            <!-- エラーが起きた時に、再度ユーザー名を入力しなくてよいようにvalueをセットする -->
            <input type="text" name="username" class="form-input" value="<?php echo e(Input::post('username', '')); ?>" placeholder="例: tanaka_tarou">
          </label>
        </div>

        <div class="form-group">
          <label class="form-label">
            メールアドレス
            <input type="email" name="email" class="form-input" value="<?php echo e(Input::post('email', '')); ?>" placeholder="example@mail.com">
          </label>
        </div>

        <div class="form-group">
          <label class="form-label">
            パスワード（8文字以上）
            <input type="password" name="password" class="form-input" placeholder="パスワードを入力">
          </label>
        </div>

        <div class="form-group">
          <label class="form-label">
            パスワード（確認）
            <input type="password" name="password_confirm" class="form-input" placeholder="もう一度パスワードを入力">
          </label>
        </div>

        <div class="form-actions">
          <?php echo Form::submit('submit', 'アカウントを作成', ['class' => 'btn btn-primary btn-full']); ?>
        </div>

        <div class="form-footer">
          <p>すでにアカウントをお持ちの方は <?php echo Html::anchor('auth/login', 'ログインへ'); ?></p>
        </div>
      <?php echo Form::close(); ?>
    </div>
  </div>
</body>
</html>