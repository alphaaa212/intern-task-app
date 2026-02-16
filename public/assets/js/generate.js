// ページの読み込みが終わってから実行(jQuery)
$(function () {
  /**
   * 生成された各ネタのデータモデル
   */
  // タイトルの文字列61行目のtextがtextStrに代入される
  const IdeaItemModel = function (idea_text_str) {
    // self = 生成されたネタ一行分のオブジェクト
    const self = this;
    // observableを用いることで、値が変わったら画面にも反映させる
    self.text = ko.observable(idea_text_str); //確定済みの文章（値）
    self.isChecked = ko.observable(false); // チェックが入っているか
    self.isSaved = ko.observable(false); //保存されたか
    self.isEditing = ko.observable(false); //今、編集中か
    // --- 追加: 編集中の未確定テキストを保持するObservable ---
    self.editText = ko.observable("");

    // 編集開始
    self.startEdit = () => {
      // 編集を開始する瞬間に、現在の値を編集用フィールドにコピーする
      self.editText(self.text());
      self.isEditing(true);
    };

    // 確定（保存）
    self.confirmEdit = () => {
      const newVal = self.editText().trim();
      if (!newVal) {
        self.cancelEdit();
        return;
      }
      // 確定ボタンを押した時だけ、元の text に値を反映させる
      self.text(newVal);
      self.isEditing(false);
    };

    // キャンセル（戻す）
    self.cancelEdit = () => {
      self.isEditing(false);
    };
  };

  /**
   * メインViewModel
   */
  const IdeaGenerateViewModel = function () {
    // self = アプリ全体の動きを管理する ViewModel（頭脳）自体
    const self = this;

    // ---  画面全体の「状態」を管理する変数 ---
    self.rawInput = ko.observable(""); //ユーザーの入力内容
    self.isGeneratingStatus = ko.observable(false); //生成通信中フラグ
    self.isSavingStatus = ko.observable(false); //保存通信中フラグ
    self.generatedIdeasList = ko.observableArray([]); //生成されたネタのリスト

    // --- 追加: ボタンのテキストを動的に切り替える計算プロパティ ---
    self.generateButtonText = ko.computed(() => {
      if (self.isGeneratingStatus()) return "抽出中...";
      return self.generatedIdeasList().length > 0
        ? "ネタを再生成する"
        : "ネタを生成する";
    });

    // 算出プロパティ: チェック済みかつ未保存のネタが1つでもあるか
    self.hasCheckedIdeas = ko.computed(() => {
      return self
        .generatedIdeasList() // 今あるネタの中から
        .some((idea_item) => idea_item.isChecked() && !idea_item.isSaved()); // 条件に合うものを探す
    });

    /**
     * AIにネタを生成してもらうボタンを押した時の処理
     */
    // 入力欄が空なら、処理を実行しない
    self.generateIdeas = () => {
      const trimmed_input_text = self.rawInput().trim();
      if (!trimmed_input_text) return;

      // 状態を「生成中」にする（画面のボタンが『抽出中...』に変わる）
      self.isGeneratingStatus(true);
      self.generatedIdeasList([]); // 再生成時も含め前のリストをクリア

      // サーバー側の post_api_generate メソッドに送信
      const generate_request_data = {
        user_input: trimmed_input_text,
        fuel_csrf_token: window.AppConfig.csrfToken, // PHPから渡されたセキュリティ用の合言葉
      };

      //jQueryを使ってPHPに「AIでネタ作って！」とリクエスト
      $.post(window.AppConfig.endpoints.generateApi, generate_request_data)
        .done((api_response) => {
          //通信が成功したら...
          if (api_response.new_token)
            window.AppConfig.csrfToken = api_response.new_token; //トークンを更新

          if (api_response.status === "success" && api_response.ideas) {
            // 返ってきた文字列（ネタ）をModelに変換する処理を5件分繰り返す
            const formatted_idea_objects = api_response.ideas.map(
              (single_idea_text) => new IdeaItemModel(single_idea_text),
            );
            //配列にセット（HTMLのforeach 部分が動き、ネタの数だけIdeaItemModel（機能を備えたネタデータ）のリストとして表示される）
            self.generatedIdeasList(formatted_idea_objects);
          } else {
            alert(
              "ネタの生成に失敗しました: " +
                (api_response.message || "不明なエラー"),
            );
          }
        })
        .fail((xhr_error_object) => {
          // 失敗時もトークンを更新
          const error_response = xhr_error_object.responseJSON;
          if (error_response && error_response.new_token) {
            window.AppConfig.csrfToken = error_response.new_token;
          }
          alert("通信エラーが発生しました。");
        })
        .always(() => {
          self.isGeneratingStatus(false);
        });
    };

    /**
     * 選択したネタの一括保存
     */
    self.saveSelectedIdeaList = () => {
      const checked_idea_items = self
        .generatedIdeasList() // チェック済みのネタだけを抜き出す
        .filter((idea_item) => idea_item.isChecked() && !idea_item.isSaved());

      if (checked_idea_items.length === 0) return;

      self.isSavingStatus(true);

      const save_request_data = {
        ideas: checked_idea_items.map((item) => item.text()),
        fuel_csrf_token: window.AppConfig.csrfToken,
      };

      // PHPに「これらを保存して！」とリクエスト
      $.post(window.AppConfig.endpoints.saveIdeas, save_request_data)
        .done(() => {
          // 保存できたら、画面上のネタを「保存済み」状態に変える
          checked_idea_items.forEach((item) => {
            item.isSaved(true);
            item.isChecked(false);
            item.isEditing(false);
          });
          alert("ネタを保存しました！");
          // マイネタ一覧へ遷移
          window.location.href = window.AppConfig.endpoints.redirectUrl;
        })
        .fail(() => {
          alert("保存に失敗しました");
        })
        .always(() => {
          self.isSavingStatus(false);
        });
    };
  };

  // 起動処理
  const app_container_element = document.getElementById("generate-app");
  if (app_container_element) {
    ko.applyBindings(new IdeaGenerateViewModel(), app_container_element);
  }
});
