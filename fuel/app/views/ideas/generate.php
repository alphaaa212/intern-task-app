<?php 
echo \Asset::js('jquery.min.js');
echo \Asset::js('knockout.js');
?>

<div id="generate-area" class="container mt20" style="display: none;" data-bind="visible: true">
  <h2 class="mb20">💡 ネタの種を撒く</h2>

  <div class="card shadow-sm mb20">
    <div class="card-body">
      <div class="form-group">
        <textarea class="form-control" rows="5" 
          data-bind="value: rawInput" 
          placeholder="今の正直な思いを吐き出してください（ここに入力した内容からネタを抽出します）"></textarea>
      </div>
      <button class="btn btn-primary btn-block btn-lg" data-bind="click: generateIdeas, disable: isGenerating">
        <span data-bind="text: isGenerating() ? '生成中...' : 'ネタを生成する'"></span>
      </button>
    </div>
  </div>

  <div class="row" data-bind="visible: generatedIdeas().length > 0">
    <div class="col-12">
      <div class="card shadow-sm">
        <div class="card-header bg-white">
          <h4 class="mb0">✨ 提案されたネタ案</h4>
        </div>
        <div class="card-body p-0">
          <ul class="list-group list-group-flush" data-bind="foreach: generatedIdeas">
            <li class="list-group-item d-flex align-items-center py-3">
              <div class="mr15" data-bind="visible: !isSaved()">
                <input type="checkbox" style="width:25px; height:25px;" data-bind="checked: isChecked">
              </div>
              <div class="flex-grow-1">
                <span class="h5 mb0" data-bind="text: text, style: { color: isSaved() ? '#bbb' : '#333' }"></span>
              </div>
              <div data-bind="visible: isSaved">
                <span class="badge badge-success p-2">✓ 保存済み</span>
              </div>
            </li>
          </ul>
        </div>
        
        <div class="card-footer text-center bg-white py-3" data-bind="visible: hasCheckedIdeas">
          <button class="btn btn-success btn-lg px-5 shadow" data-bind="click: saveSelectedIdeas, disable: isSaving">
            <span data-bind="text: isSaving() ? '保存中...' : 'チェックしたネタを保存して一覧へ'"></span>
          </button>
        </div>
      </div>
    </div>
  </div>
</div>

<style>
  .mt20 { margin-top: 20px; }
  .mb20 { margin-bottom: 20px; }
  .mr15 { margin-right: 15px; }
  .mb0 { margin-bottom: 0; }
  .badge-success { background-color: #28a745; color: white; }
  /* placeholderの見栄えを少し調整 */
  textarea::placeholder { color: #adb5bd; opacity: 1; }
</style>

<script>
$(function() {
  function IdeaGenerateViewModel() {
    var self = this;
    self.rawInput = ko.observable(""); 
    self.isGenerating = ko.observable(false);
    self.isSaving = ko.observable(false);
    self.generatedIdeas = ko.observableArray([]);

    self.hasCheckedIdeas = ko.computed(function() {
      return self.generatedIdeas().some(function(item) {
        return item.isChecked() && !item.isSaved();
      });
    });

    self.generateIdeas = function() {
      if (!self.rawInput().trim()) return;
      self.isGenerating(true);
      self.generatedIdeas([]);

      setTimeout(function() {
        var input = self.rawInput().substring(0, 5);
        var dummy = [
          "【漫才】「" + input + "」の悩み",
          "【コント】「" + input + "」の専門家",
          "【エッセイ】「" + input + "」の考察",
          "【大喜利】「" + input + "」な一言",
          "【短編】「" + input + "」から始まる物語"
        ];

        var mapped = dummy.map(function(t) {
          return {
            text: t,
            isChecked: ko.observable(false),
            isSaved: ko.observable(false)
          };
        });
        self.generatedIdeas(mapped);
        self.isGenerating(false);
      }, 1000);
    };

    self.saveSelectedIdeas = function() {
      var selected = self.generatedIdeas().filter(function(i) {
        return i.isChecked() && !i.isSaved();
      });
      self.isSaving(true);
      
      $.post('<?php echo \Uri::create("ideas/add_bulk"); ?>', {
        ideas: selected.map(function(i) { return i.text; }),
        fuel_csrf_token: '<?php echo \Security::fetch_token(); ?>'
      })
      .done(function() {
        selected.forEach(function(i) {
          i.isSaved(true);
          i.isChecked(false);
        });
        alert('保存しました！一覧画面へ移動します。');
        // 保存成功後に一覧画面へリダイレクト
        window.location.href = '<?php echo \Uri::create("ideas/index"); ?>';
      })
      .fail(function() { 
        alert('保存に失敗しました'); 
        self.isSaving(false);
      });
    };
  }
  ko.applyBindings(new IdeaGenerateViewModel(), document.getElementById('generate-area'));
});
</script>