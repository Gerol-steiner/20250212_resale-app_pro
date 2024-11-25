# COACHTECH フリマアプリ

## 環境構築

ここでは、COACHTECH フリマアプリをクローンし、ローカル環境で動作させるための手順を説明します。

---

### 1. プロジェクトをクローン

#### 【目的】

GitHub のリポジトリからプロジェクトをローカル環境にコピーします。

#### 【手順】

1. ターミナルで、クローンしたいディレクトリに移動します。
   ```bash
   cd /path/to/your/desired/directory
   ```
2. 以下のコマンドを実行してリポジトリをクローンします。

   ```bash
   git clone git@github.com:Gerol-steiner/20241112_resale-app.git

   ```

3. クローンしたディレクトリに移動します（必要であればディレクトリ名を変更）
   ```bash
   cd 20241112_resale-app
   ```

### 2. Docker でコンテナを起動

#### 【目的】

アプリケーションの実行に必要な PHP、MySQL、phpMyAdmin、Nginx などを Docker で立ち上げます。

#### 【手順】

1. 以下のコマンドを実行してコンテナを構築・起動します。
   ```bash
   docker-compose up -d --build
   ```
2. 起動後、コンテナが正常に起動していることを確認します。
   ```bash
   docker ps
   ```
3. 以下のコンテナが動作していることを確認してください。

- PHP
- MySQL
- Nginx
- phpMyAdmin

### 3. 「.env」ファイルの設定

#### 【目的】

環境変数を定義するための`.env`ファイルを作成し、設定を適用します。

#### 【手順】

1. PHP コンテナ内に入ります。
   ```bash
   docker-compose exec php bash
   ```
2. `.env.example`ファイルをコピーして`.env`ファイルを作成します。
   ```bash
   cp .env.example .env
   ```
3. `.env`ファイルに必要な情報を入力します。

   - `DB_`セクションにデータベースの情報を入力。
     ```bash
     DB_DATABASE=laravel_db
     DB_USERNAME=laravel_user
     DB_PASSWORD=laravel_pass
     ```
   - `MAIL_`セクションにメール設定を入力。
     ```bash
     MAIL_USERNAME=（各自設定）
     MAIL_PASSWORD=（各自設定）
     MAIL_FROM_ADDRESS=（各自設定）
     ```
   - `STRIPE_`セクションに Stripe の公開キーと秘密キーを入力。

   ```bash
   STRIPE_PUBLISHABLE_KEY=（各自設定）
   STRIPE_SECRET_KEY=（各自設定）

   ```

4. 補足：php コンテナから退出したいときは、以下のコマンドで退出します。
   ```bash
   exit
   ```

### 4. Composer パッケージのインストール

#### 【目的】

アプリケーションで必要なライブラリやパッケージをインストールします。

#### 【手順】

1. PHP コンテナ内に入ります。

   ```bash
   docker-compose exec php bash

   ```

2. コンテナ内で以下のコマンドを実行します。
   ```bash
   composer install
   ```

### 5. アプリケーションキーの生成

#### 【目的】

アプリケーションの暗号化キーを生成します。

#### 【手順】

1. PHP コンテナ内に入ります。

   ```bash
   docker-compose exec php bash

   ```

2. コンテナ内で以下のコマンドを実行します。
   ```bash
   php artisan key:generate
   ```

- `.env`ファイルの`APP_KEY`が生成されていることを確認してください。
- `APP_KEY`が生成されていないときは、`.env`ファイルを閉じた状態で手順「2.」を再度実行してください。

### 6. ダミーデータ用の商品画像を移動

#### 【目的】

シードデータに対応する商品画像やプロフィール画像を適切な場所に配置します。

#### 【手順】

1. 必要に応じ、PHP コンテナから退出します。

   ```bash
   exit

   ```

2. コンテナ外で以下のコマンドを実行します。
   ```bash
   mv src/public/images/uploads src/storage/app/public/
   ```

### 7. ストレージリンクの作成

#### 【目的】

