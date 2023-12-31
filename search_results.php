<?php
/**
 * @copyright Copyright (c) 2024, KUTBU
 *
 * @author Muhammed Yalçınkaya <muhammed.yalcinkaya@kutbu.com>
 *
 * @license AGPL-3.0
 *
 * This code is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License, version 3,
 * as published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License, version 3,
 * along with this program. If not, see <http://www.gnu.org/licenses/>
 *
 */
// Veritabanı bağlantısı ve sorgu işlemleri gibi kodlar burada yer alır
global $db, $showErrors, $siteUrl, $siteName, $siteShortName;
// Hata mesajlarını göster veya gizle ve ilgili işlemleri gerçekleştir
$showErrors ? ini_set('display_errors', 1) : ini_set('display_errors', 0);
$showErrors ? ini_set('display_startup_errors', 1) : ini_set('display_startup_errors', 0);

// Oturum kontrolü
session_start();
session_regenerate_id(true);

if (!isset($_SESSION["admin_id"])) {
    header("Location: admin_login.php"); // Giriş sayfasına yönlendir
    exit();
}

require_once "db_connection.php";

// formdan gelen search_type değerini alalım
$searchType = $_GET['search_type'] ?? '';

// Arama sorgusunu hazırlayın ve sonuçları alın
if ($searchType === "user") {
    // Kullanıcı arama sorgusu
    $query = "SELECT * FROM users WHERE firstname LIKE :searchQuery";
} elseif ($searchType === "student") {
    // Öğrenci arama sorgusu
    $query = "SELECT * FROM students WHERE firstname LIKE :searchQuery OR lastname LIKE :searchQuery";
} elseif ($searchType === "class") {
    // Sınıf arama sorgusu
    $query = "SELECT * FROM classes WHERE class_name LIKE :searchQuery";
    } elseif ($searchType === "teacher") {
    // Öğretmen arama sorgusu
    $query = "SELECT * FROM teachers WHERE first_name LIKE :searchQuery OR last_name LIKE :searchQuery";
} elseif ($searchType === "course") {
    // Ders arama sorgusu
    $query = "SELECT * FROM courses WHERE course_name LIKE :searchQuery";
}
$searchQuery = $_GET['q'] ?? ''; // searchQuery değişkenini tanımlayın

$stmt = $db->prepare($query);
$stmt->bindValue(":searchQuery", "%$searchQuery%");
$stmt->execute();
$searchResults = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<h2>Arama Sonuçları</h2>

<?php if (empty($searchResults)) { ?>
    <p>Hiçbir sonuç bulunamadı.</p>
<?php } else { ?>
    <ul>
        <?php foreach ($searchResults as $result) { ?>
            <li>
                <?php if ($searchType === "user") { ?>
                    Kullanıcı Adı: <?php echo $result['firstname']; ?>
                <?php } elseif ($searchType === "student") { ?>
                    Öğrenci: <?php echo $result['firstname'] . ' ' . $result['lastname']; ?>
                <?php } elseif ($searchType === "class") { ?>
                    Sınıf Adı: <?php echo $result['class_name']; ?>
                <?php } elseif ($searchType === "teacher") { ?>
                    Öğretmen: <?php echo $result['first_name'] . ' ' . $result['last_name']; ?>
                <?php } elseif ($searchType === "course") { ?>
                    Ders: <?php echo $result['course_name']; ?>
                <?php } ?>

                <!-- Düzenleme ve Silme Bağlantıları -->
                <?php if ($searchType === "user") { ?>
                    <a href="edit_user.php?id=<?php echo $result['id']; ?>">Düzenle</a>
                    <a href="delete_user.php?id=<?php echo $result['id']; ?>" onclick="return confirm('Bu kullanıcıyı silmek istediğinizden emin misiniz?')">Sil</a>
                <?php } elseif ($searchType === "student") { ?>
                    <a href="edit_student.php?id=<?php echo $result['id']; ?>">Düzenle</a>
                    <a href="delete_student.php?id=<?php echo $result['id']; ?>" onclick="return confirm('Bu öğrenciyi silmek istediğinizden emin misiniz?')">Sil</a>
                <?php } elseif ($searchType === "class") { ?>
                    <a href="edit_class.php?id=<?php echo $result['id']; ?>">Düzenle</a>
                    <a href="delete_class.php?id=<?php echo $result['id']; ?>" onclick="return confirm('Bu sınıfı silmek istediğinizden emin misiniz?')">Sil</a>
                <?php } elseif ($searchType === "teacher") { ?>
                    <a href="edit_teacher.php?id=<?php echo $result['id']; ?>">Düzenle</a>
                    <a href="delete_teacher.php?id=<?php echo $result['id']; ?>" onclick="return confirm('Bu öğretmeni silmek istediğinizden emin misiniz?')">Sil</a>
                <?php } elseif ($searchType === "course") { ?>
                    <a href="edit_course?id=<?php echo $result['id']; ?>">Düzenle</a>
                    <a href="delete_course.php?id=<?php echo $result['id']; ?>" onclick="return confirm('Bu dersi silmek istediğinizden emin misiniz?')">Sil</a>
                <?php } ?>
            </li>
        <?php } ?>
    </ul>
<?php } ?>
