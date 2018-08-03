<?php

//insert.php

$connect = new PDO('mysql:host=localhost;dbname=novelnotarypublic', 'root', '');

if (isset($_POST["title"])) {
    $query = "
 INSERT INTO events 
 (title, user_id, start_event, end_event) 
 VALUES (:title, :user_id, :start_event, :end_event)
 ";
    $statement = $connect->prepare($query);
    $statement->execute(
            array(
                ':user_id' => $_POST['user_id'],
                ':title' => $_POST['title'],
                ':start_event' => $_POST['start'],
                ':end_event' => $_POST['end']
            )
    );
}
?>
