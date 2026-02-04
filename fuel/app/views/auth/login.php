<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>ログイン - ネタ管理アプリ</title>
    <style>
        .error { color: red; }
        .form-group { margin-bottom: 15px; }
    </style>
</head>
<body>
    <h2>ログイン</h2>

    <?php if (isset($error)): ?>
        <p class="error"><?php echo $error; ?></p>
    <?php endif; ?>

    <?php echo Form::open('auth/login'); ?>
        
        <div class="form-group">
            <label>ユーザー名:</label><br>
            <?php echo Form::input('username', Input::post('username')); ?>
        </div>

        <div class="form-group">
            <label>パスワード:</label><br>
            <?php echo Form::password('password'); ?>
        </div>

        <div class="form-group">
            <?php echo Form::submit('submit', 'ログイン'); ?>
        </div>

    <?php echo Form::close(); ?>

    <hr>
    <p>アカウントをお持ちでない方は <?php echo Html::anchor('auth/register', '新規登録'); ?> へ</p>
</body>
</html>