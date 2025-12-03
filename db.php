<?php

session_start();

$config = require __DIR__ . "/config.php";

$pdo = new PDO("sqlite:" . $config['db_path']);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// создаём таблицу, если она отсутствует
$pdo->exec("
CREATE TABLE IF NOT EXISTS users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    login TEXT UNIQUE,
    password TEXT,
    bg_color TEXT,
    font_color TEXT
)
");


// функция регистрации
function registerUser($login, $password, $bg, $font) {
    global $pdo;

    $sql = "INSERT INTO users (login, password, bg_color, font_color)
            VALUES (:l, :p, :b, :f)";
    $stmt = $pdo->prepare($sql);
    return $stmt->execute([
        ":l" => $login,
        ":p" => password_hash($password, PASSWORD_DEFAULT),
        ":b" => $bg,
        ":f" => $font
    ]);
}

// функция логина
function loginUser($login, $password) {
    global $pdo;

    $stmt = $pdo->prepare("SELECT * FROM users WHERE login = :l");
    $stmt->execute([":l" => $login]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) return false;
    if (!password_verify($password, $user["password"])) return false;

    // сохраняем в сессию
    $_SESSION["user_id"] = $user["id"];

    // создаём cookie
    setcookie("bg_color", $user["bg_color"], time()+3600);
    setcookie("font_color", $user["font_color"], time()+3600);

    return true;
}

// получение текущего пользователя
function currentUser() {
    global $pdo;

    // если в сессии есть user_id — ок
    if (!empty($_SESSION["user_id"])) {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = :id");
        $stmt->execute([":id" => $_SESSION["user_id"]]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // если нет сессии, но есть cookies — считаем, что он авторизован
    if (!empty($_COOKIE["bg_color"]) && !empty($_COOKIE["font_color"])) {
        return [
            "bg_color" => $_COOKIE["bg_color"],
            "font_color" => $_COOKIE["font_color"],
            "login" => "Автовход"
        ];
    }

    return null;
}

// logout
function logout() {
    session_destroy();
    setcookie("bg_color", "", time()-3600);
    setcookie("font_color", "", time()-3600);
}
