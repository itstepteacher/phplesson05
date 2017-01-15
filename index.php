<form method="post">
    <label>
        Title: <br/>
        <input name="title" type="text" />
    </label>
    <br />
    <label for="content">Content:</label> <br/>
    <textarea id="content" name="content" rows="5" cols="20"></textarea>
    <br/>
    <button type="submit">Save</button>
</form>

<form method="get">
    <label>Filter title:<br/>
        <input type="search"
               value="<?php if(!empty($_GET)) echo $_GET['filter'] ?>"
               name="filter"/>
    </label>
    <button type="submit">Search</button>
</form>

<?php

try {
    //$dsn = "mysql:host=localhost;dbname=blog";
    $dsn = "sqlite:blog.sqlite";
    // Подключение к б/д
    // ';set password=password('hack');'
    $db = new PDO($dsn, "blog", "blog");
    $db->beginTransaction(); // начало транзакции
    echo "<p style='color:green;'>connected!</p>";

    // Настройки PDO
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

//    $sql = "CREATE TABLE post  (
//                id INTEGER PRIMARY KEY AUTOINCREMENT,
//                title varchar(60) not null,
//                content text not null,
//                published_date text
//            )";
//    $db->exec($sql);
    // Запрос без ответа (CREATE, DROP, DELET, ALTER, INSERT ...)
    //$db->exec('');

    if(!empty($_POST))
    {

        if(isset($_POST['title']) && isset($_POST['content']))
        {
//            $title = $_POST['title'];
//            $content = $_POST['content'];

            extract($_POST);
            $title = htmlentities($title, ENT_QUOTES);
            //echo "title: $title, content: $content<br/>";
            $pubdate = date('Y-m-d H:i:s');
            $sql = "INSERT INTO post(title, content, published_date) values ('$title', '$content', '$pubdate')";
            echo "insert query: $sql<br/>";
            $count = $db->exec($sql);
            //throw new PDOException("Unknown exception");
        }
    }
    $db->commit();

    // Запросы с ответом (SELECT, SHOW DATABASES, SHOW TABLES)
    if(empty($_GET)) {
        //$sql = "SELECT * FROM post ORDER BY published_date DESC";
        $st = $db->prepare("SELECT * FROM post ORDER BY published_date DESC");
        $st->execute();
    } else { // filter
        extract($_GET); //$filter = $_GET['filter'];
        //$sql = "SELECT * FROM post WHERE title like '%$filter%'";
        // Подготовленные запросы
        $st = $db->prepare("SELECT * FROM post WHERE title like :filter");
        $st->execute(['filter'=>"%$filter%"]);
    }
    echo "<pre>";
    echo "sql:" . $st->queryString;
    echo "</pre>";

    //foreach ($db->query($sql) as $row) {
    foreach ($st->fetchAll() as $row) {
//        echo "<pre>";
//        print_r($row);
//        echo "</pre>";
        echo "<article>";
        echo "<header>";
        echo "<h3>{$row['title']}</h3>";
        echo "</header>";
        echo "<div>{$row['content']}</div>";
        echo "<footer>";
        echo "<span style='font-size: 12px'>Published at: {$row['published_date']}</span>";
        echo "</footer>";
        echo "</article>";
    }

// Транзакции

} catch (PDOException $ex) {
    $db->rollBack();
    // Обработка ошибок
    echo "<p style='color:red'>";
    echo $ex->getMessage();
    echo "</p>";
}