<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TODO лист</title>
    <script src="./js/main.js" type="module" defer></script>
    <script src="./js/animate.js" type="module" defer></script>
    <link rel="stylesheet" href="./css/style.css">
</head>

<body>
    <div id="header">
        <h2>Добавить задачу</h2>
        <form id="add-task">
            <textarea name="message" rows="5"></textarea>
            <select name="rank">
                <?php
                for ($i = 1; $i < 11; $i++) {
                    echo "<option name={$i}>{$i}</option>";
                }
                ?>
            </select>
            <button>Добавить</button>
        </form>
    </div>
    <div id="loader" class="loading"></div>
    <div id="container">
        <table>
            <thead>
                <th>№</th>
                <th>Задача</th>
                <th>Ранг</th>
                <th></th>
            </thead>
            <tbody></tbody>
        </table>
    </div>
</body>

</html>