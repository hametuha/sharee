# .gitattributes 生成コマンド

`.distignore`ファイルから`.gitattributes`を生成します。

## 処理内容

1. `.distignore`ファイルを読み込む
2. コメント行（#で始まる）と空行を除外
3. 各行の末尾に` export-ignore`を追加
4. Composer（Packagist）専用の除外項目を追加：
   - `sharee.php`（プラグインのメインファイルはComposerでは不要）
5. `.gitattributes`ファイルに書き出す

## 実行

上記の手順に従って`.gitattributes`を生成し、差分があれば報告してください。

$ARGUMENTS
