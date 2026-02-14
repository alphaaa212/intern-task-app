<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>ネタ一覧</title>
  <?php echo Asset::css('ideas.css'); ?>
</head>
<body>
<div id="ideas-app">
  <div class="page-header flex justify-between items-center mb30">
    <h2 class="page-title">保存したネタ一覧</h2>
    <?php echo Html::anchor('ideas/create', '+ 手動で追加', ['class' => 'btn btn-primary']); ?>
  </div>
    
  <div class="action-bar flex justify-between items-center mt20">
    <div class="filter-group">
      <button type="button" class="btn btn-outline" 
        data-bind="click: () => filterMode('all'), css: { active: filterMode() === 'all' }">すべて</button>
      <button type="button" class="btn btn-outline" 
        data-bind="click: () => filterMode('fav'), css: { active: filterMode() === 'fav' }">★ お気に入り</button>
    </div>
    <div class="stats-text">
      全 <span data-bind="text: ideas().length"></span> 件
    </div>
  </div>

  <div class="ideas-list mt20" data-bind="foreach: filteredIdeas">
    <div class="idea-item mb12">
      
      <div data-bind="ifnot: isEditing" class="flex justify-between items-center">
        <div class="idea-main flex items-center gap15">
          <span class="favorite-icon" 
            data-bind="click: $parent.toggleFav, 
                      text: is_favorite() ? '★' : '☆', 
                      css: is_favorite() ? 'is-fav' : 'not-fav'"></span>
          <p class="idea-text" data-bind="text: idea_text"></p>
        </div>
        <div class="idea-actions flex gap8">
          <button type="button" class="btn btn-outline btn-sm" data-bind="click: () => isEditing(true)">編集</button>
          <button type="button" class="btn btn-danger btn-sm" data-bind="click: $parent.deleteIdea">削除</button>
        </div>
      </div>

      <div data-bind="visible: isEditing">
        <div class="flex gap10">
          <input type="text" class="edit-input" data-bind="textInput: idea_text">
          
          <button type="button" class="btn btn-save" data-bind="click: $root.saveEdit">保存</button>
          
          <button type="button" class="btn btn-outline" data-bind="click: () => isEditing(false)">戻る</button>
        </div>
      </div>

    </div>
  </div>
</div>

<script>
  // データをwindow にセットする
  window.AppConfig = {
    // PHP側のデータをJSで使えるようにJSON形式で埋め込む
    ideasData: <?php echo $ideas_json ?? '[]'; ?>,
    endpoints: {
      // Ajaxでアクセスする「住所（URL）」をJSに教える
      save: '<?php echo \Uri::create("ideas/save"); ?>',
      delete: '<?php echo \Uri::create("ideas/delete"); ?>'
    },
    csrf: {
      // トークンに渡すに渡す
      key: '<?php echo \Config::get("security.csrf_token_key", "fuel_csrf_token"); ?>',
      token: '<?php echo \Security::fetch_token(); ?>'
    }
  };
</script>

<?php echo Asset::js('jquery.min.js'); ?>
<?php echo Asset::js('knockout.js'); ?>
<script src="<?php echo Asset::get_file('ideas_index.js', 'js'); ?>?v=<?php echo time(); ?>"></script>
</body>
</html>