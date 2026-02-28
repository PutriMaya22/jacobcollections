# =========================
# IMPORT LIBRARY
# =========================
from flask import Flask, request, jsonify
from sqlalchemy import create_engine
import pandas as pd
import numpy as np
from sklearn.preprocessing import PolynomialFeatures
from sklearn.linear_model import LinearRegression
from sklearn.metrics import mean_squared_error, mean_absolute_error, r2_score
from sklearn.model_selection import train_test_split
import traceback

app = Flask(__name__)

# =========================
# KONEKSI DATABASE
# =========================
engine = create_engine("mysql+mysqlconnector://root:@localhost/jacobcollections")

model = None
poly = None
evaluasi_cache = None


# =========================
# FUNCTION MAPE (AMAN)
# =========================
def mean_absolute_percentage_error(y_true, y_pred):
    y_true, y_pred = np.array(y_true), np.array(y_pred)
    mask = y_true != 0
    if len(y_true[mask]) == 0:
        return 0
    return np.mean(np.abs((y_true[mask] - y_pred[mask]) / y_true[mask])) * 100


# =========================
# LOAD & PREPARE DATA
# =========================
def load_and_prepare_data():
    query = """
        SELECT tanggal, total_pesanan, total_penjualan
        FROM data_penjualan
    """
    data = pd.read_sql(query, engine)

    if data.empty:
        raise Exception("Data penjualan kosong")

    data['tanggal'] = pd.to_datetime(data['tanggal'], errors='coerce')
    data['total_pesanan'] = pd.to_numeric(data['total_pesanan'], errors='coerce')
    data['total_penjualan'] = pd.to_numeric(data['total_penjualan'], errors='coerce')

    data = data.dropna()

    if len(data) < 5:
        raise Exception("Data minimal harus 5 baris untuk training")

    # Feature Engineering
    data['hari_dalam_minggu'] = data['tanggal'].dt.dayofweek
    data['weekend'] = data['hari_dalam_minggu'].apply(lambda x: 1 if x >= 5 else 0)
    data['bulan'] = data['tanggal'].dt.month
    data['tahun'] = data['tanggal'].dt.year

    X = data[['total_pesanan',
              'hari_dalam_minggu',
              'weekend',
              'bulan',
              'tahun']]

    y = data['total_penjualan']

    return X, y


# =========================
# ANALISIS KATEGORI PRIORITAS (BERDASARKAN STOK TERENDAH)
# =========================
def analisis_kategori_prioritas():
    try:
        query = """
            SELECT kategori, COALESCE(SUM(stok),0) as total_stok
            FROM data_barang
            GROUP BY kategori
            ORDER BY total_stok ASC
        """
        df = pd.read_sql(query, engine)

        if df.empty:
            print("DEBUG: data_barang kosong")
            return None

        print("DEBUG kategori & stok:")
        print(df)

        kategori_prioritas = df.iloc[0]['kategori']
        total_stok = df.iloc[0]['total_stok']

        return kategori_prioritas, int(total_stok)

    except Exception as e:
        print("ERROR analisis_kategori_prioritas:", e)
        return None