ストレージディレクトリをパブリックディレクトリにリンクして、Web で画像を参照できるようにします。

#### 【手順】

1. PHP コンテナ内に入ります。

   ```bash
   docker-compose exec php bash

   ```

2. コンテナ内で以下のコマンドを実行します。
   ```bash
   php artisan storage:link
   ```

### 8. ストレージ権限の付与

#### 【目的】

アプリケーションが`storage`ディレクトリにアクセスできるようにします。

#### 【手順】

■ Linux 環境の場合:

1. PHP コンテナ内に入ります。

   ```bash
   docker-compose exec php bash

   ```

2. コンテナ内で以下のコマンドを実行します。
   ```bash
   chmod -R 775 storage
   chown -R www-data:www-data storage
   ```

■ その他の環境（Windows、macOS）の場合:

- ファイルシステムの GUI を使用するか、環境に応じた適切な方法で storage ディレクトリとその中のファイルに対して、Web サーバーが読み取り/書き込みできるように権限を設定してください。
- 具体的な方法は OS やサーバー設定によって異なるため、必要に応じてシステム管理者に相談するか、お使いの環境のドキュメントを参照してください。

※ 注意: 権限設定は適切なセキュリティを維持しつつ、アプリケーションが正常に動作するように行ってください。

### 9. データベースの準備

#### 【目的】

アプリケーションで使用するデータベースを構築します。

#### 【手順】

1. PHP コンテナ内に入ります。

   ```bash
   docker-compose exec php bash

   ```

2. コンテナ内で以下のコマンドを実行してマイグレーションを実行します。

   ```bash
   php artisan migrate

   ```

3. ダミーデータを作成します。
   ```bash
   php artisan db:seed
   ```

### ＜注意事項＞

1. 商品画像やプロフィール画像: 商品やプロフィール画像が正しい場所に配置されていない場合、アプリケーションが正常に動作しません。必ずダミーデータの画像を storage/app/public/uploads に移動してください。
2. 権限の付与: 特に storage ディレクトリへの書き込み権限が不足している場合、エラーが発生します。権限設定を確実に行ってください。
3. 環境依存: 上記の手順は Linux を想定しています。Windows や Mac の場合、コマンドが異なる場合がありますので適宜環境に合わせて調整してください。

---

## テスト環境と実行手順

テスト用のデータベースを準備し、プロジェクトのテストを適切に実行できる環境を整えます。

### 1. テスト用データベースの作成

#### 目的

テスト用データベースを作成して、テスト実行時に使用できるようにする。

#### 手順

1. MySQL コンテナに入る  
   以下のコマンドで MySQL コンテナに接続します:

```bash
docker-compose exec mysql bash
```

2. MySQL にログイン  
   コンテナ内で以下を実行して MySQL にログインします:

```bash
mysql -u root -p
```

※ パスワードは `docker-compose.yml` で設定した `MYSQL_ROOT_PASSWORD` を入力してください（例: `root`）。

3. テスト用データベースの作成  
   以下のコマンドでデータベースを作成します:

```bash
CREATE DATABASE demo_test; -- テーブル名は変更しないでください
SHOW DATABASES; -- 作成したデータベースが表示されれば成功
```

4. 権限の付与

   以下のコマンドを実行して、`laravel_user` に `demo_test`テーブル へのアクセス権を付与します:

```bash
GRANT ALL PRIVILEGES ON demo_test.* TO 'laravel_user'@'%';
```

権限変更を適用するために次のコマンドを実行します:

```bash
FLUSH PRIVILEGES;
```

5. ログアウトおよびコンテナ退出  
   以下を実行して MySQL からログアウトし、コンテナを退出します:

```bash
exit; -- MySQLログアウト
exit; -- コンテナから退出
```

---

### 2. テスト用環境設定

#### 目的

`.env.testing` を作成して、テスト用環境を設定する。

#### 手順

1. `.env.testing` を作成  
   PHP コンテナ内で以下を実行します:

```bash
cp .env .env.testing
```

3. `.env.testing` の設定  
   必要に応じて以下を編集します:

