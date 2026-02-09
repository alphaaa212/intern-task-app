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
     * Gemini AIネタ生成（サーバー経由でAPI呼び出し）
     */
    self.generateIdeas = () => {
      const trimmedInput = self.rawInput().trim();
      if (!trimmedInput) return;

      // 状態を「生成中」にする
      self.isGenerating(true);
      self.generatedIdeas([]); // 以前の結果をクリア

      // サーバー側の post_api_generate メソッドに送信
      const payload = {
        user_input: trimmedInput,
        fuel_csrf_token: window.AppConfig.csrfToken,
      };

      $.post(window.AppConfig.endpoints.generateApi, payload)
        .done((res) => {
          // 成功時もトークンを更新
          if (res.new_token) window.AppConfig.csrfToken = res.new_token;
          if (res.status === "success" && res.ideas) {
            // 返ってきたネタ（配列）をModelに変換してセット
            const mappedIdeas = res.ideas.map(
              (text) => new IdeaItemModel(text),
            );
            self.generatedIdeas(mappedIdeas);
          } else {
            alert(
              "ネタの生成に失敗しました: " + (res.message || "不明なエラー"),
            );
          }
        })
        .fail((xhr) => {
          // 失敗時もトークンを更新
          const res = xhr.responseJSON;
          if (res && res.new_token) window.AppConfig.csrfToken = res.new_token;

          console.error("--- API Error Details ---");
          if (res && res.debug_info) {
            console.log("HTTP Status:", res.debug_info.http_code);
            console.log("CURL Error:", res.debug_info.curl_error);
            console.log("Google Response:", res.debug_info.google_response);
          } else {
            console.dir(xhr);
          }
          console.error("-------------------------");

          alert(
            "通信エラーが発生しました。コンソール(F12)を確認してください。",
          );
        })
        .always(() => {
          self.isGenerating(false);
        });
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
          // マイネタ一覧へ遷移
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

  // Knockout.jsのバインディング開始
  const container = document.getElementById("generate-app");
  if (container) {
    ko.applyBindings(new IdeaGenerateViewModel(), container);
  }
});
