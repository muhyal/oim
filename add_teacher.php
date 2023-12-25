<?php
global $db, $showErrors, $siteName, $siteShortName, $siteUrl;
session_start();
// Oturum kontrolü
if (!isset($_SESSION["admin_id"])) {
    header("Location: admin_login.php"); // Giriş sayfasına yönlendir
    exit();
}
require_once "db_connection.php";
require_once "config.php";
require_once "admin_panel_header.php";
// Hata mesajlarını göster veya gizle ve ilgili işlemleri gerçekleştir
$showErrors ? ini_set('display_errors', 1) : ini_set('display_errors', 0);
$showErrors ? ini_set('display_startup_errors', 1) : ini_set('display_startup_errors', 0);

// Dersleri veritabanından çekme
$query = "SELECT * FROM courses";
$stmt = $db->query($query);
$courses = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Sınıfları veritabanından çekme
$query = "SELECT * FROM classes";
$stmt = $db->query($query);
$classes = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $firstName = $_POST["first_name"];
    $lastName = $_POST["last_name"];
    $tcIdentity = $_POST["tc_identity"];
    $birthDate = $_POST["birth_date"];
    $phone = $_POST["phone"];
    $email = $_POST["email"];
    $selectedCourse = $_POST["course"];
    $selectedClass = $_POST["class"];

    // Veritabanına yeni öğretmen ekleyin
    $query = "INSERT INTO teachers (first_name, last_name, tc_identity, birth_date, phone, email, course_id, class_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $db->prepare($query);
    $stmt->execute([$firstName, $lastName, $tcIdentity, $birthDate, $phone, $email, $selectedCourse, $selectedClass]);

}
?>
<div class="container-fluid">
    <div class="row">
        <?php
        require_once "admin_panel_sidebar.php";
        ?>
        <main role="main" class="col-md-9 ml-sm-auto col-lg-10 pt-3 px-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3">
                <h2>Öğretmen Ekle</h2>
            </div>
<form method="post">
    <label for="first_name">Adı:</label>
    <input type="text" id="first_name" name="first_name" required><br>
    <label for="last_name">Soyadı:</label>
    <input type="text" id="last_name" name="last_name" required><br>

    <label for="tc_identity">TC Kimlik No:</label>
    <input type="text" name="tc_identity" required><br>

    <label for="birth_date">Doğum Tarihi:</label>
    <input type="date" id="birth_date" name="birth_date" required><br>
    <label for="phone">Telefon:</label>
    <input type="tel" id="phone" name="phone"><br>
    <label for="email">E-posta:</label>
    <input type="email" id="email" name="email" required><br>

    <!-- Ders Seçimi -->
    <label for="course">Ders:</label>
    <select id="course" name="course">
        <?php foreach ($courses as $course): ?>
            <option value="<?= $course['id'] ?>"><?= $course['course_name'] ?></option>
        <?php endforeach; ?>
    </select><br>

    <!-- Sınıf Seçimi -->
    <label for="class">Sınıf:</label>
    <select id="class" name="class">
        <?php foreach ($classes as $class): ?>
            <option value="<?= $class['id'] ?>"><?= $class['class_name'] ?></option>
        <?php endforeach; ?>
    </select><br>

    <button type="submit" name="add_teacher">Öğretmen Ekle</button>
</form>

    <button onclick="location.href='teachers_list.php'" type="button">Öğretmen Listesi</button>

<?php
require_once "footer.php";
?>