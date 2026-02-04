<h2>新しいネタを登録する</h2>

<?php echo Form::open(['action' => 'ideas/create', 'method' => 'post']); ?>
    <?php echo Form::csrf(); // セキュリティ対策：CSRFトークンの埋め込み ?>

    <div class="form-group">
        <?php echo Form::label('ネタの内容', 'idea_text'); ?>
        <?php echo Form::input('idea_text', Input::post('idea_text'), [
            'placeholder' => '例：AIを使った時短術について',
            'style' => 'width: 100%;'
        ]); ?>
    </div>

    <div class="actions" style="margin-top: 20px;">
        <?php echo Form::submit('submit', '保存する', ['class' => 'btn btn-primary']); ?>
        <a href="/ideas/index">キャンセル</a>
    </div>
<?php echo Form::close(); ?>