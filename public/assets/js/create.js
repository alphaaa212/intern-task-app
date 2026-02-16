$(function () {
  $("#cancelBtn").click(function (e) {
    // デフォルトのリンク遷移を一度とめる
    e.preventDefault();

    // 2. 入力フィールドの値を取得（念のため前後の空白を削除）
    const ideaText = $('input[name="idea_text"]').val().trim();
    const targetUrl = $(this).attr("href"); // 遷移先のURLを取得（ideas/index）

    //入力がある場合のみ確認ダイアログを出す
    if (ideaText !== "") {
      if (!confirm("入力された内容は消えますがよろしいですか？")) {
        return false;
      }
    }

    //入力がない、または確認でOKが押された場合はリダイレクト
    window.location.href = targetUrl;
  });
});
