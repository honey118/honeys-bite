@php($editing = $product->exists)

<div class="form-grid">
    <div class="form-field form-field-wide">
        <label for="name">Nama produk</label>
        <input id="name" name="name" type="text" value="{{ old('name', $product->name) }}" maxlength="255" required>
        @error('name')<p class="field-error">{{ $message }}</p>@enderror
    </div>

    <div class="form-field form-field-wide">
        <label for="description">Deskripsi</label>
        <textarea id="description" name="description" rows="4" maxlength="5000">{{ old('description', $product->description) }}</textarea>
        @error('description')<p class="field-error">{{ $message }}</p>@enderror
    </div>

    <div class="form-field">
        <label for="price">Harga (IDR)</label>
        <input id="price" name="price" type="number" min="0" max="99999999.99" step="0.01" value="{{ old('price', $product->price) }}" required>
        @error('price')<p class="field-error">{{ $message }}</p>@enderror
    </div>

    <div class="form-field">
        <label for="stock">Stok</label>
        <input id="stock" name="stock" type="number" min="0" step="1" value="{{ old('stock', $product->stock ?? 0) }}" required>
        @error('stock')<p class="field-error">{{ $message }}</p>@enderror
    </div>

    <div class="form-field">
        <label for="is_active">Status katalog</label>
        <select id="is_active" name="is_active" required>
            <option value="1" @selected((string) old('is_active', $product->is_active ?? true) === '1')>Aktif</option>
            <option value="0" @selected((string) old('is_active', $product->is_active ?? true) === '0')>Diarsipkan</option>
        </select>
        @error('is_active')<p class="field-error">{{ $message }}</p>@enderror
    </div>

    <div class="form-field">
        <label for="photo">Foto produk {{ $editing ? '(opsional, ganti foto saat ini)' : '(opsional)' }}</label>
        <input id="photo" name="photo" type="file" accept="image/jpeg,image/png,image/webp">
        <span class="field-hint">JPG, PNG, atau WebP. Maksimum 2 MB.</span>
        @error('photo')<p class="field-error">{{ $message }}</p>@enderror
    </div>
</div>

<button class="button button-primary" type="submit">{{ $editing ? 'Simpan perubahan' : 'Tambah produk' }}</button>
<a class="text-link" href="{{ route('admin.products.index') }}">Batal</a>
