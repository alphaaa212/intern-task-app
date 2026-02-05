<?php echo \Asset::js('jquery.min.js'); ?>
<?php echo \Asset::js('knockout.js'); ?>

<div id="generate-app">
  <div class="page-header">
    <h2 class="page-title">💡 ネタの種を撒く</h2>
    <p class="page-subtitle">今の正直な思いを吐き出してください。そこからAIがネタを抽出します。</p>
  </div>

  <div class="generate-input-card">
    <textarea class="generate-textarea" rows="5" 
      data-bind="value: rawInput, valueUpdate: 'input'" 
      placeholder="頭の中にあることを自由に書いてみてください..."></textarea>
    
    <button class="btn btn-primary btn-large" 
      data-bind="click: generateIdeas, disable: isGenerating() || !rawInput().trim()">
      <span data-bind="text: isGenerating() ? '抽出中...' : 'ネタを生成する'">ネタを生成する</span>
    </button>
  </div>

  <div class="results-area" data-bind="visible: generatedIdeas().length > 0" style="display: none;">
    <h3 class="section-title">✨ 提案されたネタ案</h3>
    
    <div class="generated-list" data-bind="foreach: generatedIdeas">
      <div class="generated-item" data-bind="css: { 'is-selected': isChecked(), 'is-saved': isSaved(), 'is-editing': isEditing() }">
        <div class="item-main-row">
          <div class="checkbox-wrapper" data-bind="visible: !isSaved()">
            <input type="checkbox" class="item-checkbox" data-bind="checked: isChecked">
          </div>
          
          <div class="text-container">
            <div data-bind="ifnot: isEditing">
          <span class="item-text" data-bind="text: text"></span>
            </div>
            
            <div data-bind="if: isEditing">
              <input type="text" class="edit-field" data-bind="value: text, hasFocus: isEditing" placeholder="ネタを修正...">
            </div>
          </div>

          <div class="item-actions">
            <button class="btn btn-outline btn-sm" 
                      data-bind="visible: !isEditing(), click: () => isEditing(true)">編集</button>
              
              <button class="btn btn-save btn-sm" 
                      data-bind="visible: isEditing(), click: () => isEditing(false)">確定</button>
            <span class="badge-success" data-bind="visible: isSaved">保存済み</span>
          </div>
      </div>
    </div>
    </div>

    <div class="floating-actions" data-bind="visible: hasCheckedIdeas">
      <button class="btn btn-success btn-large shadow" data-bind="click: saveSelectedIdeas, disable: isSaving">
        <span data-bind="text: isSaving() ? '保存中...' : 'チェックした内容で保存する'"></span>
      </button>
    </div>
  </div>
</div>

