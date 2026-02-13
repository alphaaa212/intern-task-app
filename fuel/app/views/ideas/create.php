<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>ネタの手動追加</title>
  <?php echo Asset::css('create.css'); ?>
</head>
<body>

  <div class="page-header">
    <h2 class="page-title">新しいネタを登録する</h2>
  </div>

<div class="flex justify-center mt20">
  <div class="idea-form-container">
  <!-- <form action='ideas/create', method='post'> -->
  <?php echo Form::open(['action' => 'ideas/create', 'method' => 'post']); ?>
        <?php echo Form::csrf(); ?>

      <div class="form-group">
          <?php echo Form::label('ネタの内容', 'idea_text', ['class' => 'form-label']); ?>
          <?php echo Form::input('idea_text', Input::post('idea_text'), [
              'placeholder' => '例：AIを使った時短術について',
            'class' => 'form-input',
            'required' => 'required',
            'autofocus' => 'autofocus'
          ]); ?>
      </div>

      <div class="form-actions mt25 flex gap12">
          <?php echo Form::submit('submit', '保存する', ['class' => 'btn btn-primary']); ?>
          <?php echo Html::anchor('ideas/index', 'キャンセル', ['class' => 'btn btn-outline']); ?>
      </div>

<!-- </form> -->
  <?php echo Form::close(); ?>
    </div>
  </div>

</body>
</html>