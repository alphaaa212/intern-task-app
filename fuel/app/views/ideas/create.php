<div class="page-header">
  <h2 class="page-title">新しいネタを登録する</h2>
</div>

<div class="card-container">
  <div class="form-card">
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

      <div class="form-actions">
        <?php echo Form::submit('submit', '保存する', ['class' => 'btn btn-primary']); ?>
        <?php echo Html::anchor('ideas/index', 'キャンセル', ['class' => 'btn btn-outline']); ?>
    </div>
<?php echo Form::close(); ?>
  </div>
</div>

<style>
  .card-container { display: flex; justify-content: center; margin-top: 20px; }
  .form-card { background: #fff; padding: 30px; border-radius: 12px; border: 1px solid #e2e8f0; width: 100%; max-width: 500px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); }
  .form-label { display: block; margin-bottom: 8px; font-weight: 600; color: var(--secondary-color); }
  .form-input { width: 100%; padding: 12px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 1rem; box-sizing: border-box; transition: border-color 0.2s; }
  .form-input:focus { border-color: var(--primary-color); outline: none; box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1); }
  .form-actions { margin-top: 25px; display: flex; gap: 12px; }
</style>