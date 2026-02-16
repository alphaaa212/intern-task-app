<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>思考整理＆ネタ提案</title>
  <?php echo Asset::css('generate.css'); ?>
</head>
<body>

<div id="generate-app">
  <div class="page-header">
    <h2 class="page-title">💡 ネタの種を撒く</h2>
    <p class="page-subtitle">今の正直な思いを吐き出してください。そこからAIがネタを抽出します。</p>
  </div>

  <div class="generate-input-container">
    <textarea class="generate-textarea" rows="5" 
      data-bind="value: rawInput, valueUpdate: 'input'" 
      placeholder="整っていなくて構いません。頭の中にあることを自由に書いてみてください..."></textarea>
    
    <button class="btn btn-primary btn-large" 
      data-bind="click: generateIdeas, disable: isGeneratingStatus() || !rawInput().trim()">
      <span data-bind="text: generateButtonText"></span>
    </button>
  </div>

  <div class="results-area" data-bind="visible: generatedIdeasList().length > 0" style="display: none;">
    <h3 class="section-title">✨ 提案されたネタ案</h3>
    
    <div class="generated-list" data-bind="foreach: generatedIdeasList">
      <div class="generated-item" data-bind="css: { 'is-selected': isChecked(), 'is-saved': isSaved(), 'is-editing': isEditing() }">
        <div class="item-main-row flex items-center">
          <div class="checkbox-wrapper" data-bind="visible: !isSaved()">
            <input type="checkbox" class="item-checkbox" data-bind="checked: isChecked">
          </div>
          
          <div class="text-container">
            <div data-bind="ifnot: isEditing">
              <span class="item-text" data-bind="text: text"></span>
            </div>
            
            <div data-bind="if: isEditing">
              <input type="text" class="edit-field" data-bind="value: editText, hasFocus: isEditing" placeholder="ネタを修正...">
            </div>
          </div>

          <div class="item-actions">
            <button class="btn btn-outline btn-sm" 
              data-bind="visible: !isEditing() && !isSaved(), click: startEdit">編集</button>
              
            <button class="btn btn-save btn-sm" 
              data-bind="visible: isEditing(), click: confirmEdit">確定</button>

            <button class="btn btn-outline btn-sm" 
              data-bind="visible: isEditing(), click: cancelEdit">戻す</button>
            <span class="badge-success" data-bind="visible: isSaved">保存済み</span>
          </div>
        </div>
      </div>
    </div>

    <div class="floating-actions" data-bind="visible: hasCheckedIdeas">
      <button class="btn btn-success btn-large shadow" data-bind="click: saveSelectedIdeaList, disable: isSavingStatus">
        <span data-bind="text: isSavingStatus() ? '保存中...' : 'チェックした内容で保存する'"></span>
      </button>
    </div>
  </div>
</div>

<script>
  // PHPからの動的変数をJSに渡すためのブリッジ
  window.AppConfig = {
    endpoints: {
      generateApi: '<?php echo \Uri::create("ideas/api_generate"); ?>',
      saveIdeas: '<?php echo \Uri::create("ideas/add_bulk"); ?>',
      redirectUrl: '<?php echo \Uri::create("ideas/index"); ?>'
    },
    csrfToken: '<?php echo \Security::fetch_token(); ?>'
  };
</script>

<?php echo Asset::js('jquery.min.js'); ?>
<?php echo Asset::js('knockout.js'); ?>
<?php echo Asset::js('generate.js'); ?>

</body>
</html>