# インターン課題環境構築手順

## Dockerの基本知識

Dockerの基本的な概念については、以下のリンクを参考にしてください：

- [Docker入門（1）](https://qiita.com/Sicut_study/items/4f301d000ecee98e78c9)
- [Docker入門（2）](https://qiita.com/takusan64/items/4d622ce1858c426719c7)

## セットアップ手順

1. **リポジトリをクローン**

   ```bash
   git clone <リポジトリURL>
   ```

2. **dockerディレクトリに移動**

   ```bash
   cd docker
   ```

3. **データベース名の設定**
   `docker-compose.yml` 内の `db` サービスにある `MYSQL_DATABASE` の値を、各自任意のデータベース名に設定してください。

   例:

   ```yaml
   environment:
     MYSQL_ROOT_PASSWORD: root
     MYSQL_DATABASE: <your_database_name> # 任意のデータベース名を指定
   ```

4. **Dockerイメージのビルド**

   ```bash
   docker-compose build
   ```

5. **コンテナの起動**
   ```bash
   docker-compose up -d
   ```
6. **ブラウザからlocalhostにアクセス**

## PHP周りのバージョン

- **PHP**: 7.3
- **FuelPHP**: 1.8

## ログについて

- **アクセスログ**: Dockerのコンテナのログ
- **FuelPHPのエラーログ**: /var/www/html/intern_kadai/fuel/app/logs/
  - 年月日ごとにログが管理されている
  - tail -f {見たいログファイル}でログを出力

## MySQLコンテナ設定

このプロジェクトには、MySQLを使用するDBコンテナが含まれています。設定は以下の通りです。

- **MySQLバージョン**: 8.0
- **ポート**: `3306`
- **環境変数**:
  - `MYSQL_ROOT_PASSWORD`: root
  - `MYSQL_DATABASE`: 各自設定したデータベース名

### アクセス情報

- **ホスト**: `localhost`
- **ポート**: `3306`
- **ユーザー名**: `root`
- **パスワード**: `root`
- **データベース名**: 各自設定した名前

# intern-task-app (Note記事ネタ提案・管理アプリ)

## 開発概要

「Noteなどの記事を書きたいが、ネタが思い浮かばない」「毎日投稿したいがハードルが高い」という悩みを解決するためのアプリです。
テーマや思考が明確になっていなくても、今の感情や考えを自由に書き連ねるだけで、Gemini AIが内容を解析し、具体的な記事のネタを提案します。

## 機能要件

- **ユーザー認証**: 新規登録、ログイン、ログアウト
- **AIネタ提案**: 自由記述テキストからGemini APIがネタを生成・表示
- **選択保存**: 提案されたネタから気に入ったものを選択して保存
- **ネタ管理（CRUD）**: 保存したネタの一覧表示、手動追加、編集、削除

## 使用技術

- **Backend**: FuelPHP 1.8 (PHP 7.3)
- **Frontend**: Knockout.js, jQuery
- **Infrastructure**: Docker, MySQL 8.0
- **External API**: Gemini API
- **Tool**: phpMyAdmin,Figma

## 使い方

1. リポジトリをクローンする
2. 必要なライブラリをインストール
3. プログラムを実行

## 作成者

https://github.com/alphaaa212
