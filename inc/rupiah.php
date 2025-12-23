<?php 
//membuat format rupiah dengan PHP 
function rupiah($angka){
	// Handle null, empty, or invalid values
	if ($angka === null || $angka === '' || !is_numeric($angka)) {
		$angka = 0;
	}
	$hasil_rupiah = "Rp " . number_format((float)$angka, 2, ',', '.');
	return $hasil_rupiah;
}
