<h1>テスト表示</h1>

<?php echo \Asset::js('jquery.min.js'); ?>
<?php echo \Asset::js('knockout.js'); ?>

<style>
  .page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
  }

  .page-title { margin: 0; font-size: 1.5rem; }

  /* 操作バー */
  .action-bar {
    background: #fff;
    padding: 15px;
    border-radius: 8px;
    margin-bottom: 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    box-shadow: 0 1px 2px rgba(0,0,0,0.05);
  }

  /* ネタカード */
  .idea-item {
    background: #fff;
    margin-bottom: 12px;
    padding: 20px;
    border-radius: 8px;
    border: 1px solid #e2e8f0;
    transition: transform 0.2s, box-shadow 0.2s;
  }
  .idea-item:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
  }

  .idea-main {
    display: flex;
    align-items: center;
    gap: 15px;
    flex: 1;
  }

  .favorite-icon {
    font-size: 1.4rem;
    cursor: pointer;
    user-select: none;
    transition: scale 0.2s;
  }
  .favorite-icon:hover { scale: 1.2; }
  .is-fav { color: #fbc02d; }
  .not-fav { color: #cbd5e1; }

  .idea-text {
    font-size: 1.1rem;
    font-weight: 500;
    color: #1e293b;
    margin: 0;
  }

  /* ボタン類 */
  .btn {
    padding: 8px 16px;
    border-radius: 6px;
    border: none;
    cursor: pointer;
    font-weight: 600;
    font-size: 0.875rem;
    transition: all 0.2s;
  }
  .btn-primary { background: #3498db; color: #fff; text-decoration: none; }
  .btn-primary:hover { background: #2980b9; }
  
  .btn-outline { background: transparent; border: 1px solid #e2e8f0; color: #64748b; }
  .btn-outline.active { background: #64748b; color: #fff; }

  .btn-sm { padding: 4px 10px; font-size: 0.75rem; }
  .btn-save { background: #2ecc71; color: #fff; }
  .btn-danger { color: #e74c3c; }

  .edit-input {
    width: 100%;
    padding: 8px;
    border: 2px solid #3498db;
    border-radius: 4px;
    outline: none;
  }

  .empty-state {
    text-align: center;
    padding: 60px;
    color: #94a3b8;
    background: #fff;
    border-radius: 8px;
    border: 2px dashed #e2e8f0;
  }
</style>

<div id="ideas-app">
<div class="page-header">
  <h2 class="page-title">保存したネタ一覧</h2>
  <?php echo Html::anchor('ideas/create', '+ 手動で追加', ['class' => 'btn btn-primary']); ?>
</div>
    
<div class="action-bar">
  <div class="filter-group">
    <button class="btn btn-outline" data-bind="click: () => filterMode('all'), css: { active: filterMode() === 'all' }">すべて</button>
    <button class="btn btn-outline" data-bind="click: () => filterMode('fav'), css: { active: filterMode() === 'fav' }">★ お気に入り</button>
  </div>
  <div class="stats-text">
    全 <span data-bind="text: ideas().length"></span> 件
    </div>
  </div>

<div class="empty-state">
  <p>まだネタが登録されていません。新しいアイデアをストックしましょう！</p>
    </div>
    <div class="ideas-list" data-bind="foreach: filteredIdeas">
  <div class="idea-item">
    <div data-bind="ifnot: isEditing" style="display: flex; justify-content: space-between; align-items: center;">
      <div class="idea-main">
        <span class="favorite-icon" data-bind="click: $parent.toggleFav, text: is_favorite() ? '★' : '☆', css: is_favorite() ? 'is-fav' : 'not-fav'"></span>
        <p class="idea-text" data-bind="text: idea_text"></p>
            </div>
      <div class="idea-actions">
        <button class="btn btn-outline btn-sm" data-bind="click: () => isEditing(true)">編集</button>
        <button class="btn btn-danger btn-sm" data-bind="click: $parent.deleteIdea">削除</button>
          </div>
        </div>

        <div data-bind="if: isEditing">
      <div style="display: flex; gap: 10px;">
        <input type="text" class="edit-input" data-bind="value: idea_text">
        <button class="btn btn-save" data-bind="click: $parent.saveEdit">保存</button>
        <button class="btn btn-outline" data-bind="click: () => isEditing(false)">戻る</button>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
$(function() {
  // PHPからデータを取得
  const dataFromPhp = <?php echo $ideas_json ?? '[]'; ?>;
  console.log("Debug Data:", dataFromPhp);

  const IdeaViewModel = function(rawIdeas) {
    const self = this;

    // CSRF設定
    const csrfKey = '<?php echo \Config::get('security.csrf_token_key', 'fuel_csrf_token'); ?>';
    // 【修正】初期トークンを正しく変数に格納
    let currentCsrfToken = '<?php echo \Security::fetch_token(); ?>';

    const updateCsrfToken = (response) => {
      if (response && response.new_token) {
        currentCsrfToken = response.new_token;
      }
    };

    self.ideas = ko.observableArray(rawIdeas.map(item => ({
        id: Number(item.id),
        idea_text: ko.observable(item.idea_text),
        is_favorite: ko.observable(String(item.is_favorite) === '1'),
        isEditing: ko.observable(false)
    })));

    self.filterMode = ko.observable('all');

    self.filteredIdeas = ko.computed(() => {
      const mode = self.filterMode();
      return mode === 'all' ? self.ideas() : self.ideas().filter(i => i.is_favorite());
    });

    self.toggleFav = (item) => {
      item.is_favorite(!item.is_favorite());
      self.sendUpdate(item);
    };

    self.saveEdit = (item) => {
      self.sendUpdate(item);
      item.isEditing(false);
    };

    /**
     * 更新リクエスト
     */
    self.sendUpdate = (item) => {
      const postData = {
        id: item.id,
        idea_text: item.idea_text(),
        is_favorite: item.is_favorite() ? 1 : 0
      };
      postData[csrfKey] = currentCsrfToken;

      $.post('<?php echo \Uri::create("ideas/save"); ?>', postData)
        .done(res => updateCsrfToken(res))
        .fail(xhr => {
          if (xhr.responseJSON) updateCsrfToken(xhr.responseJSON);
          alert('保存に失敗しました。'); 
        });
    };

    /**
     * 削除リクエスト
     */
    self.deleteIdea = (item) => {
      if (!confirm('このネタを削除してもよろしいですか？')) return;

      const deleteData = { id: item.id };
      deleteData[csrfKey] = currentCsrfToken;

      $.post('<?php echo \Uri::create("ideas/delete"); ?>', deleteData)
        .done(res => {
          updateCsrfToken(res);
          self.ideas.remove(item);
          })
        .fail(xhr => {
          if (xhr.responseJSON) updateCsrfToken(xhr.responseJSON);
          alert('削除に失敗しました。');
          });
    };
  };

  // 【重要】HTML側に id="ideas-app" があることを確認してバインド
  const el = document.getElementById('ideas-app');
  if (el) {
    ko.applyBindings(new IdeaViewModel(dataFromPhp), el);
  } else {
    console.error("Element #ideas-app not found!");
  }
});
</script>