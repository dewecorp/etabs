<!-- Content Header (Page header) -->
<section class="content-header">
	<h1>
		Tabungan
		<small>Pencarian</small>
	</h1>
</section>

<section class="content">
	<div class="rounded-2xl bg-white shadow-sm">
		<div class="border-b border-slate-100 px-6 py-4">
			<h3 class="text-lg font-semibold text-slate-900">
                <i class="fa-solid fa-magnifying-glass text-indigo-500 mr-2"></i>Cari Siswa
            </h3>
		</div>
		<!-- /.box-header -->
		<!-- form start -->
		<form action="?page=data_tabungan" method="post" enctype="multipart/form-data">
			<div class="p-6 space-y-6">

				<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-1.5">
                        <label class="text-sm font-medium text-slate-700">Pilih Siswa</label>
                        <select name="nis" id="nis" class="auth-input appearance-none" required>
                            <option selected="selected">-- Pilih --</option>
                            <?php
                            // ambil data dari database
                            $query = "select * from tb_siswa where status='Aktif'";
                            $hasil = mysqli_query($koneksi, $query);
                            while ($row = mysqli_fetch_array($hasil)) {
                            ?>
                            <option value="<?php echo $row['nis'] ?>">
                                <?php echo $row['nis'] ?>
                                -
                                <?php echo $row['nama_siswa'] ?>
                            </option>
                            <?php
                            }
                            ?>
                        </select>
                    </div>

                    <div class="space-y-1.5">
                        <label class="text-sm font-medium text-slate-700">Jumlah Tabungan</label>
                        <div class="relative">
                            <input type="text" name="saldo" id="saldo" class="auth-input" placeholder="Saldo" readonly>
                        </div>
                    </div>
                </div>

			</div>
			<!-- /.box-body -->

			<div class="px-6 py-4 bg-slate-50 border-t border-slate-100   flex justify-end">
				<button type="submit" name="Lihat" value="Lihat" class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-medium text-white hover:bg-indigo-700 hover:shadow-lg hover:shadow-indigo-500/30 transition-all">
                    <i class="fa-solid fa-eye"></i> Lihat Data
                </button>
			</div>
		</form>
	</div>
</section>

<script>
	$(document).ready(function() {
		$('#nis').change(function() {
			var nis = $(this).val();
			$.ajax({
				url: "plugins/proses-ajax.php",
				method: "POST",
				data: {
					nis: nis
				},
				success: function(data) {
					$('#saldo').val(data);
				}
			});
		});
	});
</script>