<style>
  /* 基本スタイル継承 */
  .page-subtitle { color: var(--text-sub); margin-top: 5px; }
  .generate-input-card { background: #fff; padding: 20px; border-radius: 12px; border: 1px solid #e2e8f0; margin-bottom: 30px; }
  .generate-textarea { width: 100%; border: 1px solid #e2e8f0; border-radius: 8px; padding: 15px; font-size: 1rem; resize: vertical; box-sizing: border-box; margin-bottom: 15px; }
  .generate-textarea:focus { border-color: var(--primary-color); outline: none; }
  .btn-large { width: 100%; padding: 15px; font-size: 1.1rem; }

  .section-title { font-size: 1.2rem; margin-bottom: 15px; color: var(--secondary-color); }
  .generated-list { display: flex; flex-direction: column; gap: 10px; margin-bottom: 120px; }
  
  .generated-item { background: #fff; padding: 12px 20px; border-radius: 8px; border: 1px solid #e2e8f0; transition: all 0.2s; }
  .generated-item.is-selected { border-color: var(--primary-color); background: #f0f9ff; }
  .generated-item.is-saved { opacity: 0.6; background: #f8fafc; }
  .generated-item.is-editing { border-color: var(--primary-color); background: #fff; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); }
  
  .item-main-row { display: flex; align-items: center; gap: 15px; min-height: 44px; }
  .checkbox-wrapper { display: flex; align-items: center; }
  .item-checkbox { width: 22px; height: 22px; cursor: pointer; }
  
  .text-container { flex: 1; overflow: hidden; }
  .item-text { font-size: 1.05rem; font-weight: 500; color: var(--text-main); line-height: 1.4; display: block; }
  .edit-field { width: 100%; padding: 6px 10px; border: 2px solid var(--primary-color); border-radius: 6px; font-size: 1rem; box-sizing: border-box; }

  .item-actions {
    display: flex;
    align-items: center;
    justify-content: flex-end; /* 右寄せ */
    gap: 8px;
    flex-shrink: 0;
    width: 70px; /* ボタンの幅を固定しておくと切り替え時にガタつかない */
  }
  
  /* ボタン追加 */
  .btn-sm { padding: 4px 12px; font-size: 0.8rem; }
  .btn-save { background: var(--primary-color); color: #fff; }
  .btn-save:hover { opacity: 0.9; }
  
  .badge-success { background: #2ecc71; color: #fff; font-size: 0.75rem; padding: 4px 8px; border-radius: 4px; font-weight: bold; }
  .floating-actions { position: fixed; bottom: 30px; left: 50%; transform: translateX(-50%); width: 100%; max-width: 400px; padding: 0 20px; box-sizing: border-box; z-index: 100; }
  .shadow { box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1); }
</style>

<script>
$(function() {
  const IdeaGenerateViewModel = function() {
    const self = this;
    
    // 1. 状態管理
    self.rawInput = ko.observable(""); 
    self.isGenerating = ko.observable(false);
    self.isSaving = ko.observable(false);
    self.generatedIdeas = ko.observableArray([]);

    // 2. 算出プロパティ
    self.hasCheckedIdeas = ko.computed(() => {
      return self.generatedIdeas().some(item => item.isChecked() && !item.isSaved());
    });

    // 3. ネタ生成ロジック
    self.generateIdeas = () => {
      if (!self.rawInput().trim()) return;
      
      self.isGenerating(true);
      self.generatedIdeas([]);

      // 擬似的な生成待ち演出
      setTimeout(() => {
        const input = self.rawInput().substring(0, 10);
        const dummy = [
          `【漫才】「${input}」の絶妙な違和感`,
          `【コント】自称「${input}」の達人`,
          `【エッセイ】なぜ現代人は「${input}」に惹かれるのか`,
          `【大喜利】「${input}」を100倍楽しくする方法`,
          `【短編】「${input}」から始まる物語`
        ];

        const mapped = dummy.map(textStr => ({
          text: ko.observable(textStr),
            isChecked: ko.observable(false),
          isSaved: ko.observable(false),
          isEditing: ko.observable(false)
        }));

        self.generatedIdeas(mapped);
        self.isGenerating(false);
      }, 800);
    };

    // 4. 保存処理
    self.saveSelectedIdeas = () => {
      const selected = self.generatedIdeas().filter(i => i.isChecked() && !i.isSaved());
      if (selected.length === 0) return;

      self.isSaving(true);
      
      const payload = {
        // text() として呼び出すことで最新の入力値を取得
        ideas: selected.map(i => i.text()), 
        fuel_csrf_token: '<?php echo \Security::fetch_token(); ?>'
      };

      $.post('<?php echo \Uri::create("ideas/add_bulk"); ?>', payload)
        .done(() => {
          selected.forEach(i => {
          i.isSaved(true);
          i.isChecked(false);
            i.isEditing(false);
        });
          alert('ネタを保存しました！一覧画面へ移動します。');
        window.location.href = '<?php echo \Uri::create("ideas/index"); ?>';
      })
        .fail(() => { 
        alert('保存に失敗しました'); 
        self.isSaving(false);
      });
    };
  };

  ko.applyBindings(new IdeaGenerateViewModel(), document.getElementById('generate-app'));
});
</script>