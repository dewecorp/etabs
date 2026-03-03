<?php 

function tgl_indo($tgl) {
$tanggal = substr($tgl, 8, 2);
$bulan = substr($tgl, 5, 2);
$tahun = substr($tgl, 0, 4);
return $tanggal."/".$bulan."/".$tahun;
}

function format_hari_tanggal($waktu)
{
$hari_array = array(
'Minggu',
'Senin',
'Selasa',
'Rabu',
'Kamis',
'Jumat',
'Sabtu'
);
$hr = date('w', strtotime($waktu));
$hari = $hari_array[$hr];
$tanggal = date('j', strtotime($waktu));
$bulan_array = array(
1 => 'Januari',
2 => 'Februari',
3 => 'Maret',
4 => 'April',
5 => 'Mei',
6 => 'Juni',
7 => 'Juli',
8 => 'Agustus',
9 => 'September',
10 => 'Oktober',
11 => 'November',
12 => 'Desember',
);
$bl = date('n', strtotime($waktu));
$bulan = $bulan_array[$bl];
$tahun = date('Y', strtotime($waktu));
$jam = date( 'H:i:s', strtotime($waktu));

//untuk menampilkan hari, tanggal bulan tahun jam
//return "$hari, $tanggal $bulan $tahun $jam";
//untuk menampilkan hari, tanggal bulan tahun
return "$hari, $tanggal $bulan $tahun";
}

// Fungsi untuk mendapatkan page title dinamis
function getPageTitle($page = '') {
    // Mapping title untuk setiap halaman
    $pageTitles = array(
        // Dashboard
        'admin' => 'Dashboard Administrator',
        'petugas' => 'Dashboard Petugas',
        
        // Master Data - Pengguna
        'MyApp/data_pengguna' => 'Data Pengguna',
        'MyApp/add_pengguna' => 'Tambah Pengguna',
        'MyApp/edit_pengguna' => 'Edit Pengguna',
        'MyApp/del_pengguna' => 'Hapus Pengguna',
        
        // Master Data - Profil
        'MyApp/data_profil' => 'Profil Sekolah',
        'MyApp/edit_profil' => 'Edit Profil Sekolah',
        
        // Master Data - Kelas
        'MyApp/data_kelas' => 'Data Kelas',
        'MyApp/add_kelas' => 'Tambah Kelas',
        'MyApp/edit_kelas' => 'Edit Kelas',
        'MyApp/del_kelas' => 'Hapus Kelas',
        
        // Master Data - Siswa
        'MyApp/data_siswa' => 'Data Siswa',
        'MyApp/add_siswa' => 'Tambah Siswa',
        'MyApp/edit_siswa' => 'Edit Siswa',
        'MyApp/edit_siswa_multiple' => 'Edit Multiple Siswa',
        'MyApp/del_siswa' => 'Hapus Siswa',
        'MyApp/del_siswa_multiple' => 'Hapus Multiple Siswa',
        
        // Backup & Restore
        'MyApp/backup_restore' => 'Backup & Restore',
        
        // Aktivitas
        'MyApp/view_activity' => 'Activity Log',
        'MyApp/activity_log' => 'Activity Log',
        
        // Transaksi - Setoran
        'data_setor' => 'Data Setoran',
        'edit_setor_multiple' => 'Edit Multiple Setoran',
        'del_setor' => 'Hapus Setoran',
        'del_setor_multiple' => 'Hapus Multiple Setoran',
        
        // Transaksi - Tarikan
        'data_tarik' => 'Data Tarikan',
        'edit_tarik_multiple' => 'Edit Multiple Tarikan',
        'del_tarik' => 'Hapus Tarikan',
        'del_tarik_multiple' => 'Hapus Multiple Tarikan',
        
        // Riwayat
        'data_riwayat' => 'Data Riwayat',
        'del_riwayat' => 'Hapus Riwayat',
        
        // Tabungan
        'data_tabungan' => 'Data Tabungan',
        'view_tabungan' => 'View Tabungan',
        
        // Kas
        'kas_tabungan' => 'Kas Tabungan',
        'kas_full' => 'Kas Full',
        'view_kas' => 'Info Kas',
        
        // Laporan
        'laporan' => 'Laporan',
    );
    
    // Jika halaman ada di mapping, gunakan title tersebut
    if (!empty($page) && isset($pageTitles[$page])) {
        return $pageTitles[$page];
    }
    
    // Jika halaman kosong atau tidak ditemukan, kembalikan default berdasarkan konteks
    if (empty($page)) {
        // Default akan ditentukan di index.php berdasarkan level user
        return 'Dashboard';
    }
    
    // Jika halaman tidak ditemukan di mapping
    return 'Halaman Tidak Ditemukan';
}






 ?>
