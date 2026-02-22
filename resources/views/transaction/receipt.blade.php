<!DOCTYPE html>
<html>
<head>
    <title>Struk #{{ $transaction->invoice_id }}</title>
    <style>
        body {
            font-family: 'Arial', 'Helvetica', sans-serif;
            font-size: 14px;
            width: 58mm;
            margin: 0;
            padding: 8px;
            background: white;
            line-height: 1.4;
        }
        
        strong {
            font-size: 15px;
        }
        
        .text-center strong {
            font-size: 16px;
        }
        
        .text-right strong {
            font-size: 15px;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .line { border-bottom: 1px dashed #000; margin: 5px 0; }
        
        /* Sembunyikan elemen lain saat print */
        @media print {
            .no-print { display: none; }
            @page { margin: 0; }
            body { margin: 0; padding: 5px; }
        }
    </style>
</head>
<body>
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
        Plg: {{ $transaction->customer_name ?? 'Umum' }} ({{ ucfirst($transaction->customer_type) }})
    </div>

    <div class="line"></div>

    <table style="width: 100%">
        @foreach($transaction->items as $item)
        <tr>
            <td colspan="2">
                @php
                    $productName = $item->product->name;
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
        @if($transaction->status == 'pending')
            <strong>Subtotal: Rp {{ number_format($transaction->total_amount, 0, ',', '.') }}</strong><br>
            <strong>DP: Rp {{ number_format($transaction->down_payment, 0, ',', '.') }}</strong><br>
            <strong>Sisa: Rp {{ number_format($transaction->remaining_amount, 0, ',', '.') }}</strong><br>
            <strong>Status: PENDING</strong><br>
        @else
            <strong>Total: Rp {{ number_format($transaction->total_amount, 0, ',', '.') }}</strong><br>
            <strong>Status: LUNAS</strong><br>
        @endif
        Metode: {{ $transaction->payment_method }}
    </div>

    <br><br>
    <button class="no-print" onclick="window.print()">Cetak Struk</button>
</body>
</html>
