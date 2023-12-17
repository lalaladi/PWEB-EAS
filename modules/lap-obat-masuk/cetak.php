<?php
require_once '../../vendor/autoload.php';
require_once "../../config/database.php";
include "../../config/fungsi_tanggal.php";
include "../../config/fungsi_rupiah.php";
require('../../assets/plugins/fpdf/fpdf.php');

use FPDF;

class PDF extends FPDF
{
    function Header()
    {
        $this->SetFont('Arial', 'B', 12);
        $this->Cell(0, 10, 'LAPORAN DATA OBAT MASUK', 0, 1, 'C');
        $this->Ln(10);
    }

    function FancyTable($header, $data, $columnWidths)
    {
        $this->SetFillColor(232, 235, 239);
        $this->SetFont('Arial', 'B', 10);

        // Set column widths
        foreach ($columnWidths as $width) {
            $this->Cell($width, 7, $width > 0 ? '' : '', 0); // Use 0 as the border argument to avoid drawing cell borders
        }
        $this->Ln();

        // Set header
        $this->SetFont('Arial', 'B', 10);
        foreach ($header as $key => $col) {
            $this->Cell($columnWidths[$key], 7, $col, 1, 0, 'C');
        }
        $this->Ln();

        // Set data
        $this->SetFont('Arial', '', 10);
        foreach ($data as $row) {
            foreach ($row as $key => $col) {
                $this->Cell($columnWidths[$key], 7, $col, 1, 0, 'C');
            }
            $this->Ln();
        }
    }
}

$hari_ini = date("d-m-Y");
$no = 1;

// ambil data hasil submit dari form
$tgl1 = $_GET['tgl_awal'];
$explode = explode('-', $tgl1);
$tgl_awal = $explode[2] . "-" . $explode[1] . "-" . $explode[0];

$tgl2 = $_GET['tgl_akhir'];
$explode = explode('-', $tgl2);
$tgl_akhir = $explode[2] . "-" . $explode[1] . "-" . $explode[0];

if (isset($_GET['tgl_awal'])) {
    $no = 1;
    // fungsi query untuk menampilkan data dari tabel obat masuk
    $query = mysqli_query($mysqli, "SELECT a.kode_transaksi,a.tanggal_masuk,a.kode_obat,a.jumlah_masuk,b.kode_obat,b.nama_obat,b.satuan
                                    FROM is_obat_masuk as a INNER JOIN is_obat as b ON a.kode_obat=b.kode_obat
                                    WHERE a.tanggal_masuk BETWEEN '$tgl_awal' AND '$tgl_akhir'
                                    ORDER BY a.kode_transaksi ASC")
        or die('Ada kesalahan pada query tampil Transaksi : ' . mysqli_error($mysqli));
    $count  = mysqli_num_rows($query);
}

$pdf = new PDF();
$pdf->AddPage();

$header = array('NO.', 'KODE TRANSAKSI', 'TANGGAL', 'KODE OBAT', 'NAMA OBAT', 'JUMLAH MASUK', 'SATUAN');
$data = array();

while ($row = mysqli_fetch_assoc($query)) {
    $tanggal = $row['tanggal_masuk'];
    $exp = explode('-', $tanggal);
    $tanggal_masuk = $exp[2] . "-" . $exp[1] . "-" . $exp[0];

    $data[] = array(
        $no,
        $row['kode_transaksi'],
        $tanggal_masuk,
        $row['kode_obat'],
        $row['nama_obat'],
        $row['jumlah_masuk'],
        $row['satuan']
    );

    $no++;
}

// Set custom column widths (adjust as needed)
$columnWidths = array(10, 35, 25, 25, 40, 35, 20);

$pdf->FancyTable($header, $data, $columnWidths);

$filename = "LAPORAN DATA OBAT MASUK.pdf";

ob_end_clean();
$pdf->Output($filename, 'I'); // 'I' will display the PDF inline
?>
