<!DOCTYPE html>
<html>
<head>
    <title>Struk #{{ $transaction->invoice_id }}</title>
    <style>
        body {
            font-family: 'Courier New', Courier, monospace;
            font-size: 12px;
            width: 58mm;
            margin: 0;
            padding: 5px;
            background: white;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .line { border-bottom: 1px dashed #000; margin: 5px 0; }
        
        /* Print styles */
        @media print {
            .no-print { display: none !important; }
            @page { margin: 0; }
            body { margin: 0; padding: 5px; }
        }
        
        /* Iframe container styles */
        .iframe-container {
            width: 100%;
            height: 100vh;
            border: none;
        }
        
        /* Control buttons - moved to bottom */
        .receipt-controls {
            position: fixed;
            bottom: 10px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 1000;
            display: flex;
            gap: 10px;
            background: rgba(255,255,255,0.95);
            padding: 10px 15px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            border: 1px solid #e5e7eb;
        }
        
        .control-btn {
            padding: 8px 16px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: bold;
            font-size: 14px;
        }
        
        .print-btn {
            background: #3b82f6;
            color: white;
        }
        
        .close-btn {
            background: #ef4444;
            color: white;
        }
        
        .back-btn {
            background: #6b7280;
            color: white;
        }
    </style>
</head>
<body>
    <!-- Control buttons (only visible when not printing) -->
    <div class="receipt-controls no-print">
        <button class="control-btn print-btn" onclick="printReceipt()">🖨️ Cetak</button>
        <button class="control-btn close-btn" onclick="closeReceipt()">✕ Tutup</button>
        <button class="control-btn back-btn" onclick="goBack()">← Kembali</button>
    </div>
    
    <!-- Receipt content -->
    <div class="text-center">
        <strong>DTHREE STUDIO</strong><br>
        Jl. Rajawali Km. 3.5<br>
        Telp: 0852-5205-9520
    </div>
    
    <div class="line"></div>
    
    <div>
        No: {{ $transaction->invoice_id }}<br>
        Kasir: {{ $transaction->user->name }}<br>
        Tgl: {{ $transaction->created_at->format('d/m/Y H:i') }}<br>
        Plg: {{ $transaction->customer_name ?? 'Umum' }} ({{ ucfirst($transaction->customer_type) }})<br>
        HP: {{ $transaction->customer_phone ?? '-' }}
    </div>

    <div class="line"></div>

    <table style="width: 100%">
        @foreach($transaction->items as $item)
        <tr>
            <td colspan="2">
                @php
                    $productName = $item->product_name ?? ($item->product->name ?? 'Booking Studio');
                    $typeColor = $item->product->type_color ?? null;
                    if ($typeColor && trim($typeColor) !== '') {
                        echo $productName . ' - ' . $typeColor;
                    } else {
                        echo $productName;
                    }
                @endphp
            </td>
        </tr>
        <tr>
            <td>{{ $item->quantity }} x {{ number_format($item->price_at_transaction, 0, ',', '.') }}</td>
            <td class="text-right">{{ number_format($item->subtotal, 0, ',', '.') }}</td>
        </tr>
        @endforeach
    </table>

    <div class="line"></div>

    <div class="text-right">
        <strong>Total: Rp {{ number_format($transaction->total_amount, 0, ',', '.') }}</strong><br>
        Metode: {{ $transaction->payment_method }}
    </div>

    <div class="line"></div>
    
    <div class="text-center" style="margin-top: 20px;">
        <strong>TERIMA KASIH</strong><br>
        <small>Barang yang sudah dibeli tidak dapat ditukar/dikembalikan</small>
    </div>

    <script>
        function printReceipt() {
            window.print();
        }
        
        function closeReceipt() {
            if (window.opener) {
                window.close();
            } else if (window.parent !== window) {
                // We're in an iframe
                window.parent.postMessage('closeReceipt', '*');
            } else {
                window.history.back();
            }
        }
        
        function goBack() {
            window.history.back();
        }
        
        // Auto-print option
        <?php if(request()->get('autoprint') == '1'): ?>
        window.onload = function() {
            setTimeout(function() {
                window.print();
            }, 500);
        };
        <?php endif; ?>
        
        // Handle messages from parent window
        window.addEventListener('message', function(event) {
            if (event.data === 'printReceipt') {
                printReceipt();
            } else if (event.data === 'closeReceipt') {
                closeReceipt();
            }
        });
    </script>
</body>
</html>