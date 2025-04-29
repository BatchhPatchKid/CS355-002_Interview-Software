<?php
require_once 'vendor/autoload.php';

$host = 'localhost';
$user = 'root';
$password = '';
$dbname = 'databaseCS355';

$conn = new mysqli($host, $user, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$faker = Faker\Factory::create();

// Create some users
$user_ids = [];
for ($i = 0; $i < 5; $i++) {
    $username = $faker->unique()->userName;
    $passwordHash = password_hash('password', PASSWORD_DEFAULT);
    $role = $faker->randomElement(['instructor', 'ta']);
    $conn->query("INSERT INTO users (username, password, role) VALUES ('$username', '$passwordHash', '$role')");
    $user_ids[] = $conn->insert_id;
}

// Create some class competencies
$class_competencies = [];
for ($i = 0; $i < 5; $i++) {
    $class = $faker->bothify('CLS###');
    $competency = $faker->bothify('CMP###');
    $subject = $faker->bothify('SUB###');
    $conn->query("INSERT INTO class_competency (class_name, competency_name, class_subject) 
                  VALUES ('$class', '$competency', '$subject')");
    $class_competencies[] = [$class, $competency, $subject];
}

// Create a bunch of competency questions
$used_ids = [];
for ($i = 0; $i < 100; $i++) {
    do {
        $qid = $faker->unique()->bothify('QST#######');
    } while (in_array($qid, $used_ids));
    $used_ids[] = $qid;

    $user_id = $faker->randomElement($user_ids);
    [$class, $comp, $subj] = $faker->randomElement($class_competencies);
    $text = $faker->sentence(10);
    $notes = $faker->sentence(5);
    $conn->query("INSERT INTO competency_questions (question_id, user_id, class_name, competency_name, class_subject, question_text, question_notes) 
                  VALUES ('$qid', $user_id, '$class', '$comp', '$subj', '$text', '$notes')");
}

echo "Fake data with unique IDs generated successfully";
$conn->close();
?>
