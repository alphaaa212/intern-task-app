<?php echo \Asset::js('jquery.min.js'); ?>
<?php echo \Asset::js('knockout.js'); ?>

<style>
  .ideas-container { margin-top: 20px; }
  .page-title { margin-bottom: 20px; }
  .action-bar { margin-bottom: 20px; }
  .idea-item { 
    margin-bottom: 15px; 
    padding: 15px;
    border: 1px solid #dee2e6;
    border-radius: 4px;
  }
  .idea-text-wrapper { margin-bottom: 0; }
  .favorite-icon { 
    margin-right: 10px; 
    cursor: pointer; 
  }
  .text-favorite { color: #ffc107; }
</style>

<div class="ideas-container">
  <h2 class="page-title">保存したネタ一覧</h2>

  <div class="action-bar d-flex justify-content-between align-items-center">
    <a href="<?php echo \Uri::create('ideas/create'); ?>" class="btn btn-primary">
      + 手動でネタを追加する
    </a>
    
    <div class="btn-group">
      <button class="btn btn-outline-secondary" data-bind="click: function(){ filterMode('all') }, css: { active: filterMode() == 'all' }">すべて</button>
      <button class="btn btn-outline-warning" data-bind="click: function(){ filterMode('fav') }, css: { active: filterMode() == 'fav' }">★お気に入り</button>
    </div>
  </div>

  <?php if (empty($ideas)): ?>
    <div class="alert alert-warning">
      まだネタが登録されていません。新しいアイデアをストックしましょう！
    </div>
  <?php else: ?>
    <div class="ideas-list" data-bind="foreach: filteredIdeas">
      <div class="idea-item shadow-sm">
        
        <div data-bind="ifnot: isEditing">
          <div class="d-flex w-100 justify-content-between align-items-center">
            <h5 class="idea-text-wrapper text-primary">
              <span class="favorite-icon text-favorite" data-bind="click: $parent.toggleFav, text: is_favorite() ? '★' : '☆'"></span>
              <span data-bind="text: idea_text"></span>
            </h5>
            <div class="button-group">
              <button class="btn btn-sm btn-outline-info" data-bind="click: function(){ isEditing(true) }">編集</button>
              <button class="btn btn-sm btn-outline-danger" data-bind="click: $parent.deleteIdea">削除</button>
            </div>
          </div>
        </div>

        <div data-bind="if: isEditing">
          <div class="input-group">
            <input type="text" class="form-control" data-bind="value: idea_text">
            <div class="input-group-append">
              <button class="btn btn-success" data-bind="click: $parent.saveEdit">保存</button>
              <button class="btn btn-secondary" data-bind="click: function(){ isEditing(false) }">戻る</button>
            </div>
          </div>
        </div>
        
      </div>
    </div>
  <?php endif; ?>
</div>

<script>
$(function() {
  function IdeaViewModel(rawIdeas) {
    var self = this;

    // セキュリティ設定
    var csrf_key = '<?php echo \Config::get('security.csrf_token_key', 'fuel_csrf_token'); ?>';
    var current_csrf_token = '<?php echo \Security::fetch_token(); ?>';

    self.updateCsrfToken = function(response) {
      if (response && response.new_token) {
        current_csrf_token = response.new_token;
      }
    };

    // データの初期化
    self.ideas = ko.observableArray(rawIdeas.map(function(item) {
      return {
        id: Number(item.id),
        idea_text: ko.observable(item.idea_text),
        is_favorite: ko.observable(String(item.is_favorite) === '1'),
        isEditing: ko.observable(false)
      };
    }));

    self.filterMode = ko.observable('all');

    // フィルタリング
    self.filteredIdeas = ko.computed(function() {
      var mode = self.filterMode();
      if (mode === 'all') return self.ideas();
      return self.ideas().filter(function(i) { 
        return i.is_favorite(); 
      });
    });

    self.toggleFav = function(item) {
      item.is_favorite(!item.is_favorite());
      self.sendUpdate(item);
    };

    self.saveEdit = function(item) {
      self.sendUpdate(item);
      item.isEditing(false);
    };

    // 通信処理
    self.sendUpdate = function(item) {
      var post_data = {
        id: item.id,
        idea_text: item.idea_text(),
        is_favorite: item.is_favorite() ? 1 : 0
      };
      post_data[csrf_key] = current_csrf_token;

      $.post('<?php echo \Uri::create("ideas/save"); ?>', post_data)
        .done(function(res) {
          self.updateCsrfToken(res);
        })
        .fail(function(xhr) { 
          if (xhr.responseJSON) self.updateCsrfToken(xhr.responseJSON);
          alert('保存に失敗しました。'); 
        });
    };

    self.deleteIdea = function(item) {
      if (!confirm('本当に削除しますか？')) {
        return;
      }
      
      var delete_data = { 
        id: item.id 
      };
      delete_data[csrf_key] = current_csrf_token;

      $.post('<?php echo \Uri::create("ideas/delete"); ?>', delete_data)
          .done(function(res) { 
          self.updateCsrfToken(res);
          self.ideas.remove(item);
          })
          .fail(function(xhr) { 
          if (xhr.responseJSON) self.updateCsrfToken(xhr.responseJSON);
          alert('削除に失敗しました。');
          });
    };
  }

  var dataFromPhp = <?php echo $ideas_json ?? '[]'; ?>;
  ko.applyBindings(new IdeaViewModel(dataFromPhp));
});
</script>