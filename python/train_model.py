import pandas as pd
import numpy as np
from sklearn.preprocessing import PolynomialFeatures
from sklearn.linear_model import LinearRegression
import pickle

# Load data
data = pd.read_excel("datapenjualan.xlsx")

# Konversi tanggal ke fitur
data['tanggal'] = pd.to_datetime(data['tanggal'])
data['day'] = data['tanggal'].dt.day
data['month'] = data['tanggal'].dt.month
data['weekday'] = data['tanggal'].dt.weekday
data['is_weekend'] = data['weekday'].apply(lambda x: 1 if x>=5 else 0)

# Fitur dan target
X = data[['day','month','weekday','is_weekend']]
y = data['total_penjualan']

# Polynomial transform
poly = PolynomialFeatures(degree=2)
X_poly = poly.fit_transform(X)

# Train model
model = LinearRegression()
model.fit(X_poly, y)

# Simpan model & transform
with open("sales_prediction_model.pkl", "wb") as f:
    pickle.dump(model, f)
with open("poly_transform.pkl", "wb") as f:
    pickle.dump(poly, f)

print("Training selesai dan model disimpan")
