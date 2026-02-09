<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>ログイン</title>
  <?php echo Asset::css('auth.css'); ?>
</head>
<body>
    <div class="page-header">
      <h2 class="page-title">ログイン</h2>
      <p class="page-subtitle">ネタを記録・整理するためにログインしてください。</p>
    </div>

    <div class="card-container">
      <div class="form-card">
        <?php if (isset($error)): ?>
            <div class="error-summary single-error">
                <p class="error-text"><?php echo e($error); ?></p>
            </div>
        <?php endif; ?>

        <?php echo Form::open('auth/login'); ?>
            <?php echo Form::csrf(); ?>

            <div class="form-group">
                    <label class="form-label">
                        ユーザー名
              <input type="text" name="username" class="form-input" value="<?php echo e(Input::post('username', '')); ?>" placeholder="ユーザー名を入力" required autofocus>
                    </label>
            </div>

            <div class="form-group">
                    <label class="form-label">
                        パスワード
              <input type="password" name="password" class="form-input" placeholder="パスワードを入力" required>
                    </label>
            </div>

            <div class="form-actions">
              <?php echo Form::submit('submit', 'ログインする', ['class' => 'btn btn-primary btn-full']); ?>
            </div>

            <div class="form-footer">
              <p>アカウントをお持ちでない方は <?php echo Html::anchor('auth/register', '新規登録へ'); ?></p>
            </div>
        <?php echo Form::close(); ?>
      </div>
    </div>
</body>
</html>