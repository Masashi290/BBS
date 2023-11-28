<?php

session_start();
mb_internal_encoding("utf8");

if (isset($_SESSION['id'])) {
    header("Location:board.php");
}

//エラーメッセージを扱う変数の初期化
$errors = "";

// POSTアクセス時の処理
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // エスケープ処理
    $input["mail"] = htmlentities($_POST["mail"] ?? "", ENT_QUOTES);
    $input["password"] = htmlentities($_POST["password"] ?? "", ENT_QUOTES);

    // バリデーションチェック
    if (!filter_input(INPUT_POST, "mail", FILTER_VALIDATE_EMAIL)) { // メールの形式確認
        $errors = "メールアドレスとパスワードを正しく入力してください。";
    }
    if (strlen(trim($_POST["password"] ?? "")) == 0) {  //入力されているかの確認
        $errors = "メールアドレスとパスワードを正しく入力してください。";
    }

    // ログイン認証
    if (empty($errors)) {
        // DBに接続し一致するメールアドレスを取り出す
        try {
            $pdo = new PDO("mysql:dbname=php_jissen;host=localhost;", "root", ""); // DBに接続
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); // エラーモードを「警告」に設定
            $stmt = $pdo->prepare(" SELECT * FROM user WHERE mail = ? ");
            $stmt->execute(array($input["mail"]));
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            $pdo = NULL; // DB切断
        } catch (PDOException $e) {
            $e->getMessage(); // 例外発生時にエラーメッセージを出力
        }

        // ユーザー情報が取り出し　かつ　パスワードが一致していれば代入し、board.phpへ
        if (!empty($user) && password_verify($input["password"], $user["password"])) {
            // SESSIONに値を格納
            $_SESSION['id'] = $user['id'];
            $_SESSION['name'] = $user['name'];
            $_SESSION['mail'] = $user['mail'];
            $_SESSION['password'] = $input['password'];

            //ログイン情報を保持する　がチェックされていればセッションにセットする
            if ($_POST['login_keep'] == 1) {
                $_SESSION['login_keep'] = $_POST['login_keep'];
            }

            // ログイン情報を保持する　にチェックがあればクッキーをセット、なければ削除
            if (!empty($_SESSION['id']) && !empty($_SESSION['login_keep'])) {
                setcookie('mail', $_SESSION['mail'], time() + 60 * 60 * 24 * 7);
                setcookie('password', $_SESSION['password'], time() + 60 * 60 * 24 * 7);
                setcookie('login_keep', $_SESSION['login_keep'], time() + 60 * 60 * 24 * 7);
            } else if (empty($_SESSION['login_keep'])) {
                setcookie('mail', '', time() - 1);
                setcookie('password', '', time() - 1);
                setcookie('login_keep', '', time() - 1);
            }

            // board.phpにリダイレクト
            header("Location:board.php");
        } else {
            $errors = "メールアドレスとパスワードを正しく入力してください。";
        }
    }
}

?>

<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <title>ログインページ</title>
    <link rel="stylesheet" type="text/css" href="login.css">
</head>

<body>
    <h1 class="form_title">ログインページ</h1>
    <form method="POST" action="">
        <div class="item">
            <label>メールアドレス</label>
            <input type="text" class="text" size="35" name="mail" value="<?php
                                                                            if ($_COOKIE['login_keep'] ?? '') {
                                                                                echo $_COOKIE['mail'];
                                                                            }
                                                                        ?>">
        </div>
        <div class="item">
            <label>パスワード</label>
            <input type="password" class="text" size="35" name="password" value="<?php
                                                                                    if ($_COOKIE['login_keep'] ?? '') {
                                                                                        echo $_COOKIE['password'];
                                                                                    }
                                                                                ?>">
            <?php if (!empty($errors)) : ?>
                <p class="err_message"><?php echo $errors; ?></p>
            <?php endif; ?>
        </div>
        <div class="item">
            <label>
                <input type="checkbox" name="login_keep" value="1" <?php
                                                                        if ($_COOKIE['login_keep'] ?? '') {
                                                                            echo "checked='checked'";
                                                                        }
                                                                    ?>>ログイン状態を保持する
            </label>
        </div>
        <div class="item">
            <input type="submit" class="submit" value="ログイン">
        </div>
    </form>
</body>

</html>