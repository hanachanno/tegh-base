<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>mission_5-1</title>
</head>
<body>
    好きな動物は？
    <?php
    try {
        // データベース接続情報
        $dsn = 'mysql:dbname=データベース名;host=localhost';
        $user = 'ユーザー名';
        $password = 'パスワード';
        $pdo = new PDO($dsn, $user, $password, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING));

        // テーブルが存在しない場合は作成する
        $sql = "CREATE TABLE IF NOT EXISTS posts (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(255),
            text TEXT,
            date DATETIME,
            password VARCHAR(255)
        )";

        if ($pdo->query($sql) === FALSE) {
            die("テーブルの作成に失敗しました: " . $pdo->errorInfo()[2]);
        }

        // 編集モード用の初期化
        $editMode = false;
        $eddnum = 0;
        $eddname = "";
        $eddtext = "";
        $password = "";

        // 編集ボタンが押された場合の処理
        if (isset($_POST["edit"])) {
            $hen = $_POST["hen"];
            $hen_password = $_POST["hen_password"];
            // 編集モード用のデータベースクエリを実行
            $sql = "SELECT * FROM posts WHERE id = ? AND password = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$hen, $hen_password]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($row) {
                $eddnum = $row["id"];
                $eddname = $row["name"];
                $eddtext = $row["text"];
                $password = $row["password"];
                $editMode = true;
            }
        }

        // 削除ボタンが押された場合の処理
        if (isset($_POST["delete"])) {
            $del = $_POST["del"];
            $del_password = $_POST["del_password"];
            // 削除モード用のデータベースクエリを実行
            $sql = "DELETE FROM posts WHERE id = ? AND password = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$del, $del_password]);
        }

    } catch (PDOException $e) {
        die("データベース接続エラー: " . $e->getMessage());
    }
    ?>

    <form action="" method="post">
        <input type="text" name="name" placeholder="名前" value="<?php echo $eddname; ?>">
        <input type="text" name="text" placeholder="コメント" value="<?php echo $eddtext; ?>">
        <input type="password" name="password" placeholder="パスワード" value="<?php echo $password; ?>">
        <?php if ($editMode): ?>
            <input type="hidden" name="edit_mode" value="<?php echo $hen; ?>">
        <?php endif; ?>
        <input type="submit" name="submit" value="<?php echo $editMode ? '編集' : '送信'; ?>">

        <input type="number" name="del" placeholder="削除番号指定用フォーム">
        <input type="password" name="del_password" placeholder="削除パスワード">
        <input type="submit" name="delete" value="削除">

        <input type="number" name="hen" placeholder="編集番号指定用フォーム">
        <input type="password" name="hen_password" placeholder="編集パスワード">
        <input type="submit" name="edit" value="編集"> <!-- ボタン名を変更 -->
    </form>

    <?php
    $date = date("Y/m/d H:i:s");

    if (isset($_POST["submit"]) && !empty($_POST["name"]) && !empty($_POST["text"]) && !empty($_POST["password"])) {
        $name = $_POST["name"];
        $text = $_POST["text"];
        $password = $_POST["password"];

        if (!empty($_POST["edit_mode"])) {
            $editLineNumber = $_POST["edit_mode"];
            // 編集モード用のデータベースクエリを実行して更新
            $sql = "UPDATE posts SET name = ?, text = ?, password = ? WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$name, $text, $password, $editLineNumber]);
        } else {
            // 新規投稿用のデータベースクエリを実行
            $sql = "INSERT INTO posts (name, text, date, password) VALUES (?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$name, $text, $date, $password]);
        }
    }

    // 投稿内容の取得と表示
    $sql = "SELECT * FROM posts ORDER BY id";
    $stmt = $pdo->query($sql);
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $post_num = $row["id"];
        $post_name = $row["name"];
        $post_text = $row["text"];
        $post_date = $row["date"];

        // 投稿内容の表示（指定されたフォーマット）
        echo $post_num . $post_name . $post_date . "<br>";
        echo $post_text . "<br>";
        echo "<hr>";
    }
    ?>

</body>
</html>

