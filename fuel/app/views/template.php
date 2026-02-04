<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title><?php echo isset($title) ? $title : 'ネタ管理アプリ'; ?></title>
    <style>
        body { font-family: sans-serif; margin: 20px; line-height: 1.6; }
        nav { background: #f4f4f4; padding: 10px; margin-bottom: 20px; }
        footer { margin-top: 50px; font-size: 0.8em; color: #888; }
    </style>
</head>
<body>
    <nav>
        <strong>思考整理・ネタ提案アプリ</strong>
    </nav>

    <main>
        <?php echo $content; ?>
    </main>

    <footer>
        <p>&copy; 2026 ネタ管理アプリ開発プロジェクト</p>
    </footer>
</body>
</html>