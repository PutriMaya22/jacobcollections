import pandas as pd
import numpy as np
from sklearn.preprocessing import PolynomialFeatures
from sklearn.linear_model import LinearRegression
import joblib

# Load data historis
df = pd.read_excel("datapenjualan.xlsx")
df = df.dropna(subset=['total_sales', 'total_orders'])
df = df[df['total_orders'] > 0]

# Buat fitur numerik: day, month, weekday, is_weekend
X = np.array([[d.day, d.month, d.weekday(), 1 if d.weekday() >= 5 else 0]
              for d in pd.to_datetime(df['date'])])
y = df['total_sales'].values

# Polynomial transform
poly = PolynomialFeatures(degree=2)
X_poly = poly.fit_transform(X)

# Fit model
model = LinearRegression()
model.fit(X_poly, y)

# Simpan file baru kompatibel Python 3.13
joblib.dump(poly, "poly_transform.pkl")
joblib.dump(model, "sales_prediction_model.pkl")

print("Model & poly transform berhasil dibuat.")
