<?php
// Database connection settings
$host = 'localhost';
$user = 'root';
$password = ''; // no password for now
$dbname = 'databaseCS355';

// Connect to MySQL without specifying a database
$conn = new mysqli($host, $user, $password);
if ($conn->connect_error) {
    die("Initial connection failed: " . $conn->connect_error);
}

// Create the database if it doesn't exist
$sql = "CREATE DATABASE IF NOT EXISTS `$dbname`";
if ($conn->query($sql) === TRUE) {
    echo "Database '$dbname' created or already exists.<br>";
} else {
    die("Error creating database: " . $conn->error);
}

// Close the initial connection
$conn->close();

$conn = new mysqli($host, $user, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$queries = array(
  // Table: users
  "CREATE TABLE IF NOT EXISTS users (
    user_id INT NOT NULL AUTO_INCREMENT,
    username VARCHAR(45) NOT NULL,
    password VARCHAR(255),
    role ENUM('instructor', 'ta'),
    PRIMARY KEY (user_id),
    UNIQUE KEY (username)
)",
  
  // Table: students
  "CREATE TABLE IF NOT EXISTS students (
    student_id INT NOT NULL AUTO_INCREMENT,
    student_name VARCHAR(45) NOT NULL,
    PRIMARY KEY (student_id)
)",
  
  // Table: class_competency
  "CREATE TABLE IF NOT EXISTS class_competency (
    class_name VARCHAR(10) NOT NULL,
    competency_name VARCHAR(10) NOT NULL,
    class_subject VARCHAR(10) NOT NULL,
    PRIMARY KEY (class_name, competency_name, class_subject)
)",
  
  // Table: competency_questions
  "CREATE TABLE IF NOT EXISTS competency_questions (
    question_id VARCHAR(12) NOT NULL,
    user_id INT,
    class_name VARCHAR(10),
    competency_name VARCHAR(10),
    class_subject VARCHAR(10),
    question_text TEXT,
    question_notes TEXT,
    parent_id VARCHAR(12),
    date_added TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (question_id),
    FOREIGN KEY (user_id) REFERENCES users(user_id),
    FOREIGN KEY (class_name, competency_name, class_subject) REFERENCES class_competency(class_name, competency_name, class_subject)
)",
  
  // Table: logged_questions
  "CREATE TABLE IF NOT EXISTS logged_questions (
    logged_question_id INT NOT NULL AUTO_INCREMENT,
    user_id INT,
    class_name VARCHAR(10),
    competency_name VARCHAR(10),
    class_subject VARCHAR(10),
    question_text TEXT,
    question_notes TEXT,
    parent_id VARCHAR(12),
    date_added TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (logged_question_id),
    FOREIGN KEY (user_id) REFERENCES users(user_id),
    FOREIGN KEY (class_name, competency_name, class_subject) REFERENCES class_competency(class_name, competency_name, class_subject)
)",
  
  // Table: student_competency_performance
  "CREATE TABLE IF NOT EXISTS student_competency_performance (
    performance_id INT NOT NULL AUTO_INCREMENT,
    student_id INT,
    class_name VARCHAR(10),
    competency_name VARCHAR(10),
    class_subject VARCHAR(10),
    performance_score VARCHAR(45),
    instructor_notes TEXT,
    last_attempt_date TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    attempt_count INT,
    PRIMARY KEY (performance_id),
    FOREIGN KEY (student_id) REFERENCES students(student_id),
    FOREIGN KEY (class_name, competency_name, class_subject) REFERENCES class_competency(class_name, competency_name, class_subject)
)",
  
  // Table: student_competency_questions_asked
  "CREATE TABLE IF NOT EXISTS student_competency_questions_asked (
    record_id INT NOT NULL AUTO_INCREMENT,
    performance_id INT,
    question_id VARCHAR(12),
    PRIMARY KEY (record_id),
    FOREIGN KEY (performance_id) REFERENCES student_competency_performance(performance_id),
    FOREIGN KEY (question_id) REFERENCES competency_questions(question_id)
)"
);

foreach ($queries as $query) {
    if ($conn->query($query) === TRUE) {
        echo "Query executed successfully: " . $query . "<br>";
    } else {
        echo "Error executing query: " . $query . "<br>Error: " . $conn->error . "<br>";
    }
}

$conn->close();
?>