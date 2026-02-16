<!DOCTYPE html>
<html>
<head>
    <title>Edit Produk</title>
    <style>
        body { font-family: sans-serif; padding: 20px; background-color: #f4f4f4; }
        .container { max-width: 600px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input, select { width: 100%; padding: 8px; box-sizing: border-box; border: 1px solid #ddd; border-radius: 4px; }
        .btn { padding: 10px 20px; border: none; cursor: pointer; border-radius: 4px; color: white; }
        .btn-save { background-color: #007bff; }
        .btn-back { background-color: #6c757d; text-decoration: none; display: inline-block; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Edit Produk: {{ $product->name }}</h2>
        
        @if($errors->any())
            <div style="color: red; margin-bottom: 15px;">
                <ul>@foreach($errors->all() as $e) <li>{{ $e }}</li> @endforeach</ul>
            </div>
        @endif

        <form action="{{ route('products.update', $product->id) }}" method="POST">
            @csrf
            @method('PUT')
            
            <div class="form-group">
                <label>Nama Produk</label>
                <input type="text" name="name" value="{{ $product->name }}" required>
            </div>
            <div class="form-group">
                <label>Kategori</label>
                <select name="category_id" required>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ $product->category_id == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label>Warna / Tipe (Opsional)</label>
                <input type="text" name="type_color" value="{{ $product->type_color }}">
            </div>
            <div class="form-group">
                <label>Barcode (Opsional)</label>
                <input type="text" name="barcode" value="{{ $product->barcode }}">
            </div>
            <div class="form-group">
                <label>Stok</label>
                <input type="number" name="stock" value="{{ $product->stock }}" required>
            </div>
            
            <h3>Harga</h3>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                <div class="form-group"><label>Harga Beli (Modal)</label><input type="number" name="buy_price" value="{{ $product->buy_price }}" required></div>
                <div class="form-group"><label>Harga Umum</label><input type="number" name="price_general" value="{{ $product->price_general }}" required></div>
                <div class="form-group"><label>Harga Agen 1</label><input type="number" name="price_agent1" value="{{ $product->price_agent1 }}" required></div>
                <div class="form-group"><label>Harga Agen 2</label><input type="number" name="price_agent2" value="{{ $product->price_agent2 }}" required></div>
            </div>

            <br>
            <button type="submit" class="btn btn-save">Simpan Perubahan</button>
            <a href="{{ route('products.index') }}" class="btn btn-back">Batal</a>
        </form>
    </div>
</body>
</html>