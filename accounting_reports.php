<?php
global $showErrors, $db;
require 'vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

// Oturum kontrolü
session_start();
if (!isset($_SESSION["admin_id"])) {
    header("Location: admin_login.php"); // Giriş sayfasına yönlendir
    exit();
}

require_once "db_connection.php";
require_once "config.php";

// Hata mesajlarını göster veya gizle ve ilgili işlemleri gerçekleştir
$showErrors ? ini_set('display_errors', 1) : ini_set('display_errors', 0);
$showErrors ? ini_set('display_startup_errors', 1) : ini_set('display_startup_errors', 0);

// Bugünün tarihini al
$currentDate = date('d-m-Y');

// Uyarı mesajları için değişkenler
$alertMessage = '';
$alertColor = '';

// Form gönderildi mi kontrolü
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Rapor alma işlemi
    if (isset($_POST["generate_report"])) {
        $start_date = $_POST["start_date"];
        $end_date = $_POST["end_date"];

        // Akademileri getir
        $academiesSql = "SELECT * FROM academies";
        $stmtAcademies = $db->prepare($academiesSql);
        $stmtAcademies->execute();
        $academies = $stmtAcademies->fetchAll(PDO::FETCH_ASSOC);

        // Excel dosyasını oluştur
        $spreadsheet = new Spreadsheet();

        foreach ($academies as $academy) {
            $academyName = $academy['name'];

            // Akademi sayfasını oluştur
            $spreadsheet->createSheet()->setTitle($academyName);
            $spreadsheet->setActiveSheetIndexByName($academyName);
            $sheet = $spreadsheet->getActiveSheet();

            // Türkçe karakter sorunlarını önlemek için
            $sheet->setCellValue('A1', 'Akademi');
            $sheet->setCellValue('B1', 'Toplam Tutar');

            // Bu tarih aralığındaki alınan ödemeleri getir
            $sql = "
                SELECT 
                    academies.name AS academy_name,
                    SUM(accounting_entries.amount) AS total_amount
                FROM accounting_entries
                INNER JOIN academies ON accounting_entries.academy_id = academies.id
                WHERE academies.id = :academy_id
                AND accounting_entries.entry_date BETWEEN :start_date AND :end_date
                GROUP BY accounting_entries.academy_id
            ";

            $stmt = $db->prepare($sql);
            $stmt->bindParam(":academy_id", $academy['id'], PDO::PARAM_INT);
            $stmt->bindParam(":start_date", $start_date, PDO::PARAM_STR);
            $stmt->bindParam(":end_date", $end_date, PDO::PARAM_STR);
            $stmt->execute();
            $reportData = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $row = 2;
            foreach ($reportData as $data) {
                $sheet->setCellValue('A' . $row, $data['academy_name']);
                $sheet->setCellValue('B' . $row, $data['total_amount']);
                $row++;
            }

            // Öğrenci Detayları
            $sheet->setCellValue('D1', 'Öğrenci Adı');
            $sheet->setCellValue('E1', 'Ders Adı');
            $sheet->setCellValue('F1', 'Ödeme Tarihi');
            $sheet->setCellValue('G1', 'Ödeme Tutarı');

            // Öğrenci Detayları için sorgu
            $studentDetailSql = "
                SELECT 
                    students.firstname AS student_name,
                    courses.course_name,
                    accounting_entries.entry_date AS payment_date,
                    accounting_entries.amount AS payment_amount
                FROM accounting_entries
                INNER JOIN students ON accounting_entries.student_id = students.id
                INNER JOIN courses ON accounting_entries.course_id = courses.id
                WHERE accounting_entries.academy_id = :academy_id
                AND accounting_entries.entry_date BETWEEN :start_date AND :end_date
            ";

            $stmtStudentDetail = $db->prepare($studentDetailSql);
            $stmtStudentDetail->bindParam(":academy_id", $academy['id'], PDO::PARAM_INT);
            $stmtStudentDetail->bindParam(":start_date", $start_date, PDO::PARAM_STR);
            $stmtStudentDetail->bindParam(":end_date", $end_date, PDO::PARAM_STR);
            $stmtStudentDetail->execute();
            $studentDetailData = $stmtStudentDetail->fetchAll(PDO::FETCH_ASSOC);

            $row = 2;
            foreach ($studentDetailData as $studentDetail) {
                $sheet->setCellValue('D' . $row, $studentDetail['student_name']);
                $sheet->setCellValue('E' . $row, $studentDetail['course_name']);
                $sheet->setCellValue('F' . $row, $studentDetail['payment_date']);
                $sheet->setCellValue('G' . $row, $studentDetail['payment_amount']);
                $row++;
            }
        }

        // Hata ayıklama
        error_reporting(E_ALL);
        ini_set('display_errors', TRUE);
        ini_set('display_startup_errors', TRUE);

        // Excel dosyasını indir
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="rapor.xlsx"');
        header('Cache-Control: max-age=0');

        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }
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
                <h2>Muhasebe</h2>
            </div>

            <!-- Uyarı Mesajları -->
            <?php if (!empty($alertMessage)): ?>
                <div class="alert alert-<?= $alertColor ?>" role="alert">
                    <?= $alertMessage ?>
                </div>
            <?php endif; ?>

            <!-- Rapor Alma Formu -->
            <h5>Rapor Al</h5>
            <form method="post">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="start_date">Başlangıç Tarihi:</label>
                            <input type="date" name="start_date" class="form-control" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="end_date">Bitiş Tarihi:</label>
                            <input type="date" name="end_date" class="form-control" required>
                        </div>
                    </div>
                </div>
                <button type="submit" name="generate_report" class="btn btn-primary">Rapor Al</button>
            </form>

            <?php require_once "footer.php"; ?>
        </main>
    </div>
</div>