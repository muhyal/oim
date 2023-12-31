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
global $resetPasswordDescription, $db, $showErrors, $siteName, $siteShortName, $siteUrl, $config;
// Hata mesajlarını göster veya gizle ve ilgili işlemleri gerçekleştir
$showErrors ? ini_set('display_errors', 1) : ini_set('display_errors', 0);
$showErrors ? ini_set('display_startup_errors', 1) : ini_set('display_startup_errors', 0);
require_once "config.php";

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;

// Oturum kontrolü
session_start();
session_regenerate_id(true);

require_once "db_connection.php"; // Veritabanı bağlantısı
// Load Composer's autoloader
require 'vendor/autoload.php';

if (isset($_POST["reset_request"])) {
    // Şifre sıfırlama talebi gönderildiğinde
    $email = $_POST["email"];

    // Veritabanında kullanıcıyı e-posta adresine göre ara
    $query = "SELECT * FROM users WHERE email = ?";
    $stmt = $db->prepare($query);
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        // Belirlediğiniz zaman dilimini ayarlayın
        date_default_timezone_set('Europe/Istanbul');

        // Şifre sıfırlama için token oluştur
        $token = bin2hex(random_bytes(32));
        $tokenExpiry = date("Y-m-d H:i:s", strtotime("+1 hour"));

        // Token'ı ve süresini veritabanına kaydet
        $updateQuery = "UPDATE users SET reset_token = ?, reset_token_expiry = ? WHERE id = ?";
        $updateStmt = $db->prepare($updateQuery);
        $updateStmt->execute([$token, $tokenExpiry, $user["id"]]);

        // Token ile şifre sıfırlama bağlantısı oluştur
        $resetLink = "reset_password.php?token=" . $token;

        // E-posta gönderme işlemi
        $mail = new PHPMailer(true);
        try {
            // SMTP ayarları
            $mail->isSMTP();
            $mail->Host = $config['smtp']['host'];
            $mail->SMTPAuth = true;
            $mail->Username = $config['smtp']['username'];
            $mail->Password = $config['smtp']['password'];
            $mail->SMTPSecure = $config['smtp']['encryption'];
            $mail->Port = $config['smtp']['port'];
            $mail->CharSet = $config['smtp']['mailCharset'];
            $mail->ContentType = $config['smtp']['mailContentType'];

            // E-posta ayarları
            $mail->setFrom($config['smtp']['username'], $siteName);
            $mail->addAddress($email, $user["firstname"]); // Alıcı adresi ve adı

            $mail->isHTML(true);
            $mail->Subject = '=?UTF-8?B?' . base64_encode('Şifre Sıfırlama Talebi') . '?='; // Encode subject in UTF-8
            $mail->Body    = "Merhaba, eğer bu şifre sıfırlama isteğini siz talep ettiyseniz, <a href='$siteUrl/$resetLink'>şifrenizi sıfırlamak için tıklayın</a>. Siz talep etmediyseniz farklı bir işlem yapmanız gerekmeyecektir.";

            $mail->send();

            echo "Şifre sıfırlama bağlantısı e-posta adresinize gönderildi.";
        } catch (Exception $e) {
            echo "E-posta gönderilirken bir hata oluştu: {$mail->ErrorInfo}";
        }
    } else {
        echo "Bu e-posta adresine sahip bir kullanıcı bulunamadı.";
    }

} elseif (isset($_GET["token"])) {
    // Token ile gelen şifre sıfırlama isteği
    $token = $_GET["token"];

    // Token'ın geçerliliğini kontrol et
    $query = "SELECT * FROM users WHERE reset_token = ? AND reset_token_expiry >= NOW()";
    $stmt = $db->prepare($query);
    $stmt->execute([$token]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        // Form gönderildiğinde yeni şifreyi güncelle
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $newPassword = $_POST["new_password"];
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

            // Şifreyi güncelle
            $updateQuery = "UPDATE users SET password = ?, reset_token = NULL, reset_token_expiry = NULL WHERE id = ?";
            $updateStmt = $db->prepare($updateQuery);
            $updateStmt->execute([$hashedPassword, $user["id"]]);

            echo "Şifreniz başarıyla güncellendi ve oturum açma ekranına yönlendiriliyorsunuz...";
            header("refresh:3;url=user_login.php"); // 3 saniye sonra login.php'ye yönlendirme
        }
    } else {
        echo "Geçersiz ya da süresi dolmuş bir şifre sıfırlama bağlantısı.";
    }
}
require_once "header.php";
?>
    <main class="form-signin w-100 m-auto">
        <img class="mb-4" src="./assets/brand/default_logo.png" alt="<?php echo $siteName ?>" title="<?php echo $siteName ?>" width="100" height="100">
            <div class="d-grid gap-2 d-sm-flex justify-content-sm-center">
                <?php if (!isset($_GET["token"])): ?>
                    <!-- Şifre sıfırlama talebi gönderme formu -->
                    <form method="post" action="">
                        <label class="form-label" for="email">E-posta:</label><br>
                        <input class="form-control" type="email" id="email" name="email" required><br>

                        <p class="mb-4"><small><?php echo $resetPasswordDescription ?></small></p>

                        <div class="form-group mt-3">
                            <button class="btn btn-primary w-100 py-2" name="reset_request" type="submit">
                                <i class="fas fa-sign-in-alt"></i> Şifre Sıfırlama Talebi Gönder
                            </button>
                        </div>
                        <div class="form-group mt-2">
                            <a href="<?php echo $siteUrl ?>" class="btn btn-secondary w-100 py-2">
                                <i class="fas fa-home"></i> <?php echo $siteName ?> - <?php echo $siteShortName ?>
                            </a>
                        </div>
                    </form>
                <?php else: ?>
                    <!-- Yeni şifre belirleme formu -->
                    <form method="post" action="">
                        <label class="form-label" for="new_password">Yeni Şifre:</label><br>
                        <input class="form-control" type="password" id="new_password" name="new_password" required><br>
                        <input type="submit" class="btn btn-primary" value="Şifreyi Güncelle">
                    </form>
                <?php endif; ?>
        </div>
    </div>
    </main>

<?php
require_once "footer.php";
?>