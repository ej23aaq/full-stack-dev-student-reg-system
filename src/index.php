<?php
$server = 'db';
$username = 'root';
$password = 'rootpassword';
$schema = 'StudentRecord';

// Temp Code to get DB running for testing
$pdo = new PDO(
    'mysql:host=' . $server,
    $username,
    $password,
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);

$pdo->query("CREATE DATABASE IF NOT EXISTS `$schema`");

$pdo = new PDO(
    'mysql:dbname=' . $schema . ';host=' . $server,
    $username,
    $password,
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);

$pdo->query(
    "CREATE TABLE IF NOT EXISTS Students (
        ID INT AUTO_INCREMENT PRIMARY KEY,
        Name VARCHAR(255) NOT NULL,
        Course VARCHAR(255) NOT NULL,
        Subjects JSON NOT NULL,
        `Year of Study` INT NOT NULL
    )"
);


$pdo->query(
    "CREATE DATABASE IF NOT EXISTS $schema"
);

$pdo->query(
    "CREATE TABLE IF NOT EXISTS Students (
        ID INT AUTO_INCREMENT PRIMARY KEY,
        Name VARCHAR(255) NOT NULL,
        Course VARCHAR(255) NOT NULL,
        Subjects JSON NOT NULL,
        `Year of Study` INT NOT NULL
    )"
);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['student-name'] ?? '';
    $course = $_POST['student-course'] ?? '';
    $subject1 = $_POST['student-subject-1'] ?? '';
    $subject2 = $_POST['student-subject-2'] ?? '';
    $subject3 = $_POST['student-subject-3'] ?? '';
    $subject4 = $_POST['student-subject-4'] ?? '';
    $year = (int) $_POST['student-year-of-study'] ?? 0;

    function randomGrade()
    {
        $grades = ['A', 'B', 'C', 'D', 'E', 'F'];
        return $grades[array_rand($grades)];
    }

    $subjects = json_encode([
        $subject1 => randomGrade(),
        $subject2 => randomGrade(),
        $subject3 => randomGrade(),
        $subject4 => randomGrade(),
    ]);

    $stmt = $pdo->prepare("INSERT INTO Students (Name, Course, Subjects, `Year of Study`) VALUES (?, ?, ?, ?)");
    $stmt->execute([$name, $course, $subjects, $year]);

    header("Location: index.php?registered=1");
    exit;
}


try {

    $student = null;
    $name = $_GET['query-student-name'] ?? '';

    if ($name) {
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
    <link rel="stylesheet" href="./assets/styles.css">
    <link href="https://fonts.cdnfonts.com/css/tahoma" rel="stylesheet">
</head>

<body>
    <section class="container">
        <div class="container-header">Student Registration</div>
        <div class="container-body">
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
        </div>

    </section>
    <main id="grades">
        <section class="container">
            <div class="container-header">Query Grades</div>
            <div class="container-body">
                <form id="get-grades" action="index.php" method="get">
                    <input type="text" name="query-student-name" placeholder="Name...">
                    <button class="query-button" type="submit">Get Grades</button>
                </form>
            </div>
        </section>
        <section class="container">
            <div class="container-header">Query Output</div>
            <div class="container-body">
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
            </div>

        </section>
    </main>
</body>

</html>