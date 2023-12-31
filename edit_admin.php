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
global $db, $showErrors, $siteName, $siteShortName, $siteUrl;
// Hata mesajlarını göster veya gizle ve ilgili işlemleri gerçekleştir
$showErrors ? ini_set('display_errors', 1) : ini_set('display_errors', 0);
$showErrors ? ini_set('display_startup_errors', 1) : ini_set('display_startup_errors', 0);
require_once "config.php";
require_once "db_connection.php";

// Oturum kontrolü
session_start();
session_regenerate_id(true);

if (!isset($_SESSION["admin_id"])) {
    header("Location: admin_login.php"); // Giriş sayfasına yönlendir
    exit();
}

$allowedRoles = array(1); // "sa" rolü için rol değeri (örneğin 1)
$currentUserRole = $_SESSION['admin_role'];

if (!in_array($currentUserRole, $allowedRoles)) {
    header("Location: access_denied.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $admin_id = $_POST["admin_id"];
    $new_username = $_POST["new_username"];
    $new_phone = $_POST["new_phone"];
    $new_email = $_POST["new_email"];
    $new_password = $_POST["new_password"];

    // Şifre değişikliği yapılacak mı kontrolü
    if (!empty($new_password)) {
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $update_query = "UPDATE admins SET username = ?, phone = ?, email = ?, password = ? WHERE id = ?";
        $stmt = $db->prepare($update_query);
        $stmt->execute([$new_username, $new_phone, $new_email, $hashed_password, $admin_id]);
    } else {
        $update_query = "UPDATE admins SET username = ?, phone = ?, email = ? WHERE id = ?";
        $stmt = $db->prepare($update_query);
        $stmt->execute([$new_username, $new_phone, $new_email, $admin_id]);
    }

    header("Location: admin_list.php"); // Yönetici listesine geri dön
    exit();
}

// Yönetici verisini çekme
if (isset($_GET["id"])) {
    $admin_id = $_GET["id"];
    $select_query = "SELECT * FROM admins WHERE id = ?";
    $stmt = $db->prepare($select_query);
    $stmt->execute([$admin_id]);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);
}
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
                <h2>Yönetici Düzenle</h2>
            </div>

<form method="post" action="">
    <input type="hidden" name="admin_id" value="<?php echo $admin['id']; ?>">

    <div class="form-group mt-3">
    <label class="form-label"  for="new_username">Yeni Kullanıcı Adı:</label>
    <input class="form-control" type="text" id="new_username" name="new_username" value="<?php echo $admin['username']; ?>" required>
    </div>

    <div class="form-group mt-3">
    <label class="form-label"  for="new_phone">Yeni Telefon:</label>
    <input class="form-control" type="text" id="new_phone" name="new_phone" value="<?php echo $admin['phone']; ?>" required>
    </div>

    <div class="form-group mt-3">
    <label class="form-label"  for="new_email">Yeni E-posta:</label>
    <input class="form-control" type="email" id="new_email" name="new_email" value="<?php echo $admin['email']; ?>" required>
        </div>

    <div class="form-group mt-3">
    <label class="form-label"  for="new_password">Yeni Şifre (Boş bırakabilirsiniz):</label>
    <input class="form-control" type="password" id="new_password" name="new_password">    </div>

    <div class="form-group mt-3">
    <button type="submit" class="btn btn-primary">Güncelle</button>
    </div>

</form>
<?php
require_once "footer.php";
?>
