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

// Now you can execute your table creation queries or other SQL commands
$queries = array(
    // Table: class_competency
    "CREATE TABLE IF NOT EXISTS class_competency (
      class_name VARCHAR(10) NOT NULL,
      competency_name VARCHAR(10) NOT NULL,
      PRIMARY KEY (class_name, competency_name)
    )",
    
    // Table: competency_questions
    "CREATE TABLE IF NOT EXISTS competency_questions (
      question_id VARCHAR(12) NOT NULL,
      user_id INTEGER NOT NULL,
      class_name VARCHAR(10) NOT NULL,
      competency_name VARCHAR(10) NOT NULL,
      competency_subject TEXT,
      question_text TEXT NOT NULL,
      question_notes TEXT,
      date_added TIMESTAMP NOT NULL,
      PRIMARY KEY (question_id)
    )",
    
    // Table: logged_questions
    "CREATE TABLE IF NOT EXISTS logged_questions (
      logged_question_id INTEGER NOT NULL AUTO_INCREMENT,
      user_id INTEGER NOT NULL,
      class_name VARCHAR(10) NOT NULL,
      competency_name VARCHAR(10) NOT NULL,
      question_text TEXT NOT NULL,
      competency_subject TEXT,
      question_notes TEXT,
      date_added TIMESTAMP NOT NULL,
      PRIMARY KEY (logged_question_id)
    )",
    
    // Table: users
    "CREATE TABLE IF NOT EXISTS users (
      user_id INTEGER NOT NULL AUTO_INCREMENT,
      username VARCHAR(45) NOT NULL UNIQUE,
      password VARCHAR(255) NOT NULL,
      role ENUM('instructor','ta') NOT NULL,
      PRIMARY KEY (user_id)
    )",
    
    // Table: students
    "CREATE TABLE IF NOT EXISTS students (
      student_id INTEGER NOT NULL AUTO_INCREMENT,
      student_name VARCHAR(45) NOT NULL,
      PRIMARY KEY (student_id)
    )",
    
    // Table: student_competency_performance
    "CREATE TABLE IF NOT EXISTS student_competency_performance (
      performance_id INTEGER NOT NULL AUTO_INCREMENT,
      student_id INTEGER NOT NULL,
      class_name VARCHAR(10) NOT NULL,
      competency_name VARCHAR(10) NOT NULL,
      performance_score VARCHAR(45),
      instructor_notes TEXT,
      last_attempt_date TIMESTAMP NOT NULL,
      attempt_count INTEGER NOT NULL,
      PRIMARY KEY (performance_id)
    )",
    
    // Table: student_competency_questions_asked
    "CREATE TABLE IF NOT EXISTS student_competency_questions_asked (
      record_id INTEGER NOT NULL AUTO_INCREMENT,
      performance_id INTEGER NOT NULL,
      question_id VARCHAR(12) NOT NULL,
      PRIMARY KEY (record_id)
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