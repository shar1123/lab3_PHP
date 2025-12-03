<?php
require "vendor/autoload.php";
require "db.php";

$user = currentUser();

// выход
if (isset($_GET["logout"])) {
    logout();
    header("Location: index.php");
    exit;
}

// обработка регистрации
if (isset($_POST["register"])) {
    registerUser($_POST["login"], $_POST["password"], $_POST["bg"], $_POST["font"]);
    echo "<p>Регистрация завершена. Теперь войдите.</p>";
}

// обработка авторизации
if (isset($_POST["login_btn"])) {
    if (loginUser($_POST["login"], $_POST["password"])) {
        header("Location: index.php");
        exit;
    } else {
        echo "<p>Неверный логин или пароль.</p>";
    }
}

// если пользователь авторизован — показываем его страницу
if ($user):
?>
<body style="background: <?= $user["bg_color"] ?>; color: <?= $user["font_color"] ?>;">
    <h1>Здравствуйте, <?= $user["login"]; ?>!</h1>
    <p>Ваши настройки применены автоматически.</p>
    <a href="?logout=1">Выйти</a>
</body>
<?php
exit;
endif;
?>


<h2>Авторизация</h2>
<form method="post">
    <input name="login" placeholder="Логин">
    <input name="password" placeholder="Пароль" type="password">
    <button name="login_btn">Войти</button>
</form>

<hr>

<h2>Регистрация</h2>
<form method="post">
    <input name="login" placeholder="Логин">
    <input name="password" placeholder="Пароль" type="password">

    <label>Цвет фона</label>
    <input type="color" name="bg">

    <label>Цвет текста</label>
    <input type="color" name="font">

    <button name="register">Зарегистрироваться</button>
</form>

