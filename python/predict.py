import sys
import numpy as np
import joblib
from math import ceil
import json
import os
import pandas as pd

# Ambil input tanggal dari Laravel
input_tanggal = sys.argv[1]

# Ubah ke fitur numerik
tanggal = pd.to_datetime(input_tanggal)
day = tanggal.day
month = tanggal.month
weekday = tanggal.weekday()
is_weekend = 1 if weekday >= 5 else 0
X_input = np.array([[day, month, weekday, is_weekend]])

# Path folder script ini
base_dir = os.path.dirname(__file__)
model_path = os.path.join(base_dir, "sales_prediction_model.pkl")
poly_path = os.path.join(base_dir, "poly_transform.pkl")
excel_path = os.path.join(base_dir, "datapenjualan.xlsx")  # data historis

# Load model & polynomial transform
model = joblib.load(model_path)
poly = joblib.load(poly_path)
X_poly = poly.transform(X_input)

# Prediksi total penjualan
prediksi_total_penjualan = float(model.predict(X_poly)[0])

# Baca data historis untuk rata-rata penjualan per pesanan
try:
    df = pd.read_excel(excel_path)
    df = df.dropna(subset=['total_sales', 'total_orders'])
    df = df[df['total_orders'] > 0]  # hindari nol
    penjualan_per_pesanan = df['total_sales'].sum() / df['total_orders'].sum()
except Exception as e:
    # fallback jika file tidak ada / error
    penjualan_per_pesanan = 50000

# Hitung total pesanan rekomendasi
total_pesanan = ceil(prediksi_total_penjualan / penjualan_per_pesanan)

# Output JSON
output = {
    "tanggal": input_tanggal,
    "total_penjualan": prediksi_total_penjualan,
    "total_pesanan": total_pesanan,
    "penjualan_per_pesanan": penjualan_per_pesanan
}

print(json.dumps(output))
print("DEBUG: prediksi_total_penjualan =", prediksi_total_penjualan, file=sys.stderr)
print("DEBUG: penjualan_per_pesanan =", penjualan_per_pesanan, file=sys.stderr)