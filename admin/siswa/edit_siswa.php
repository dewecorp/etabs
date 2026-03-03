<?php

    if(isset($_GET['kode'])){
        $sql_cek = "SELECT * FROM tb_siswa WHERE nis='".$_GET['kode']."'";
        $query_cek = mysqli_query($koneksi, $sql_cek);
        $data_cek = mysqli_fetch_array($query_cek,MYSQLI_BOTH);
    }
?>

<section class="content-header">
    <h1>
        Master Data
        <small>Siswa</small>
    </h1>
</section>

<section class="content">
    <div class="rounded-2xl bg-white shadow-sm">
        <div class="border-b border-slate-100 px-6 py-4">
            <h3 class="text-lg font-semibold text-slate-900  flex items-center gap-2">
                <i class="fa-solid fa-user-pen text-indigo-500"></i> Ubah Data Siswa
            </h3>
        </div>
        <!-- /.box-header -->
        <!-- form start -->
        <form action="" method="post" enctype="multipart/form-data">
            <div class="p-6 space-y-6">

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-1.5">
                        <label class="text-sm font-medium text-slate-700">
                        <input type='text' class="block w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm text-slate-500 focus:outline-none    name="nis" value="<?php echo $data_cek['nis']; ?>" readonly>
                    </div>

                    <div class="space-y-1.5">
                        <label class="text-sm font-medium text-slate-700  Siswa</label>"><input class="block w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-700 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20     name="nama_siswa" value="<?php echo $data_cek['nama_siswa']; ?>" />
                    </div>

                    <div class="space-y-1.5">
                        <label class="text-sm font-medium text-slate-700  Kelamin</label>"><select name="jekel" id="jekel" class="block w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-700 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20     required>"><option value="">-- Pilih --</option>
                            <?php
                            //cek data yg dipilih sebelumnya
                            if ($data_cek['jekel'] == "LK") echo "<option value='LK' selected>LK</option>";
                            else echo "<option value='LK'>LK</option>";
                            
                            if ($data_cek['jekel'] == "PR") echo "<option value='PR' selected>PR</option>";
                            else echo "<option value='PR'>PR</option>";
                        ?>
                        </select>
                    </div>

                    <div class="space-y-1.5">
                        <label class="text-sm font-medium text-slate-700">
                        <select name="id_kelas" id="id_kelas" class="block w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-700 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20     required>"><option value="">-- Pilih --</option>
                            <?php
                                // ambil data dari database
                                $query = "select * from tb_kelas";
                                $hasil = mysqli_query($koneksi, $query);
                                while ($row = mysqli_fetch_array($hasil)) {

                                //mengecek data yang dipilih sebelumnya
                                ?>
                            <option value="<?php echo $row['id_kelas'] ?>" <?=$data_cek[
                             'id_kelas']==$row[ 'id_kelas'] ? "selected" : null ?>>
                                <?php echo $row['kelas'] ?>
                            </option>
                            <?php
                        }
                        ?>
                        </select>
                    </div>

                    <div class="space-y-1.5">
                        <label class="text-sm font-medium text-slate-700  Masuk</label>"><input class="block w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-700 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20     name="th_masuk" value="<?php echo $data_cek['th_masuk']; ?>">
                    </div>

                    <div class="space-y-1.5">
                        <label class="text-sm font-medium text-slate-700">
                        <select name="status" id="status" class="block w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-700 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20     required>"><option value="">-- Pilih --</option>
                            <?php
                            //cek data yg dipilih sebelumnya
                            if ($data_cek['status'] == "Aktif") echo "<option value='Aktif' selected>Aktif</option>";
                            else echo "<option value='Aktif'>Aktif</option>";
                            
                            if ($data_cek['status'] == "Lulus") echo "<option value='Lulus' selected>Lulus</option>";
                            else echo "<option value='Lulus'>Lulus</option>";

                            if ($data_cek['status'] == "Pindah") echo "<option value='Pindah' selected>Pindah</option>";
                            else echo "<option value='Pindah'>Pindah</option>";
                        ?>
                        </select>
                    </div>
                </div>

            </div>
            <!-- /.box-body -->

            <div class="px-6 py-4 bg-slate-50 border-t border-slate-100   flex justify-end gap-3">
                <a href="?page=MyApp/data_siswa" class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-medium text-slate-700 hover:bg-slate-50 hover:text-slate-900 focus:outline-none focus:ring-2 focus:ring-slate-200/50      transition-all">Batal</a>
                <button type="submit" name="Ubah" value="Ubah" class="inline-flex items-center justify-center gap-2 rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-medium text-white hover:bg-indigo-700 hover:shadow-lg hover:shadow-indigo-500/30 focus:outline-none focus:ring-2 focus:ring-indigo-500/50 transition-all">
                    <i class="fa-solid fa-floppy-disk"></i> Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</section>

