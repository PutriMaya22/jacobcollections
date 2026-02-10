import pandas as pd
import mysql.connector
from sklearn.preprocessing import PolynomialFeatures
from sklearn.linear_model import LinearRegression
from sklearn.metrics import r2_score
import json

# =========================
# 1. KONEKSI DATABASE
# =========================
conn = mysql.connector.connect(
    host="localhost",
    user="root",
    password="",
    database="nama_database"
)

query = """
SELECT tanggal, total_penjualan, total_pesanan, penjualan_perpesanan
FROM data_penjualan
ORDER BY tanggal ASC
"""

df = pd.read_sql(query, conn)

# =========================
# 2. BUAT hari_ke (TANPA DB)
# =========================
df['tanggal'] = pd.to_datetime(df['tanggal'])
tanggal_awal = df['tanggal'].min()

df['hari_ke'] = (df['tanggal'] - tanggal_awal).dt.days

# =========================
# 3. MODEL POLINOMIAL
# =========================
X = df[['hari_ke']]
y = df['total_penjualan']

poly = PolynomialFeatures(degree=2)
X_poly = poly.fit_transform(X)

model_penjualan = LinearRegression()
model_penjualan.fit(X_poly, y)

r2 = r2_score(y, model_penjualan.predict(X_poly))

# =========================
# 4. SIMPAN HASIL
# =========================
hasil = {
    "tanggal_awal": str(tanggal_awal.date()),
    "model_penjualan": {
        "degree": 2,
        "intercept": model_penjualan.intercept_,
        "coef": model_penjualan.coef_.tolist(),
        "r2": r2
    }
}

with open("model_penjualan.json", "w") as f:
    json.dump(hasil, f, indent=4)

print("✅ Training sukses")
print("R2:", r2)