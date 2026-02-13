// DOM(HTMLの構造)の読み込みが完了してから実行する
$(function () {
  /**
   * 単体ネタのデータモデル（1件ごとのデータを管理する設計図）
   */
  const IdeaItem = function (data) {
    const self = this;
    // IDを数値として保持（変更されないので監視対象外）
    self.id = Number(data.id);
    // ネタの本文をKnockoutの監視対象(observable)にして、変更が画面に即反映されるようにする
    self.idea_text = ko.observable(data.idea_text);
    // お気に入り状態を真偽値に変換して監視対象にする
    self.is_favorite = ko.observable(String(data.is_favorite) === "1");
    // このネタが現在編集モードかどうかを管理する（初期値はfalse）
    self.isEditing = ko.observable(false);
  };

  /**
   * ネタ一覧ViewModel（画面全体の動きを制御するメインの処理）
   */
  const IdeaListViewModel = function (rawIdeas) {
    const self = this;
    // HTML側で定義した window.AppConfig（設定値やURL）を取得
    const config = window.AppConfig;
    // 現在のCSRFトークン（セキュリティ用の合言葉）を保持
    let currentCsrfToken = config.csrf.token;

    // 取得した生の配列データを、IdeaItemモデルの配列に変換して監視対象にする
    self.ideas = ko.observableArray(rawIdeas.map((item) => new IdeaItem(item)));
    // 現在のフィルタ状態（すべて: all / お気に入り: fav）を管理
    self.filterMode = ko.observable("all");

    // 画面に表示する用のネタ一覧（フィルタの結果を自動計算する）
    self.filteredIdeas = ko.computed(() => {
      // 現在のフィルタモードを取得
      const mode = self.filterMode();
      // すべてのネタリストを取得
      const allIdeas = self.ideas();
      // モードがallならそのまま、favならお気に入りがtrueのものだけを抽出して返す
      return mode === "all"
        ? allIdeas
        : allIdeas.filter((idea) => idea.is_favorite());
    });

    /**
     * トークン更新処理（サーバーから新しいセキュリティトークンが届いたら上書きする）
     */
    const updateCsrfToken = (response) => {
      if (response && response.new_token) {
        currentCsrfToken = response.new_token;
      }
    };

    /**
     * お気に入り切り替え（★ボタンを押した時の処理）
     */
    self.toggleFav = (item) => {
      // 現在の値を反転させる（trueならfalseに、falseならtrueに）
      item.is_favorite(!item.is_favorite());
      // サーバーに更新リクエストを送る
      self.sendUpdate(item);
    };

    /**
     * 編集保存（保存ボタンを押した時の処理）
     */
    self.saveEdit = (item) => {
      // サーバーに現在の内容を送信
      self.sendUpdate(item);
      // 編集モードを終了して通常表示に戻す
      item.isEditing(false);
    };

    /**
     * 更新リクエスト共通（サーバーと通信する処理）
     */
    self.sendUpdate = (item) => {
      // 送信するデータを作成（ID、テキスト、お気に入り状態）
      const postData = {
        id: item.id,
        idea_text: item.idea_text(),
        is_favorite: item.is_favorite() ? 1 : 0,
      };
      // 規約に基づき、セキュリティ用のCSRFトークンをデータに含める
      postData[config.csrf.key] = currentCsrfToken;

      // サーバーの保存用URLにPOST送信を行う
      $.post(config.endpoints.save, postData)
        .done((res) => updateCsrfToken(res)) // 成功したらトークンを更新
        .fail((xhr) => {
          // 失敗時の処理
          if (xhr.responseJSON) updateCsrfToken(xhr.responseJSON);
          alert("保存に失敗しました。");
        });
    };

    /**
     * 削除処理
     */
    self.deleteIdea = (item) => {
      // 確認ダイアログを表示し、キャンセルされたら何もしない
      if (!confirm("このネタを削除してもよろしいですか？")) {
        return;
      }

      // 送信用にIDを用意
      const deleteData = { id: item.id };
      // CSRFトークンをセット
      deleteData[config.csrf.key] = currentCsrfToken;

      // サーバーの削除用URLにPOST送信
      $.post(config.endpoints.delete, deleteData)
        .done((res) => {
          updateCsrfToken(res);
          // 成功したら、画面上のリスト(ideas)からその項目を取り除く
          self.ideas.remove(item);
        })
        .fail((xhr) => {
          if (xhr.responseJSON) updateCsrfToken(xhr.responseJSON);
          alert("削除に失敗しました。");
        });
    };
  };

  // 画面上に ideas-app というIDの要素があるか確認
  const container = document.getElementById("ideas-app");
  if (container) {
    // Knockout.jsを起動し、データをHTML要素にバインド（紐付け）する
    ko.applyBindings(
      new IdeaListViewModel(window.AppConfig.ideasData),
      container,
    );
  }
});
