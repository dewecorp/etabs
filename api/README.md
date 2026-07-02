# API SIMAD Integration

API untuk integrasi aplikasi SIMAD dengan sistem tabungan ETABS.

## Base URL
`https://etabs.misultanfattah.sch.id/api/simad.php`

## Autentikasi
Gunakan API Key via header `X-API-KEY` atau parameter `api_key` di URL.

**API Key Default:** `SIMAD_SECRET_KEY_2026`

Contoh header:
```
X-API-KEY: SIMAD_SECRET_KEY_2026
```

## Endpoints

### 1. Get Tabungan Summary
Mendapatkan ringkasan tabungan siswa (total setoran, total tarikan, saldo).

**Endpoint:** `GET /api/simad.php?action=tabungan&nis=[NIS_SISWA]`

**Parameter:**
- `action` (wajib): `tabungan`
- `nis` (wajib): NIS siswa

**Contoh Request:**
```http
GET /api/simad.php?action=tabungan&nis=12345
X-API-KEY: SIMAD_SECRET_KEY_2026
```

**Contoh Response:**
```json
{
    "success": true,
    "data": {
        "nis": "12345",
        "nama_siswa": "John Doe",
        "total_setor": 500000,
        "total_tarik": 150000,
        "saldo": 350000
    }
}
```

---

### 2. Get Riwayat Transaksi
Mendapatkan riwayat transaksi tabungan siswa.

**Endpoint:** `GET /api/simad.php?action=riwayat&nis=[NIS_SISWA]`

**Parameter:**
- `action` (wajib): `riwayat`
- `nis` (wajib): NIS siswa
- `page` (opsional): Halaman (default: 1)
- `limit` (opsional): Jumlah data per halaman (default: 50)

**Contoh Request:**
```http
GET /api/simad.php?action=riwayat&nis=12345&page=1&limit=10
X-API-KEY: SIMAD_SECRET_KEY_2026
```

**Contoh Response:**
```json
{
    "success": true,
    "data": {
        "nis": "12345",
        "nama_siswa": "John Doe",
        "transaksi": [
            {
                "id": 123,
                "tanggal": "2026-07-02",
                "jenis": "setoran",
                "nominal": 100000,
                "petugas": "Admin"
            },
            {
                "id": 120,
                "tanggal": "2026-06-28",
                "jenis": "tarikan",
                "nominal": 50000,
                "petugas": "Petugas 1"
            }
        ],
        "pagination": {
            "page": 1,
            "limit": 10,
            "total_transaksi": 25,
            "total_halaman": 3
        }
    }
}
```

---

## Response Error
Jika terjadi kesalahan, response akan berformat:

```json
{
    "success": false,
    "message": "Pesan error"
}
```

## Kode Status HTTP
- `200`: Berhasil
- `400`: Parameter tidak valid
- `401`: API Key tidak valid
