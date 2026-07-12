<?php
ini_set('session.gc_maxlifetime', 86400);
ini_set('session.cookie_lifetime', 86400);
session_set_cookie_params(86400, '/', null, null, true);
session_start();
$login = 'vital2k9';
$password = 120188522;
if ($_POST) {
    if ($_POST['name'] == $login && $_POST['pass'] == $password) {
        $_SESSION['auth'] = 'pass';
        header("location: /todo/");
        die;
    } else {
        $_SESSION['error'] = 'Данные для входа неверные';
        header("location: login.php");
        die;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Авторизация</title>
    <style>
.password {
	position: relative;
}
.password-control {
	position: absolute;
	top: 11px;
	right: 6px;
	display: inline-block;
	width: 20px;
	height: 20px;
	background: url("https://snipp.ru/demo/495/view.svg") 0 0 no-repeat;
}
.password-control.view {
	background: url("https://snipp.ru/demo/495/no-view.svg") 0 0 no-repeat;
}

    </style>
    <script>
        function show_hide_password(target){
	var input = document.getElementById('password-input');
	if (input.getAttribute('type') == 'password') {
		target.classList.add('view');
		input.setAttribute('type', 'text');
	} else {
		target.classList.remove('view');
		input.setAttribute('type', 'password');
	}
	return false;
}
    </script>
</head>
<body>
<H3>Введите Логин и Пароль</H3>
<form method="post">
Логин: <input type="test" name="name">
<div class="password" id="password-input" placeholder="Введите пароль">Пароль: <input type="password" name="pass">
<a href="#" class="password-control" onclick="return show_hide_password(this);"></a></div>
<button type="submit">Отправить</button>
</form>
<?php 
if (isset($_SESSION['error'])){
    print_r($_SESSION['error']);
    unset($_SESSION['error']);
}


?>

</body>
</html>

