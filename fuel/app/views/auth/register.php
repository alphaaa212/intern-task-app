<h2>ユーザー登録</h2>

<?php if (!empty($errors)): ?>
    <ul style="color: red;">
        <?php foreach ($errors as $error): ?>
            <li><?php echo $error; ?></li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>

<form method="post" action="/auth/register">
    <p>
        ユーザー名：<br>
        <input type="text" name="username" value="<?php echo Input::post('username'); ?>">
        <?php if (isset($errors['username'])): ?>
            <span style="color: red; display: block;">
        <?php endif; ?>
    </p>

    <p>
        メール：<br>
        <input type="email" name="email" value="<?php echo Input::post('email'); ?>">
        <?php if (isset($errors['email'])): ?>
            <span style="color:red; display: block;">
                <?php echo $errors['email'] ?>
            </span>
        <?php endif; ?>
    </p>

    <p>
        パスワード：<br>
        <input type="password" name="password">
        <?php if (isset($errors['password'])): ?>
            <span style="color: red; display: block;"><?php echo $errors['password']; ?></span>
        <?php endif; ?>
    </p>

    <button type="submit">登録</button>
</form>
