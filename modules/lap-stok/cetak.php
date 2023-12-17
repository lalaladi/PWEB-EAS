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
        $this->Cell(0, 10, 'LAPORAN STOK OBAT', 0, 1, 'C');
        $this->Ln(10);
    }

    function FancyTable($header, $data, $columnWidths)
    {
        $this->SetFillColor(232, 235, 239);
        $this->SetFont('Arial', 'B', 10);

        // Set column widths
        foreach ($columnWidths as $width) {
            if (!empty($header)) {
                $this->Cell($width, 7, '', 0);
            }
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

$query = mysqli_query($mysqli, "SELECT kode_obat,nama_obat,harga_beli,harga_jual,satuan,stok FROM is_obat ORDER BY nama_obat ASC")
    or die('Ada kesalahan pada query tampil Data Obat: ' . mysqli_error($mysqli));

$header = array('NO.', 'KODE OBAT', 'NAMA OBAT', 'HARGA BELI', 'HARGA JUAL', 'STOK', 'SATUAN');
$data = array();

while ($row = mysqli_fetch_assoc($query)) {
    $harga_beli = format_rupiah($row['harga_beli']);
    $harga_jual = format_rupiah($row['harga_jual']);

    $data[] = array(
        $no,
        $row['kode_obat'],
        $row['nama_obat'],
        "Rp. $harga_beli",
        "Rp. $harga_jual",
        $row['stok'],
        $row['satuan']
    );

    $no++;
}

$pdf = new PDF();
$pdf->AddPage();
$columnWidths = array(10, 30, 40, 35, 35, 20, 20);

$pdf->FancyTable($header, $data, $columnWidths);

$filename = "LAPORAN STOK OBAT.pdf";

ob_end_clean();
$pdf->Output($filename, 'I');
?>
