<form action="/predict" method="POST">
    @csrf

    <label>Tanggal</label>
    <input type="date" name="tanggal" required>

    <label>Total Pesanan</label>
    <input type="number" name="total_orders" required>

    <label>Penjualan per Pesanan</label>
    <input type="number" name="avg_order_value" required>

    <button type="submit">Prediksi</button>
</form>
