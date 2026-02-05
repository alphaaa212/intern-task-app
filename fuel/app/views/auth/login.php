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
        <?php echo Form::label('ユーザー名', 'username', ['class' => 'form-label']); ?>
        <input type="text" name="username" class="form-input" value="<?php echo e(Input::post('username', '')); ?>" placeholder="ユーザー名を入力" required autofocus>
        </div>

        <div class="form-group">
        <div class="label-row">
          <?php echo Form::label('パスワード', 'password', ['class' => 'form-label']); ?>
        </div>
        <input type="password" name="password" class="form-input" placeholder="パスワードを入力" required>
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

<style>
/* 変数が使えない場合を想定し、直接色を指定しています */
  .card-container {
    display: flex;
    justify-content: center;
    align-items: center;
    padding: 40px 20px;
  }

  .form-card {
    background: #ffffff;
    padding: 30px;
    border-radius: 12px;
    border: 1px solid #e2e8f0;
    width: 100%;
    max-width: 400px;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
  }

  /* フォームグループ */
  .form-group {
    margin-bottom: 20px;
  }

  .form-label {
    display: block;
    margin-bottom: 8px;
    font-weight: 600;
    color: #2c3e50;
    font-size: 0.9rem;
  }

  .form-input {
    width: 100%;
    padding: 12px;
    border: 1px solid #cbd5e1;
    border-radius: 8px;
    font-size: 1rem;
    box-sizing: border-box;
    transition: all 0.2s;
  }

  .form-input:focus {
    border-color: #3498db;
    outline: none;
    box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
  }

  /* ボタンエリア */
  .form-actions {
    margin-top: 25px;
  }

  /* ボタン自体のデザイン（思考整理ページ風） */
  .btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 12px 24px;
    border-radius: 10px;
    font-size: 1rem;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    border: none;
    text-decoration: none;
    box-sizing: border-box;
  }

  .btn-primary {
    background: linear-gradient(135deg, #3498db, #2980b9);
    color: #ffffff;
    box-shadow: 0 4px 6px -1px rgba(52, 152, 219, 0.3);
  }

  .btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 15px -3px rgba(52, 152, 219, 0.4);
  }

  .btn-full {
    width: 100%;
  }
</style>