<?php

//старый вариант, без ajax
require_once "./db.php";

ini_set('session.gc_maxlifetime', 86400);
ini_set('session.cookie_lifetime', 86400);
session_set_cookie_params(86400, '/', null, null, true);
session_start();

if (isset($_GET['do']) && $_GET['do'] == 'logout') {
    unset($_SESSION['auth']);
    header("location: login.php");
    die;
}

try {

    $pdo = getPdo();
    $query = "SELECT * FROM todolist ORDER BY rank DESC";
    $result = $pdo->query($query);

    while ($row = $result->fetch()) {
        $messages[] = $row;
    }
    $messageRow = strip_tags(trim($_POST['message'])) ?? '';
    $rank = $_POST['rank'] ?? '';

    if (isset($_POST['send'])) {
        if (strlen($messageRow) > 20) {
            // global $db;
            global $pdo;
            $query = "INSERT INTO todolist (`message`, `rank`) VALUES (?,?)";
            $sql = $pdo->prepare($query);
            if ($sql->execute([$messageRow, $rank])) {
                $_SESSION['result'] = 'Данные добавлены';
                header("location: /todo/");
                die;
            };
        } else {
            $_SESSION['result'] = 'Введите задачу (текст более 20 символов)';
            header("location: /todo/");
            die;
        }
    }
    if (isset($_GET['del'])) {
        global $pdo;
        $idbase = $_GET['del'] ?? '';
        $query = "DELETE FROM todolist WHERE id=?";
        $sql = $pdo->prepare($query);
        $sql->execute([$idbase]);
        header("location: /todo/");
        die;
    }
    if (isset($_GET['edit'])) {
        $index = $_GET['edit'] ?? '';
        $message = $messages[$index]['message'];
    }
    if (isset($_POST['edit'])) {
        global $db;
        global $pdo;
        $index = $_GET['edit'] ?? '';
        $idbase = $messages[$index]['id'] ?? '';
        $query = "UPDATE todolist SET `message`= ?, `rank`= ? WHERE id = ?";
        $sql = $pdo->prepare($query);
        $sql->execute([$message, $rank, $idbase]);

        header("location:/todo/");
        die;
    }
} catch (PDOException $e) {
    echo "Ошибка " . $e->getMessage(), '<br>';
    echo "В файле " . $e->getFile(), '<br>';
    echo "В строке " . $e->getLine();
}
?>
<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
    <title>Список дел</title>
</head>

<body>
    <div class="container" style="text-align: center;">
        <?php
        if (isset($_SESSION['result'])) {
            print_r($_SESSION['result']);
            unset($_SESSION['result']);
        }
        ?>

        <form method="post">
            <label for="message">Задача:</label><br />
            <textarea class="form-control" name="message" id="message"><?= $message; ?></textarea><br />
            Приоритет: <select class="form-select" style="text-align:center;" name="rank">
                <?php
                for ($i = 1; $i < 11; $i++) {
                    echo "<option value='$i'>$i</option>";
                }
                ?>
            </select><br />
            <button class="btn btn-success mb-4" type="submit" name="send">Отправить</button>
            <?php if (isset($_GET['edit'])): ?>
                <button class="btn btn-success mb-4" type="submit" name="edit">Обновить</button>
            <?php endif; ?>
        </form>
        <a href="?do=logout">Выйти</a>
        <table class="table" align="center" border="1">
            <?php

            foreach ($messages as $key => $value) {
                $key2 = $key + 1;
                echo "<tr>";
                echo "<td>" . $key2 . "</td>";
                $mes = htmlspecialchars(nl2br($value['message']));
                if ($value['rank'] > 7) {
                    echo "<td class=\"bg-danger bg-gradient\">{$mes} </td>";
                } elseif ($value['rank'] > 3) {
                    echo "<td class=\"bg-warning bg-gradient\">{$mes} </td>";
                } else {
                    echo "<td class=\"bg-success bg-gradient\">{$mes} </td>";
                }
                echo "<td>{$value['rank']}</td>";
                $date = date_create($value['created_at']);
                echo "<td>" . date_format($date, 'd/m/Y') . "</td>";
                echo "<td><a href=\"?edit={$key}\">Ред.</a> <a href=\"?del={$value['id']}\">Удалить</a> </p></td>";
            }
            ?>
        </table>
    </div>
</body>

</html>