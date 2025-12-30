# sharee 開発の基本

このリポジトリはWordPressプラグインです。ただし、composer経由でも使えるように、プラグインメインファイル `sharee.php` は簡素な作りにしています。

- PHPバージョンはcomposer.jsonに記載
- nodeバージョンはpackage.jsonに記載

## 構文チェック

構文テストなどは以下のツールを使ってください。

```bash
# PHP構文チェック
composer lint
# 構文自動修正
composer fix

# JS
npm run lint:js
# CSS
npm run lint:css

## 自動修正
npm run fix:js
npm run fix:css
```

## 環境

`@wordpress/env` でDocker環境を構築しています。

```bash
# スタート
npm start

# 終了
npm stop

# CLIを実行（wpコマンドを実行できる）
npm run cli plugin list

# PHPUnitをコンテナの中で実行
npm test
```

## ブラウザでの確認

一部の機能はログインが必要です。この場合、定数 `HAMETUAH_LOGGED_IN_AS` を `wp-config.php` に定義することで、特定のユーザーとして振る舞うことができます。
ローカルのデフォルト管理者は `admin` です。
詳細は `tests/auto-login.php` をご覧ください。
WordPressの管理画面のほか、Hashboardが提供する `http://localhost:8888/dashboard/` もログインが必要です。

```
# adminとしてログインしている
npm run cli config set HAMETUHA_LOGGED_IN_AS admin
# 元に戻す
npm run cli config delete HAMETUHA_LOGGED_IN_AS 
```

Chrome MCP ServerなどはCookieの取り回しに問題があるので、この機能を使ってください。
