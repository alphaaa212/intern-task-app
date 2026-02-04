<style>
  .text-error {
    color: #ff0000;
  }
  .error-message {
    display: block;
  }
  .form-group {
    margin-bottom: 15px;
  }
  .error-list {
    margin-bottom: 20px;
  }
</style>

<h2>ユーザー登録</h2>

<?php if (!empty($errors)): ?>
  <ul class="text-error error-list">
    <?php foreach ($errors as $error): ?>
      <li><?php echo e($error); ?></li>
    <?php endforeach; ?>
  </ul>
<?php endif; ?>

<form method="post" action="/auth/register">
  <form method="post" action="/auth/register">
  <?php echo Form::csrf(); ?>
  <div class="form-group">
    <label>ユーザー名：</label><br>
    <input type="text" name="username" value="<?php echo e(Input::post('username', '')); ?>">
    <?php if (isset($errors['username'])): ?>
      <span class="text-error error-message"><?php echo e($errors['username']); ?></span>
    <?php endif; ?>
  </div>

  <div class="form-group">
    <label>メール：</label><br>
    <input type="email" name="email" value="<?php echo e(Input::post('email', '')); ?>">
    <?php if (isset($errors['email'])): ?>
      <span class="text-error error-message"><?php echo e($errors['email']); ?></span>
    <?php endif; ?>
  </div>

  <div class="form-group">
    <label>パスワード：</label><br>
    <input type="password" name="password">
    <?php if (isset($errors['password'])): ?>
      <span class="text-error error-message"><?php echo e($errors['password']); ?></span>
    <?php endif; ?>
  </div>

  <button type="submit">登録</button>

  <hr>
  <p>アカウントをお持ちの方は <?php echo Html::anchor('auth/login', 'ログイン'); ?> へ</p>
</form>