# =========================
# TRAIN MODEL
# =========================
def train_model():
    global model, poly, evaluasi_cache

    X, y = load_and_prepare_data()

    X_train, X_test, y_train, y_test = train_test_split(
        X, y, test_size=0.2, random_state=42
    )

    poly = PolynomialFeatures(degree=2)
    X_train_poly = poly.fit_transform(X_train)
    X_test_poly = poly.transform(X_test)

    model = LinearRegression()
    model.fit(X_train_poly, y_train)

    # =========================
    # EVALUASI
    # =========================
    y_train_pred = model.predict(X_train_poly)
    y_test_pred = model.predict(X_test_poly)

    evaluasi_cache = {
        "training": {
            "RMSE": float(np.sqrt(mean_squared_error(y_train, y_train_pred))),
            "MAE": float(mean_absolute_error(y_train, y_train_pred)),
            "MAPE": float(mean_absolute_percentage_error(y_train, y_train_pred)),
            "R2": float(r2_score(y_train, y_train_pred))
        },
        "testing": {
            "RMSE": float(np.sqrt(mean_squared_error(y_test, y_test_pred))),
            "MAE": float(mean_absolute_error(y_test, y_test_pred)),
            "MAPE": float(mean_absolute_percentage_error(y_test, y_test_pred)),
            "R2": float(r2_score(y_test, y_test_pred))
        }
    }

    # =========================
    # PRINT KE TERMINAL
    # =========================
    print("\n===== HASIL EVALUASI MODEL =====")
    print("\n--- DATA TRAINING ---")
    print(f"RMSE : {evaluasi_cache['training']['RMSE']:.2f}")
    print(f"MAE  : {evaluasi_cache['training']['MAE']:.2f}")
    print(f"MAPE : {evaluasi_cache['training']['MAPE']:.4f}")
    print(f"R2   : {evaluasi_cache['training']['R2']:.4f}")

    print("\n--- DATA TESTING ---")
    print(f"RMSE : {evaluasi_cache['testing']['RMSE']:.2f}")
    print(f"MAE  : {evaluasi_cache['testing']['MAE']:.2f}")
    print(f"MAPE : {evaluasi_cache['testing']['MAPE']:.4f}")
    print(f"R2   : {evaluasi_cache['testing']['R2']:.4f}")
    print("=================================\n")


# =========================
# ROUTE EVALUASI
# =========================
@app.route('/evaluasi', methods=['GET'])
def evaluasi():
    try:
        train_model()
        return jsonify(evaluasi_cache)
    except Exception as e:
        traceback.print_exc()
        return jsonify({"error": str(e)}), 400


# =========================
# ROUTE PREDIKSI
# =========================
@app.route('/predict', methods=['POST'])
def predict():
    try:
        global model, poly

        if model is None:
            train_model()

        data_input = request.get_json(force=True)

        total_pesanan = float(data_input['total_pesanan'])
        tanggal_input = pd.to_datetime(data_input['tanggal'], errors='coerce')

        if pd.isnull(tanggal_input):
            raise Exception("Format tanggal tidak valid. Gunakan YYYY-MM-DD")

        # Feature engineering
        hari = tanggal_input.dayofweek
        weekend = 1 if hari >= 5 else 0
        bulan = tanggal_input.month
        tahun = tanggal_input.year

        input_df = pd.DataFrame([{
            'total_pesanan': total_pesanan,
            'hari_dalam_minggu': hari,
            'weekend': weekend,
            'bulan': bulan,
            'tahun': tahun
        }])

        input_poly = poly.transform(input_df)
        prediksi = model.predict(input_poly)[0]

        # Rata-rata historis
        _, y_hist = load_and_prepare_data()
        rata_historis = y_hist.mean()

        kategori_rekomendasi = "Tidak ada rekomendasi kategori"

        # =========================
        # LOGIKA REKOMENDASI
        # =========================
        if prediksi > rata_historis * 1.05:
            status = "Di atas rata-rata historis"
            rekomendasi = "Disarankan menambah stok dan mempertahankan strategi pemasaran."

            hasil_kategori = analisis_kategori_prioritas()

            if hasil_kategori:
                kategori_rekomendasi = f"{hasil_kategori[0]} (Total Stok: {hasil_kategori[1]})"
            else:
                kategori_rekomendasi = "Kategori tidak ditemukan"

        elif prediksi < rata_historis:
            status = "Di bawah rata-rata historis"
            rekomendasi = "Disarankan melakukan promosi atau evaluasi strategi penjualan."
        else:
            status = "Sama dengan rata-rata historis"
            rekomendasi = "Penjualan stabil, strategi dapat dipertahankan."

        return jsonify({
    "total_pesanan_input": total_pesanan,
    "tanggal_input": str(tanggal_input.date()),
    "prediksi_total_penjualan": float(prediksi),
    "rata_rata_historis": float(rata_historis),
    "status": status,
    "rekomendasi": rekomendasi,
    "rekomendasi_kategori_stok": kategori_rekomendasi,
    "evaluasi_testing": evaluasi_cache["testing"] if evaluasi_cache else None
})

    except Exception as e:
        traceback.print_exc()
        return jsonify({'error': str(e)}), 400


# =========================
# START SERVER
# =========================
if __name__ == '__main__':
    print("Server starting...")
    app.run(debug=True)