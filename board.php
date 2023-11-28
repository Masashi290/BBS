<?php
session_start();
mb_internal_encoding("utf8");
//ログインされていない場合は強制的にログインページにリダイレクト
if (!isset($_SESSION["id"])) {
    header("Location: login.php");
}
//変数の初期化
$errors = array();

//ログインされている場合は表示用メッセージを編集
$message = "こんにちは".$_SESSION['name']."さん";
$message = htmlspecialchars($message);

//POSTアクセス時の処理
if ($_SERVER["REQUEST_METHOD"] == "POST"){
    //エスケープ処理
    $input["title"] = htmlentities($_POST["title"] ?? "", ENT_QUOTES);
    $input["comments"] = htmlentities($_POST["comments"] ?? "", ENT_QUOTES);

    if (strlen(trim($input["title"] ?? "")) == 0) {  //入力されているかの確認
        $errors["title"] = "タイトルを入力してください。";
    }
    if (strlen(trim($input["comments"] ?? "")) == 0) {  //入力されているかの確認
        $errors["comments"] = "コメントを入力してください。";
    }

    if (empty($errors)) {
        try {
            $pdo = new PDO("mysql:dbname=php_jissen;host=localhost;","root",""); //DBに接続
            $pdo->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
            $stmt = $pdo->prepare(" INSERT INTO post(user_id,title,comments) VALUES(?,?,?)"); //DB(postテーブル)にデータ挿入
            $stmt->execute(array($_SESSION["id"],$input["title"],$input["comments"]));
            $pdo = NULL; //DB切断
        } catch (PDOException $e) {
            $e->getMessage(); //例外発生時にエラーメッセージを出力
        }
    }
}

//GET･POSTアクセス時の処理
try {
    $pdo = new PDO("mysql:dbname=php_jissen;host=localhost;","root",""); //DB接続
    $pdo->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
    $posts = $pdo->query(" SELECT title,comments,name,posted_at FROM post INNER JOIN user ON post.user_id = user.id ORDER BY posted_at DESC ");
    $pdo = NULL; //DB切断
} catch (PDOException $e) {
    $e->getMessage(); //例外発生時にエラーメッセージを出力
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" type="text/css" href="board.css">
    <title>4eachblog</title>
</head>
<body>
    <div class=topMenu>
        <img class="logoImg" src="img/4eachblog_logo.jpg">
        <div class=loginName>
        <?php echo $message;?>    
            <form action="logout.php">
	            <input type="submit" class="button1" value="ログアウト">
            </form>
        </div>
    </div>
    <header>
        <ul>
            <li>トップ</li>
            <li>プロフィール</li>
            <li>4eachについて</li>
            <li>登録フォーム</li>
            <li>問い合わせ</li>
            <li>その他</li>
        </ul>
    </header>
    
    <main>
        <div class="left">
            <h1>プログラミングに役立つ掲示板</h1>
            <form method="POST" action="board.php">
                <h2 class=form_title>入力フォーム</h2>
                <div class=item>
                    <label>タイトル</label>
                    <input type="text" class="text" name="title" value="<?php echo $_SESSION["title"] ?? ""; ?>">
                    <?php if(!empty($errors["title"])) : ?>
                        <p class="err_message"><?php echo $errors["title"];?></p>
                    <?php endif ?>
                </div>
                <div class=item>
                    <label>コメント</label>
                    <textarea name="comments" value="<?php echo $_SESSION["comments"] ?? ""; ?>"></textarea>
                    <?php if(!empty($errors["comments"])): ?>
                        <p class="err_message"><?php echo $errors["comments"]; ?></p>
                    <?php endif; ?>
                </div>
                <div class="item">
                    <input type="submit" class="submit" value="送信する">
                </div>
            </form>
            <?php foreach ($posts as $post) : ?>
                <div class="tweet">
                    <h2><?php echo $post["title"] ?></h2>
                    <div class="contents"><?php echo $post["comments"] ?></div>
                    <div class="contributor">投稿者：<?php echo $post["name"]; ?></div>
                    <div class="time">投稿時間：
                        <?php
                        $posted_at = new DateTime($post["posted_at"]);
                        echo $posted_at->format("Y年m月d日 H:i");
                        ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <div class="right">
            <h3>人気の記事</h3>
            <ul>
                <li>PHPオススメ本</li>
                <li>PHP MyAdminの使い方</li>
                <li>今人気のエディタ Top5</li>
                <li>HTMLの基礎</li>
            </ul>
            <h3>オススメリンク</h3>
            <ul>
                <li>インターノウス株式会社</li>
                <li>XAMPPのダウンロード</li>
                <li>Eclipseのダウンロード</li>
                <li>Bracketsのダウンロード</li>
            </ul>
            <h3>カテゴリ</h3>
            <ul>
                <li>HTML</li>
                <li>PHP</li>
                <li>MySQL</li>
                <li>JavaScript</li>
            </ul>
        </div>
    </main>

    <footer>
        <p>copyright © internous | 4each blog the which provides A to Z about programming.</p>
    </footer>
</body>
</html>