```bash
APP_ENV=testing
APP_KEY=  # 空のままにしてください
DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=demo_test  # 先ほど作成したデータベース名
DB_USERNAME=laravel_user  # docker-compose.ymlで設定した値
DB_PASSWORD=laravel_pass  # docker-compose.ymlで設定した値
```

5. アプリケーションキーの生成  
   以下のコマンドを実行してテスト用環境のキーを生成します:

```bash
php artisan key:generate --env=testing
```

7. 設定キャッシュのクリア（必要に応じて）  
   `.env.testing` の変更が反映されない場合に以下を実行してください:

```bash
php artisan config:clear
```

---

### 3. テスト用マイグレーション

#### 目的

テスト用データベースにテーブルを作成する。

#### 手順

PHP コンテナ内で以下のコマンドを実行しテーブルを作成します:

```bash
php artisan migrate --env=testing
```

---

### 4. テストの実行

#### 目的

プロジェクトの機能が期待通り動作するか確認する。

#### 手順

以下のいずれかのコマンドを使用してテストを実行します:

```bash
php artisan test
```

または、PHPUnit を直接使用する場合:

```bash
vendor/bin/phpunit
```

**Docker 環境で実行する場合**  
PHP コンテナ内で以下を実行してください:

```bash
docker-compose exec php bash
php artisan test
```

---

### 補足

- **`phpunit.xml` の設定について**  
  `phpunit.xml` はプロジェクトに既に含まれているため、基本的に編集は不要です。特殊な要件がある場合のみ修正してください。

- **テストデータの準備**  
  シードファイルを使用してテストデータを準備する場合、以下のコマンドを実行してください:

```bash
php artisan db:seed --env=testing
```

---

## 使用技術（実行環境）

以下の技術を使用しています：

### **バックエンド**

- Laravel Framework: 8.75
- PHP: ^7.3 | ^8.0
- Stripe SDK: ^16.2
- Laravel Fortify: ^1.19
- Laravel Sanctum: ^2.11
- Laravel UI: ^3.4
- Laravel Tinker: ^2.5

### **フロントエンド**

- Bootstrap: ^5.1.3
- Axios: ^0.21
- Laravel Mix: ^6.0.6
- Sass: ^1.32.11

### **その他**

- MySQL: 8.0.26 (Docker 公式イメージを使用)
- Nginx: 1.21.1 (Docker 公式イメージを使用)
- Composer: 2.8.1
- Docker: バージョンに依存せず最新の公式イメージを想定

---

## データベース設計

以下は本アプリケーションで使用しているデータベースの ER 図です。

![ER図](src/public/images/ER_diagram.png)

---

## アプリケーション URL 一覧

### ユーザー向け主要画面

- 開発環境トップページ: [http://localhost/](http://localhost/)
- ユーザー登録: [http://localhost/register](http://localhost/register)
- ログイン: [http://localhost/login](http://localhost/login)
- マイページ: [http://localhost/mypage](http://localhost/mypage)
- 商品一覧: [http://localhost/](http://localhost/)
- 商品詳細: [http://localhost/item/{item_id}](http://localhost/item/{item_id})
- 商品購入: [http://localhost/purchase/{id}](http://localhost/purchase/{id})
- プロフィール編集：[http://localhost/mypage/profile](http://localhost/mypage/profile)

### 開発者向けエンドポイント

- いいね機能: [POST] [http://localhost/like/{item_id}](http://localhost/like/{item_id})
- コメント機能: [POST] [http://localhost/item/{item_id}/comments](http://localhost/item/{item_id}/comments)

### 認証関連

- メール認証待ち: [http://localhost/register/pending](http://localhost/register/pending)
- メール認証: [GET] [http://localhost/email/verify/{id}/{hash}](http://localhost/email/verify/{id}/{hash})

正確なアプリケーションのルート構成は routes/web.php に定義されています。  
必要に応じて、該当ファイルをご確認いただき、最新のルートや追加エンドポイントをご参照ください。
