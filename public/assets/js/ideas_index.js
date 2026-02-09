$(function () {
  /**
   * 単体ネタのデータモデル
   */
  const IdeaItem = function (data) {
    const self = this;
    // idは変更されないためobservableにしない
    self.id = Number(data.id);
    self.idea_text = ko.observable(data.idea_text);
    self.is_favorite = ko.observable(String(data.is_favorite) === "1");
    self.isEditing = ko.observable(false);
  };

  /**
   * ネタ一覧ViewModel
   */
  const IdeaListViewModel = function (rawIdeas) {
    const self = this;
    const config = window.AppConfig;
    let currentCsrfToken = config.csrf.token;

    // 状態管理
    self.ideas = ko.observableArray(rawIdeas.map((item) => new IdeaItem(item)));
    self.filterMode = ko.observable("all");

    // フィルタリング処理（算出プロパティ）
    self.filteredIdeas = ko.computed(() => {
      const mode = self.filterMode();
      const allIdeas = self.ideas();
      return mode === "all"
        ? allIdeas
        : allIdeas.filter((idea) => idea.is_favorite());
    });

    /**
     * トークン更新処理
     */
    const updateCsrfToken = (response) => {
      if (response && response.new_token) {
        currentCsrfToken = response.new_token;
      }
    };

    /**
     * お気に入り切り替え
     */
    self.toggleFav = (item) => {
      item.is_favorite(!item.is_favorite());
      self.sendUpdate(item);
    };

    /**
     * 編集保存
     */
    self.saveEdit = (item) => {
      self.sendUpdate(item);
      item.isEditing(false);
    };

    /**
     * 更新リクエスト共通
     */
    self.sendUpdate = (item) => {
      const postData = {
        id: item.id,
        idea_text: item.idea_text(),
        is_favorite: item.is_favorite() ? 1 : 0,
      };
      postData[config.csrf.key] = currentCsrfToken;

      $.post(config.endpoints.save, postData)
        .done((res) => updateCsrfToken(res))
        .fail((xhr) => {
          if (xhr.responseJSON) updateCsrfToken(xhr.responseJSON);
          alert("保存に失敗しました。");
        });
    };

    /**
     * 削除処理
     */
    self.deleteIdea = (item) => {
      if (!confirm("このネタを削除してもよろしいですか？")) {
        return;
      }

      const deleteData = { id: item.id };
      deleteData[config.csrf.key] = currentCsrfToken;

      $.post(config.endpoints.delete, deleteData)
        .done((res) => {
          updateCsrfToken(res);
          self.ideas.remove(item);
        })
        .fail((xhr) => {
          if (xhr.responseJSON) updateCsrfToken(xhr.responseJSON);
          alert("削除に失敗しました。");
        });
    };
  };

  // バインドの実行
  const container = document.getElementById("ideas-app");
  if (container) {
    ko.applyBindings(
      new IdeaListViewModel(window.AppConfig.ideasData),
      container,
    );
  }
});
