<!-- Content Header (Page header) -->
<section class="content-header">
	<h1>
		Transaksi
		<small>Info Kas</small>
	</h1>
</section>

<section class="content">
	<div class="rounded-2xl bg-white shadow-sm">
		<div class="border-b border-slate-100 px-6 py-4">
			<h3 class="text-lg font-semibold text-slate-900">
                <i class="fa-solid fa-money-bill-wave text-indigo-500 mr-2"></i>Info Kas
            </h3>
		</div>
		<!-- /.box-header -->
		<!-- form start -->
		<form action="?page=kas_tabungan" method="post" enctype="multipart/form-data">
			<div class="p-6 space-y-6">

				<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-1.5">
                        <label class="text-sm font-medium text-slate-700  Awal</label>">
                        <div class="relative">
                            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                <i class="fa-regular fa-calendar text-slate-400"></i>
                            </div>
                            <input type="date" name="tgl_1" id="tgl_1" class="block w-full rounded-xl border border-slate-200 bg-white pl-10 pr-4 py-2.5 text-sm text-slate-700 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20">
                        </div>
                    </div>

                    <div class="space-y-1.5">
                        <label class="text-sm font-medium text-slate-700  Akhir</label>">
                        <div class="relative">
                            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                <i class="fa-regular fa-calendar text-slate-400"></i>
                            </div>
                            <input type="date" name="tgl_2" id="tgl_2" class="block w-full rounded-xl border border-slate-200 bg-white pl-10 pr-4 py-2.5 text-sm text-slate-700 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20">
                        </div>
                    </div>
                </div>

			</div>
			<!-- /.box-body -->

			<div class="px-6 py-4 bg-slate-50 border-t border-slate-100   flex justify-end">
				<button type="submit" name="btnCetak" class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-medium text-white hover:bg-indigo-700 hover:shadow-lg hover:shadow-indigo-500/30 transition-all">
                    <i class="fa-solid fa-print"></i> Cetak Periode
                </button>
			</div>
		</form>
	</div>
</section>

