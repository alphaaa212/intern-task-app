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

# intern-task-app

## これは何？

インターン生課題の一環で進めている個人開発プロジェクトです。

## 一言で表すと

（Note）記事作成のネタ提案・管理アプリ

## 機能要件

- 新規登録、ログイン、ログアウト
- 自由記述によるテキスト入力→gemini AIによる入力内容の解析＆ネタ提案→AIの提案結果（出力）の表示（チェックボックス）
- チェックを付けた提案の保存、一覧表示
- ネタ一覧画面でのネタの追加、削除、編集機能

## 使い方

1. リポジトリをクローンする
2. 必要なライブラリをインストール
3. プログラムを実行

## 作成者

https://github.com/alphaaa212
