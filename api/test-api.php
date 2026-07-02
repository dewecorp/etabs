<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test API SIMAD</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: Arial, sans-serif; }
        body { padding: 20px; max-width: 1200px; margin: 0 auto; }
        h1 { color: #2d3748; margin-bottom: 10px; }
        .info { color: #718096; margin-bottom: 20px; font-size: 14px; }
        .container { background: #f7fafc; padding: 20px; border-radius: 8px; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; color: #4a5568; }
        input, select { width: 100%; padding: 10px; border: 1px solid #e2e8f0; border-radius: 4px; font-size: 14px; }
        button { background: #4299e1; color: white; border: none; padding: 10px 20px; border-radius: 4px; cursor: pointer; font-size: 14px; }
        button:hover { background: #3182ce; }
        .result { margin-top: 20px; background: white; padding: 20px; border-radius: 4px; border: 1px solid #e2e8f0; }
        pre { background: #2d3748; color: #68d391; padding: 15px; border-radius: 4px; overflow-x: auto; }
        .error { color: #e53e3e; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Test API SIMAD</h1>
        <p class="info">Base URL Produksi: https://etabs.misultanfattah.sch.id/api/simad.php</p>
        
        <div class="form-group">
            <label for="api_key">API Key</label>
            <input type="text" id="api_key" value="SIMAD_SECRET_KEY_2026">
        </div>
        
        <div class="form-group">
            <label for="action">Action</label>
            <select id="action">
                <option value="tabungan">Tabungan Summary</option>
                <option value="riwayat">Riwayat Transaksi</option>
            </select>
        </div>
        
        <div class="form-group">
            <label for="nis">NIS Siswa</label>
            <input type="text" id="nis" placeholder="Masukkan NIS">
        </div>
        
        <button onclick="testApi()">Test API</button>
        
        <div id="result" class="result" style="display: none;">
            <h3>Hasil:</h3>
            <pre id="response"></pre>
        </div>
    </div>

    <script>
        async function testApi() {
            const apiKey = document.getElementById('api_key').value;
            const action = document.getElementById('action').value;
            const nis = document.getElementById('nis').value;
            
            if (!nis) {
                alert('Masukkan NIS!');
                return;
            }
            
            const url = `simad.php?action=${action}&nis=${encodeURIComponent(nis)}`;
            
            try {
                const response = await fetch(url, {
                    method: 'GET',
                    headers: {
                        'X-API-KEY': apiKey
                    }
                });
                
                const data = await response.json();
                
                document.getElementById('result').style.display = 'block';
                document.getElementById('response').textContent = JSON.stringify(data, null, 2);
            } catch (error) {
                document.getElementById('result').style.display = 'block';
                document.getElementById('response').textContent = 'Error: ' + error.message;
                document.getElementById('response').className = 'error';
            }
        }
    </script>
</body>
</html>
