<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Set up email variables
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $email = $_POST['email'];
    $question = $_POST['question'];

    // Send email to the author (example email, change it)
    $to = "author@example.com";
    $subject = "New Question Submitted";
    $message = "You have received a new question from: " . $email . "\n\nQuestion: " . $question;
    $headers = "From: webmaster@example.com";

    // Send email
    if (mail($to, $subject, $message, $headers)) {
        echo "Email sent successfully.<br>";
    } else {
        echo "Failed to send email.<br>";
    }

    // Database connection
    $conn = new mysqli("localhost", "root", "", "questions_db");

    // Check for database connection errors
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Prepare and bind the insert query
    $stmt = $conn->prepare("INSERT INTO questions (email, question, timestamp) VALUES (?, ?, NOW())");
    
    // Check if statement preparation was successful
    if ($stmt === false) {
        die('Error preparing statement: ' . $conn->error);
    }

    // Bind parameters (email and question as strings)
    $stmt->bind_param("ss", $email, $question);

    // Execute the statement
    if ($stmt->execute()) {
        // Redirect after successful post to prevent re-posting on refresh
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    } else {
        echo "Error posting question: " . $stmt->error . "<br>";
    }

    // Close the statement and connection
    $stmt->close();
    $conn->close();
}

// Database connection to fetch questions
$conn = new mysqli("localhost", "root", "", "questions_db");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch all questions
$sql = "SELECT email, question, timestamp FROM questions ORDER BY timestamp DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ask a Question</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f9;
            margin: 0;
            padding: 0;
        }

        .container {
            width: 50%;
            margin: 50px auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        h1 {
            text-align: center;
            color: #333;
        }

        label {
            font-size: 16px;
            color: #555;
        }

        input, textarea {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border-radius: 4px;
            border: 1px solid #ccc;
        }

        button {
            background-color: #4CAF50;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }

        button:hover {
            background-color: #45a049;
        }

        .question-list {
            margin-top: 40px;
        }

        .question-item {
            background-color: #f9f9f9;
            padding: 15px;
            margin-bottom: 10px;
            border-radius: 5px;
            border: 1px solid #ddd;
            transition: transform 0.3s ease, filter 0.3s ease;
        }

        .question-item h4 {
            margin: 0;
            font-size: 18px;
        }

        .question-item p {
            font-size: 16px;
            color: #555;
        }

        .question-item .meta {
            font-size: 14px;
            color: #999;
            text-align: right;
        }

        .question-item:hover {
            transform: scale(1.05);
            background-color: #e0ffe0;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
        }

        .question-item:not(:hover) {
            filter: blur(2px);
        }

        .question-item-container {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .question-item-container:hover .question-item:not(:hover) {
            filter: blur(2px);
        }

    </style>
</head>
<body>

    <div class="container">
        <h1>Ask a Question</h1>
        <form method="POST" action="index.php">
            <label for="email">Your Email:</label>
            <input type="email" id="email" name="email" required placeholder="Enter your email">
            
            <label for="question">Your Question:</label>
            <textarea id="question" name="question" rows="5" required placeholder="Ask your question here..."></textarea>
            
            <button type="submit">Post Question</button>
        </form>

        <div class="question-list">
            <h2>Previously Asked Questions</h2>
            <div class="question-item-container">
                <?php
                if ($result->num_rows > 0) {
                    // Output data of each row
                    while ($row = $result->fetch_assoc()) {
                        echo '<div class="question-item">';
                        echo '<h4>' . htmlspecialchars($row['question']) . '</h4>';
                        echo '<p>' . htmlspecialchars($row['email']) . '</p>';
                        echo '<p class="meta">' . date('F j, Y, g:i a', strtotime($row['timestamp'])) . '</p>';
                        echo '</div>';
                    }
                } else {
                    echo "No questions available.";
                }
                ?>
            </div>
        </div>
    </div>

</body>
</html>
