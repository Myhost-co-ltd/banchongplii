<?php
$pdo=new PDO('mysql:host=127.0.0.1;dbname=banchongpli','root','');
$tables=['course_teaching_hours','course_lessons','course_assignments'];
foreach ($tables as $t) {
    echo "== $t ==\n";
    $stmt=$pdo->query("SELECT * FROM $t LIMIT 3");
    print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
    echo "\n";
}
