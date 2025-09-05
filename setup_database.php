<?php
// Database Setup Script
require_once 'includes/config.php';

echo "<h2>Setting up Database...</h2>";

try {
    // Connect to MySQL without selecting database
    $connection = new mysqli(DB_HOST, DB_USERNAME, DB_PASSWORD);
    
    if ($connection->connect_error) {
        throw new Exception("Connection failed: " . $connection->connect_error);
    }
    
    echo "<p>✓ Connected to MySQL server</p>";
    
    // Read and execute SQL file
    $sql_file = 'sql/database_schema.sql';
    if (!file_exists($sql_file)) {
        throw new Exception("SQL file not found: " . $sql_file);
    }
    
    $sql_content = file_get_contents($sql_file);
    
    // Split SQL content by semicolons and execute each statement
    $statements = explode(';', $sql_content);
    
    foreach ($statements as $statement) {
        $statement = trim($statement);
        if (!empty($statement)) {
            if (!$connection->query($statement)) {
                echo "<p style='color: orange;'>Warning: " . $connection->error . "</p>";
            }
        }
    }
    
    echo "<p>✓ Database schema created successfully</p>";
    echo "<p>✓ Sample data inserted</p>";
    
    // Test connection to the new database
    $test_connection = new mysqli(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_NAME);
    if ($test_connection->connect_error) {
        throw new Exception("Cannot connect to new database: " . $test_connection->connect_error);
    }
    
    echo "<p>✓ Database connection test successful</p>";
    
    echo "<h3 style='color: green;'>Database setup completed successfully!</h3>";
    echo "<p><a href='index.php'>Go to Application</a></p>";
    
    $test_connection->close();
    $connection->close();
    
} catch (Exception $e) {
    echo "<h3 style='color: red;'>Error: " . $e->getMessage() . "</h3>";
}
?>

