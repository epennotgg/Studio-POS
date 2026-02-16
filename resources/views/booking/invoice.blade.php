<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice Booking - {{ $booking->booking_code }}</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f8f9fa;
        }
        .invoice-container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #007bff;
        }
        .header h1 {
            color: #007bff;
            margin: 0;
        }
        .header p {
            color: #6c757d;
            margin: 5px 0;
        }
        .booking-info {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
            margin-bottom: 30px;
        }
        .info-box {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            border-left: 4px solid #007bff;
        }
        .info-box h3 {
            margin: 0 0 10px 0;
            color: #495057;
            font-size: 16px;
        }
        .info-box p {
            margin: 5px 0;
            color: #212529;
        }
        .price-details {
            background: #e9ecef;
            padding: 20px;
            border-radius: 5px;
            margin-bottom: 30px;
        }
        .price-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #dee2e6;
        }
        .price-row.total {
            font-weight: bold;
            font-size: 18px;
            border-bottom: none;
            color: #007bff;
        }
        .footer {
            text-align: center;
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #dee2e6;
            color: #6c757d;
        }
        .status-badge {
            display: inline-block;
            padding: 5px 15px;
            border-radius: 20px;
            font-weight: bold;
            margin-top: 10px;
        }
        .status-pending { background: #ffc107; color: #212529; }
        .status-confirmed { background: #17a2b8; color: white; }
        .status-completed { background: #28a745; color: white; }
        .status-cancelled { background: #dc3545; color: white; }
        @media print {
            body { background: white; }
            .invoice-container { box-shadow: none; }
            .no-print { display: none; }
        }
    </style>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
</head>
<body>
    <div class="no-print" style="text-align: center; margin-bottom: 20px;">
        <button onclick="copyInvoiceToClipboard()" style="padding: 10px 20px; background: #28a745; color: white; border: none; border-radius: 5px; cursor: pointer; font-size: 16px; font-weight: bold; box-shadow: 0 2px 5px rgba(0,0,0,0.2); transition: all 0.2s;">
            📸 Salin Invoice ke Clipboard
        </button>
    </div>

    <div class="invoice-container">
        <div class="header">
            <h1>DTHREE STUDIO</h1>
            <p>Booking Code: <strong>{{ $booking->booking_code }}</strong></p>
            <p>Tanggal Cetak: {{ now()->format('d/m/Y H:i') }}</p>
        </div>

        <div class="booking-info">
            <div class="info-box">
                <h3>Informasi Pelanggan</h3>
                <p><strong>Nama:</strong> {{ $booking->customer_name }}</p>
                <p><strong>WhatsApp:</strong> {{ $booking->customer_phone }}</p>
                <p><strong>Jumlah Orang:</strong> {{ $booking->number_of_people }} orang</p>
            </div>
            
            <div class="info-box">
                <h3>Informasi Booking</h3>
                <p><strong>Kategori:</strong> {{ $booking->studio_category_label }}</p>
                <p><strong>Paket:</strong> {{ $booking->package_type }}</p>
                <p><strong>Tanggal:</strong> {{ $booking->booking_date->format('d/m/Y H:i') }}</p>
                <p><strong>Metode Pembayaran:</strong> {{ $booking->payment_method_label }}</p>
            </div>
            
            <div class="info-box">
                <h3>Status Booking</h3>
                <p><strong>Status:</strong> 
                    <span class="status-badge status-{{ $booking->status }}">
                        {{ $booking->status_label }}
                    </span>
                </p>
                <p><strong>Dibuat Oleh:</strong> {{ $booking->user->name }}</p>
                <p><strong>Tanggal Dibuat:</strong> {{ $booking->created_at->format('d/m/Y H:i') }}</p>
            </div>
            
            <div class="info-box">
                <h3>Catatan</h3>
                <p>{{ $booking->notes ?: 'Tidak ada catatan' }}</p>
            </div>
        </div>

        <div class="price-details">
            <h2 style="text-align: center; margin-bottom: 20px; color: #495057;">Rincian Pembayaran</h2>
            
            <!-- Item Booking -->
            <div class="price-row" style="border-bottom: 2px solid #007bff; padding-bottom: 10px; margin-bottom: 10px;">
                <span><strong>Item Booking:</strong></span>
                <span><strong>Qty: 1</strong></span>
            </div>
            
            <div class="price-row">
                <span>{{ $booking->studio_category_label }} - {{ $booking->package_type }}</span>
                <span>Rp {{ number_format($booking->base_package_price, 0, ',', '.') }}</span>
            </div>
            
            @if($booking->additional_charge > 0)
            <div class="price-row" style="color: #dc3545;">
                <span>Tambahan Orang ({{ $booking->number_of_people > 15 ? $booking->number_of_people - 15 : 0 }} orang × Rp 50.000)</span>
                <span>Rp {{ number_format($booking->additional_charge, 0, ',', '.') }}</span>
            </div>
            @endif
            
            <div class="price-row" style="border-top: 2px solid #dee2e6; padding-top: 15px;">
                <span><strong>Subtotal Paket:</strong></span>
                <span><strong>Rp {{ number_format($booking->package_price, 0, ',', '.') }}</strong></span>
            </div>
            
            <div class="price-row">
                <span>Down Payment (DP):</span>
                <span>Rp {{ number_format($booking->down_payment, 0, ',', '.') }}</span>
            </div>
            
            <div class="price-row">
                <span>Sisa Pembayaran:</span>
                <span>Rp {{ number_format($booking->remaining_amount, 0, ',', '.') }}</span>
            </div>
            
            <div class="price-row total">
                <span>TOTAL AMOUNT (HARGA FULL):</span>
                <span>Rp {{ number_format($booking->total_amount, 0, ',', '.') }}</span>
            </div>
            
            @if($booking->transaction)
            <div class="price-row" style="background: #d1ecf1; margin-top: 10px; padding: 10px; border-radius: 5px;">
                <span><strong>Sudah Dibayar (Transaksi):</strong></span>
                <span><strong>Rp {{ number_format($booking->total_amount, 0, ',', '.') }}</strong></span>
            </div>
            @endif
        </div>

        @if($booking->transaction)
        <div class="info-box" style="background: #d1ecf1; border-left-color: #0c5460;">
            <h3>Informasi Transaksi</h3>
            <p><strong>Invoice Transaksi:</strong> {{ $booking->transaction->invoice_id }}</p>
            <p><strong>Tanggal Transaksi:</strong> {{ $booking->transaction->created_at->format('d/m/Y H:i') }}</p>
        </div>
        @endif

        <div class="footer">
            <p>Terima kasih telah melakukan booking di studio kami.</p>
            <p>Invoice ini adalah bukti booking yang sah.</p>
            <p class="no-print">* Invoice ini tidak dapat dicetak langsung dari halaman ini</p>
        </div>
    </div>

    <script>
        // Disable print functionality
        document.addEventListener('keydown', function(e) {
            if ((e.ctrlKey || e.metaKey) && e.key === 'p') {
                e.preventDefault();
                alert('Printing is disabled for this invoice.');
            }
        });
        
        // Disable right-click
        document.addEventListener('contextmenu', function(e) {
            e.preventDefault();
        });

        // Helper untuk download gambar jika copy gagal
        function downloadImage(canvas, filename) {
            const link = document.createElement('a');
            link.download = filename;
            link.href = canvas.toDataURL('image/png');
            link.click();
        }

        // Fungsi untuk capture dan copy ke clipboard
        function copyInvoiceToClipboard() {
            const invoiceElement = document.querySelector('.invoice-container');
            const btn = document.querySelector('button[onclick="copyInvoiceToClipboard()"]');
            const originalText = btn.innerHTML;
            
            // Ubah status tombol
            btn.innerHTML = '⏳ Memproses...';
            btn.disabled = true;
            btn.style.opacity = '0.7';
            btn.style.cursor = 'wait';

            html2canvas(invoiceElement, {
                scale: 2, // Resolusi lebih tinggi (2x) agar hasil tajam
                backgroundColor: '#ffffff', // Pastikan background putih
                logging: false,
                useCORS: true // Mengizinkan gambar cross-origin jika ada
            }).then(canvas => {
                canvas.toBlob(blob => {
                    if (navigator.clipboard && navigator.clipboard.write) {
                        const item = new ClipboardItem({ 'image/png': blob });
                        navigator.clipboard.write([item]).then(() => {
                            alert('✅ Invoice berhasil disalin ke clipboard! Siap untuk di-paste (Ctrl+V) di WhatsApp.');
                        }).catch(err => {
                            console.error('Gagal menyalin:', err);
                            // Fallback ke download jika gagal copy
                            downloadImage(canvas, 'Invoice-{{ $booking->booking_code }}.png');
                            alert('⚠️ Gagal menyalin otomatis. Gambar telah diunduh ke perangkat Anda.');
                        }).finally(() => {
                            resetButton(btn, originalText);
                        });
                    } else {
                        // Fallback jika API tidak didukung (biasanya karena akses via HTTP/IP Address)
                        downloadImage(canvas, 'Invoice-{{ $booking->booking_code }}.png');
                        alert('⚠️ Browser membatasi fitur copy otomatis di koneksi ini. Gambar telah diunduh manual.');
                        resetButton(btn, originalText);
                    }
                });
            }).catch(err => {
                console.error('Error rendering:', err);
                alert('❌ Terjadi kesalahan saat memproses gambar.');
                resetButton(btn, originalText);
            });
        }

        function resetButton(btn, originalText) {
            btn.innerHTML = originalText;
            btn.disabled = false;
            btn.style.opacity = '1';
            btn.style.cursor = 'pointer';
        }
    </script>
</body>
</html>