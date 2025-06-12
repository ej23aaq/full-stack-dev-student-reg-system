<?php
$server = 'db';
$username = 'root';
$password = 'rootpassword';
//The name of the schema/database we created earlier in Adminer
//If this schema/database does not exist you will get an error!
$schema = 'StudentRecord';
$pdo = new PDO(
    'mysql:dbname=' . $schema . ';host=' . $server,
    $username,
    $password,
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['student-name'] ?? '';
    $course = $_POST['student-course'] ?? '';
    $subject1 = $_POST['student-subject-1'] ?? '';
    $subject2 = $_POST['student-subject-2'] ?? '';
    $subject3 = $_POST['student-subject-3'] ?? '';
    $subject4 = $_POST['student-subject-4'] ?? '';
    $year = (int) $_POST['student-year-of-study'] ?? 0;

    // Simple grade generator
    function randomGrade()
    {
        $grades = ['A', 'B', 'C', 'D', 'E', 'F'];
        return $grades[array_rand($grades)];
    }

    // Create subjects JSON
    $subjects = json_encode([
        $subject1 => randomGrade(),
        $subject2 => randomGrade(),
        $subject3 => randomGrade(),
        $subject4 => randomGrade(),
    ]);

    // Insert into the DB
    $stmt = $pdo->prepare("INSERT INTO Students (Name, Course, Subjects, `Year of Study`) VALUES (?, ?, ?, ?)");
    $stmt->execute([$name, $course, $subjects, $year]);

    // Optional: Redirect to prevent resubmission on refresh
    header("Location: index.php?registered=1");
    exit;
}


try {

    $student = null;
    $name = $_GET['query-student-name'] ?? '';

    if ($name) {
        // Fetch the student by name
        $stmt = $pdo->prepare("SELECT Name, Course, Subjects, `Year of Study` FROM Students WHERE Name = ?");
        $stmt->execute([$name]);
        $student = $stmt->fetch(PDO::FETCH_ASSOC);
    }
} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage();
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Grades</title>
    <link rel="stylesheet" href="./styles.css">
</head>

<body>
    <section id="login">
        <h1 class="login-header">Student Registration</h1>
        <form class="login-form" method="post" action="index.php">
            <input name="student-name" class="login-text-input" placeholder="Name..." required>
            <input name="student-course" class="login-text-input" placeholder="Course..." required>
            <input name="student-subject-1" class="login-text-input" placeholder="Subject 1..." required>
            <input name="student-subject-2" class="login-text-input" placeholder="Subject 2..." required>
            <input name="student-subject-3" class="login-text-input" placeholder="Subject 3..." required>
            <input name="student-subject-4" class="login-text-input" placeholder="Subject 4..." required>
            <input name="student-year-of-study" class="login-text-input" placeholder="Year Of Study..." required>
            <button class="login-button" type="submit">Register</button>
        </form>
    </section>
    <main id="grades">
        <section id="query-grades">
            <h1>Query Grades:</h1>
            <form id="get-grades" action="index.php" method="get">
                <input type="text" name="query-student-name" placeholder="Name...">
                <button class="query-button" type="submit">Get Grades</button>
            </form>
        </section>
        <section id="query-result">
            <h2>Query Output:</h2>  
            <?php if ($student): ?>
                <dl>
                    <dt>Name:</dt>
                    <dd><?= htmlspecialchars($student['Name']) ?></dd>
                    <dt>Course:</dt>
                    <dd><?= htmlspecialchars($student['Course']) ?></dd>
                    <dt>Year of Study:</dt>
                    <dd><?= htmlspecialchars($student['Year of Study']) ?></dd>
                    <dt>Subjects:</dt>
                    <dd>
                        <ul>
                            <?php
                            $subjects = json_decode($student['Subjects'], true);
                            if (is_array($subjects)) {
                                foreach ($subjects as $subject => $grade) {
                                    echo '<li>' . htmlspecialchars($subject) . ': ' . htmlspecialchars($grade) . '</li>';
                                }
                            } else {
                                echo '<li>Invalid subject data</li>';
                            }
                            ?>
                        </ul>
                    </dd>
                </dl>
            <?php elseif ($student === null): ?>
                <p>No student found with that name.</p>
            <?php endif; ?>

        </section>
    </main>
    <!-- <?php if (isset($_GET['registered'])): ?>
        <script>
            // Wait for the DOM to load
            window.addEventListener('DOMContentLoaded', () => {
                // Hide login
                const loginSection = document.getElementById('login');
                if (loginSection) loginSection.style.display = 'none';

                // Show query result
                const resultSection = document.getElementById('query-result');
                if (resultSection) resultSection.style.display = 'flex';
            });
        </script>
    <?php endif; ?> -->
</body>

</html>