<?php

if (isset ($_POST['Ubah'])){
    //mulai proses ubah
    $sql_ubah = "UPDATE tb_siswa SET
        nama_siswa='".$_POST['nama_siswa']."',
        jekel='".$_POST['jekel']."',
        id_kelas='".$_POST['id_kelas']."',
        th_masuk='".$_POST['th_masuk']."',
        status='".$_POST['status']."'
        WHERE nis='".$_POST['nis']."'";
    $query_ubah = mysqli_query($koneksi, $sql_ubah);

    if ($query_ubah) {
        // Log aktivitas - karena file di-include dari index.php, fungsi sudah tersedia
        // Tapi untuk memastikan, kita include lagi dengan path yang benar
        if (!function_exists('logActivity')) {
            // Coba beberapa path yang mungkin
            $paths = [
                __DIR__ . '/../../inc/activity_log.php',
                dirname(dirname(__DIR__)) . '/inc/activity_log.php',
                'inc/activity_log.php',
                '../../inc/activity_log.php'
            ];
            foreach ($paths as $path) {
                if (file_exists($path)) {
                    include_once $path;
                    break;
                }
            }
        }
        if (function_exists('logActivity')) {
            logActivity($koneksi, 'UPDATE', 'tb_siswa', 'Mengubah data siswa: ' . $_POST['nama_siswa'] . ' (NIS: ' . $_POST['nis'] . ')', $_POST['nis']);
        }
        
        echo "<script>
        (function(){
            if(typeof Swal!=='undefined'){
                Swal.fire({
                    title:'Berhasil!',
                    text:'Data siswa berhasil diubah',
                    icon:'success',
                    confirmButtonText:'OK',
                    confirmButtonColor:'#28a745',
                    allowOutsideClick:false,
                    allowEscapeKey:false,
                    timer:2500,
                    timerProgressBar:true
                }).then(function(){
                    window.location.href='index.php?page=MyApp/data_siswa';
                });
                
                // Auto redirect setelah 2.5 detik jika tidak diklik
                setTimeout(function(){
                    window.location.href='index.php?page=MyApp/data_siswa';
                }, 2500);
            }else{
                alert('Data siswa berhasil diubah');
                window.location.href='index.php?page=MyApp/data_siswa';
            }
        })();
        </script>";
        return;
        }else{
        echo "<script>
        (function(){
            if(typeof Swal!=='undefined'){
                Swal.fire({
                    title:'Gagal!',
                    text:'Data siswa gagal diubah',
                    icon:'error',
                    confirmButtonText:'OK',
                    confirmButtonColor:'#dc3545',
                    allowOutsideClick:false,
                    allowEscapeKey:false
                }).then(function(){
                    window.location.href='index.php?page=MyApp/data_siswa';
                });
            }else{
                alert('Data siswa gagal diubah');
                window.location.href='index.php?page=MyApp/data_siswa';
            }
        })();
        </script>";
        return;
    }
}

