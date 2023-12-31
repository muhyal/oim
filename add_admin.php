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
session_start();
session_regenerate_id(true);

// Oturum kontrolü
if (!isset($_SESSION["admin_id"])) {
    header("Location: admin_login.php"); // Giriş sayfasına yönlendir
    exit();
}
require_once "db_connection.php";
require_once "config.php";
// Hata mesajlarını göster veya gizle ve ilgili işlemleri gerçekleştir
$showErrors ? ini_set('display_errors', 1) : ini_set('display_errors', 0);
$showErrors ? ini_set('display_startup_errors', 1) : ini_set('display_startup_errors', 0);

$allowedRoles = array(1); // "sa" rolü için rol değeri (örneğin 1)
$currentUserRole = $_SESSION['admin_role'];

if (!in_array($currentUserRole, $allowedRoles)) {
    header("Location: access_denied.php");
    exit;
}

?>
<?php
require_once "admin_panel_header.php";
?>
<div class="container-fluid">
    <div class="row">
        <?php require_once "admin_panel_sidebar.php"; ?>
        <main role="main" class="col-md-9 ml-sm-auto col-lg-10 pt-3 px-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3">
                <h2>Yönetici Kaydı</h2>
            </div>

            <form method="post" action="add_admin_process.php">
                <div class="form-group mt-3">
                    <label for="username">Yönetici Kullanıcı Adı:</label>
                    <input type="text" id="username" name="username" class="form-control" required>
                </div>

                <div class="form-group mt-3">
                    <label for="phone">Telefon:</label>
                    <input type="text" id="phone" name="phone" class="form-control" required>
                </div>

                <div class="form-group mt-3">
                    <label for="email">E-posta:</label>
                    <input type="email" id="email" name="email" class="form-control" required>
                </div>

                <div class="form-group mt-3">
                    <label for="password">Şifre:</label>
                    <div class="input-group">
                        <input type="password" id="password" name="password" class="form-control" required>
                        <div class="input-group-append">
                            <button type="button" class="btn btn-outline-secondary" onclick="togglePassword('password')">Şifreyi Göster</button>
                        </div>
                        <div class="input-group-append">
                            <button type="button" class="btn btn-outline-secondary" onclick="copyPassword('password')">Kopyala</button>
                        </div>
                        <div class="input-group-append">
                            <button type="button" class="btn btn-outline-secondary" onclick="generatePassword('password')">Şifre Üret</button>
                        </div>
                    </div>
                </div>
                <div class="form-group mt-3">
                <button type="submit" class="btn btn-primary">Kaydet</button>
                </div>
            </form>
        </main>
    </div>
</div>

<script>
    function togglePassword(passwordId) {
        var passwordInput = document.getElementById(passwordId);
        if (passwordInput.type === "password") {
            passwordInput.type = "text";
        } else {
            passwordInput.type = "password";
        }
    }

    function copyPassword(passwordId) {
        var passwordInput = document.getElementById(passwordId);
        passwordInput.select();
        document.execCommand("copy");
        alert("Şifre kopyalandı: " + passwordInput.value);
    }

    function generatePassword(passwordId) {
        var generatedPasswordInput = document.getElementById(passwordId);
        var xhr = new XMLHttpRequest();
        xhr.onreadystatechange = function () {
            if (xhr.readyState === 4 && xhr.status === 200) {
                generatedPasswordInput.value = xhr.responseText;
            }
        };
        xhr.open("GET", "generate_password.php", true);
        xhr.send();
    }
</script>

<?php require_once "footer.php"; ?>
