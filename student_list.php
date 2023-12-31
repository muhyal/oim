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
global $db, $showErrors, $siteName, $siteShortName, $siteUrl, $config;
// Hata mesajlarını göster veya gizle ve ilgili işlemleri gerçekleştir
$showErrors ? ini_set('display_errors', 1) : ini_set('display_errors', 0);
$showErrors ? ini_set('display_startup_errors', 1) : ini_set('display_startup_errors', 0);
require_once "config.php";
// Oturum kontrolü
session_start();
session_regenerate_id(true);

if (!isset($_SESSION["admin_id"])) {
    header("Location: admin_login.php"); // Giriş sayfasına yönlendir
    exit();
}

require_once "db_connection.php"; // Veritabanı bağlantısı

// Kullanıcı bilgilerini kullanabilirsiniz
$admin_id = $_SESSION["admin_id"];
$admin_username = $_SESSION["admin_username"];

// Öğrenci listesi sorgusu
$query = "SELECT students.*, parents.*, emergency_contacts.*, addresses.* 
          FROM students 
          LEFT JOIN parents ON students.id = parents.student_id 
          LEFT JOIN emergency_contacts ON students.id = emergency_contacts.student_id 
          LEFT JOIN addresses ON students.id = addresses.student_id";

$stmt = $db->query($query);

// Öğrenci verilerini alın
$students = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<?php
require_once "admin_panel_header.php";
?>
<div class="container-fluid">
    <div class="row">
        <?php
        require_once "admin_panel_sidebar.php";
        ?>
        <main role="main" class="col-md-9 ml-sm-auto col-lg-10 pt-3 px-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3">
                <h2>Öğrenci Listesi</h2>
            </div>

            <style>
                table {
                    width: 100%;
                    border-collapse: collapse;
                    overflow-x: auto;
                    background-color: #f8f9fa; /* Arka plan rengi */
                }

                th, td {
                    border: 1px solid #dee2e6; /* Kenarlık rengi */
                    padding: 12px;
                    text-align: left;
                    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); /* Hafif gölgelendirme */
                }

                th {
                    background-color: #6b6b6b; /* Başlık arka plan rengi */
                    color: #ffffff; /* Başlık metin rengi */
                }

                tbody tr:nth-child(even) {
                    background-color: #f2f2f2; /* Sıra arka plan rengi */
                }

                tbody tr:hover {
                    background-color: #e9ecef; /* Hover efekti arka plan rengi */
                }

                .action-buttons {
                    display: flex;
                    flex-direction: column;
                    align-items: center;
                }

                .action-buttons a {
                    display: block;
                    margin-bottom: 5px;
                    padding: 8px;
                    text-align: center;
                    background-color: #007bff; /* Buton arka plan rengi */
                    color: #ffffff; /* Buton metin rengi */
                    text-decoration: none;
                    border-radius: 5px;
                }

                .action-buttons a:hover {
                    background-color: #0056b3; /* Hover efekti arka plan rengi */
                }
            </style>
            <table>
    <thead>
    <tr>
        <th>Adı</th>
        <th>Soyadı</th>
        <th>T.C. Kimlik No</th>
        <th>Doğum Tarihi</th>
        <th>Cep Telefonu</th>
        <th>E-posta</th>
        <!-- <th>Veli Ad Soyad</th>
         <th>Veli Telefon</th>
         <th>Veli E-Posta</th>
           <th>Acil Durumda Aranacak Kişi</th>
           <th>Acil Durumda Aranacak Kişi Telefonu</th>
           <th>Kan Grubu</th>
           <th>Bilinen Rahatsızlık</th>
           <th>İl</th>
           <th>İlçe</th>
           <th>Adres</th>-->
          <th>İşlemler</th>
      </tr>
      </thead>
      <tbody>
      <!-- Öğrenci bilgilerini listeleyen döngü -->
    <?php foreach ($students as $student): ?>
        <tr>
            <td><?php echo $student['firstname']; ?></td>
            <td><?php echo $student['lastname']; ?></td>
            <td><?php echo $student['tc_identity']; ?></td>
            <td><?php echo date('d.m.Y', strtotime($student['birthdate'])); ?></td>
            <td><?php echo $student['phone']; ?></td>
            <td><?php echo $student['email']; ?></td>
            <!--<td><?php echo $student['parent_firstname'] . ' ' . $student['parent_lastname']; ?></td>
            <td><?php echo $student['parent_phone']; ?></td>
            <td><?php echo $student['parent_email']; ?></td>
             <td><?php echo $student['emergency_contact']; ?></td>
            <td><?php echo $student['emergency_phone']; ?></td>
            <td><?php echo $student['blood_type']; ?></td>
            <td><?php echo $student['health_issue']; ?></td>
            <td><?php echo $student['city']; ?></td>
            <td><?php echo $student['district']; ?></td>
            <td><?php echo $student['address']; ?></td>-->
            <td>
                <a class="btn btn-primary" href="student_profile.php?id=<?php echo $student['id']; ?>"><i class="fas fa-user"></i></a>
                <a class="btn btn-warning" href="edit_student.php?id=<?php echo $student['id']; ?>"><i class="fas fa-edit"></i></a>
                <a class="btn btn-danger" href="delete_student.php?id=<?php echo $student['id']; ?>"><i class="fas fa-trash-alt"></i></a>
            </td>


        </tr>
    <?php endforeach; ?>
    </tbody>
</table>

<?php
require_once "footer.php";
?>
