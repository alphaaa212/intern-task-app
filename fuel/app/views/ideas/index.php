<div class="container">
    <h2 class="my-4">保存したネタ一覧</h2>

    <div class="d-flex justify-content-between align-items-center my-4">
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
        <div class="list-group" data-bind="foreach: filteredIdeas">
            <div class="list-group-item mb-3 shadow-sm">
                
                <div data-bind="ifnot: isEditing">
                    <div class="d-flex w-100 justify-content-between align-items-center">
                        <h5 class="mb-1 text-primary">
                            <span style="cursor: pointer;" data-bind="click: $parent.toggleFav, text: is_favorite() ? '★' : '☆'" class="text-warning mr-2"></span>
                            <span data-bind="text: idea_text"></span>
                        </h5>
                        <div>
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

<script src="https://cdnjs.cloudflare.com/ajax/libs/knockout/3.5.1/knockout-latest.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
function IdeaViewModel(rawIdeas) {
    var self = this;

    // トークン管理用の変数
    var csrf_key = '<?php echo \Config::get('security.csrf_token_key', 'fuel_csrf_token'); ?>';
    var current_csrf_token = '<?php echo \Security::fetch_token(); ?>';

    // ★リファクタリング：トークン更新の共通処理
    self.updateCsrfToken = function(response) {
        if (response && response.new_token) {
            current_csrf_token = response.new_token;
            console.log("Token updated");
        }
    };

    // データの初期化
    self.ideas = ko.observableArray(rawIdeas.map(function(item) {
        return {
            id: item.id,
            idea_text: ko.observable(item.idea_text),
            is_favorite: ko.observable(Number(item.is_favorite) === 1),
            isEditing: ko.observable(false)
        };
    }));

    self.filterMode = ko.observable('all');

    self.filteredIdeas = ko.computed(function() {
        var mode = self.filterMode();
        if (mode === 'all') return self.ideas();
        return self.ideas().filter(function(i) { return i.is_favorite(); });
    });

    self.toggleFav = function(item) {
        item.is_favorite(!item.is_favorite());
        self.sendUpdate(item);
    };

    self.saveEdit = function(item) {
        self.sendUpdate(item);
        item.isEditing(false);
    };

    // 保存・更新処理
    self.sendUpdate = function(item) {
        var data = {
            id: item.id,
            idea_text: item.idea_text(),
            is_favorite: item.is_favorite() ? 1 : 0
        };
        data[csrf_key] = current_csrf_token;

        $.post('<?php echo \Uri::create("ideas/save"); ?>', data)
            .done(function(res) {
                self.updateCsrfToken(res);
        })
        .fail(function(xhr) { 
                // 失敗時もJSONレスポンスがあればトークンを更新する
                if (xhr.responseJSON) self.updateCsrfToken(xhr.responseJSON);
                alert('通信に失敗しました。'); 
        });
    };

    // 削除処理
    self.deleteIdea = function(item) {
        if (!confirm('本当に削除しますか？')) return;
        
        var data = {};
        data[csrf_key] = current_csrf_token;

        $.post('<?php echo \Uri::create("ideas/delete/"); ?>' + item.id, data)
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

// サーバーから渡された初期データをバインド
var dataFromPhp = <?php echo $ideas_json ?? '[]'; ?>;
ko.applyBindings(new IdeaViewModel(dataFromPhp));
</script>