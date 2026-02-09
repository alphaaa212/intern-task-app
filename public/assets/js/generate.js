$(function () {
  /**
   * 生成された各ネタのデータモデル
   */
  const IdeaItemModel = function (textStr) {
    const self = this;
    // 編集可能な項目はobservable
    self.text = ko.observable(textStr);
    self.isChecked = ko.observable(false);
    self.isSaved = ko.observable(false);
    self.isEditing = ko.observable(false);
  };

  /**
   * メインViewModel
   */
  const IdeaGenerateViewModel = function () {
    const self = this;

    // 状態管理
    self.rawInput = ko.observable("");
    self.isGenerating = ko.observable(false);
    self.isSaving = ko.observable(false);
    self.generatedIdeas = ko.observableArray([]);

    // 算出プロパティ: チェックされた未保存のネタがあるか
    self.hasCheckedIdeas = ko.computed(() => {
      return self
        .generatedIdeas()
        .some((idea) => idea.isChecked() && !idea.isSaved());
    });

    /**
     * AIネタ生成（シミュレーション）
     */
    self.generateIdeas = () => {
      const trimmedInput = self.rawInput().trim();
      if (!trimmedInput) return;

      self.isGenerating(true);
      self.generatedIdeas([]); // リセット

      // 擬似的な生成待ち演出
      setTimeout(() => {
        const inputSnippet = trimmedInput.substring(0, 10);
        const suggestionTemplates = [
          `【漫才】「${inputSnippet}」の絶妙な違和感`,
          `【コント】自称「${inputSnippet}」の達人`,
          `【エッセイ】なぜ現代人は「${inputSnippet}」に惹かれるのか`,
          `【大喜利】「${inputSnippet}」を100倍楽しくする方法`,
          `【短編】「${inputSnippet}」から始まる物語`,
        ];

        const mappedIdeas = suggestionTemplates.map(
          (template) => new IdeaItemModel(template),
        );

        self.generatedIdeas(mappedIdeas);
        self.isGenerating(false);
      }, 800);
    };

    /**
     * 選択したネタの一括保存
     */
    self.saveSelectedIdeas = () => {
      const selectedIdeas = self
        .generatedIdeas()
        .filter((idea) => idea.isChecked() && !idea.isSaved());
      if (selectedIdeas.length === 0) return;

      self.isSaving(true);

      const payload = {
        ideas: selectedIdeas.map((idea) => idea.text()),
        fuel_csrf_token: window.AppConfig.csrfToken,
      };

      $.post(window.AppConfig.endpoints.saveIdeas, payload)
        .done(() => {
          selectedIdeas.forEach((idea) => {
            idea.isSaved(true);
            idea.isChecked(false);
            idea.isEditing(false);
          });
          alert("ネタを保存しました！");
          window.location.href = window.AppConfig.endpoints.redirectUrl;
        })
        .fail(() => {
          alert("保存に失敗しました");
        })
        .always(() => {
          self.isSaving(false);
        });
    };
  };

  // バインディングの開始
  const container = document.getElementById("generate-app");
  if (container) {
    ko.applyBindings(new IdeaGenerateViewModel(), container);
  }
});
