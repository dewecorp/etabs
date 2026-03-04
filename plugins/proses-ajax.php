<?php
include '../inc/koneksi.php';
include "../inc/rupiah.php";

 $output = '';  
 if(isset($_POST["nis"])) {  
        $nis = mysqli_real_escape_string($koneksi, $_POST["nis"]);
        $sql = "select sum(setor)-sum(tarik) as Total from tb_tabungan where nis= '$nis'";
        $hasil = mysqli_query($koneksi, $sql);  
        while($row = mysqli_fetch_array($hasil)) {  
            $output = $row["Total"];
        }  
        echo rupiah($output);  
    }
