<?php
// Database connection settings
$host = 'localhost';
$user = 'user';
$password = 'pass';
$dbname = 'databaseUser';

// Create a new connection using mysqli
$conn = new mysqli($host, $user, $password, $dbname);

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// List of SQL queries to create each table for the database
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

// Execute each query and display a message
foreach ($queries as $query) {
    if ($conn->query($query) === TRUE) {
        echo "Query executed successfully: " . $query . "\n";
    } else {
        echo "Error executing query: " . $query . "\nError: " . $conn->error . "\n";
    }
}

// Close the database connection
$conn->close();
?>
