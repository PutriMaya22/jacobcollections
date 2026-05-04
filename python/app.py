# =========================
# IMPORT LIBRARY
# =========================
from flask import Flask, request, jsonify
from flask_cors import CORS
from sqlalchemy import create_engine, text
import pandas as pd
import numpy as np
from sklearn.preprocessing import PolynomialFeatures
from sklearn.linear_model import LinearRegression
from sklearn.metrics import mean_squared_error, mean_absolute_error, r2_score
from sklearn.model_selection import train_test_split
import traceback
from datetime import datetime, timedelta
import warnings
import json
import os

warnings.filterwarnings('ignore')

# HILANGKAN WARNING PANDAS
pd.set_option('future.no_silent_downcasting', True)

app = Flask(__name__)
CORS(app)

# =========================
# KONEKSI DATABASE
# =========================
engine = create_engine("mysql+mysqlconnector://root:@localhost/jacobcollections")

# =========================
# GLOBAL VARIABLE
# =========================
model = None
poly = None
evaluasi_cache = None
rata_historis_cache = None
median_penjualan_cache = None
median_pesanan_cache = None
X_train = None
X_test = None
y_train = None
y_test = None

# Cache untuk data historis pesanan 1-5
historical_cache = {}

# Folder simpan visualisasi (opsional)
OUTPUT_FOLDER = "hasil_visualisasi"
os.makedirs(OUTPUT_FOLDER, exist_ok=True)


# =========================
# FUNGSI KONVERSI NUMERIK 
# =========================
def convert_to_numeric_indonesia(series):
    """
    Konversi angka format Indonesia ke numeric.
    Aman untuk data string maupun numeric.
    """
    if pd.api.types.is_numeric_dtype(series):
        return pd.to_numeric(series, errors='coerce')

    s = series.astype(str).str.strip()
    s = s.str.replace(r'[^0-9,.\-]', '', regex=True)
    s = s.str.replace('.', '', regex=False)
    s = s.str.replace(',', '.', regex=False)

    return pd.to_numeric(s, errors='coerce')


# =========================
# MAPE
# =========================
def mean_absolute_percentage_error(y_true, y_pred):
    y_true, y_pred = np.array(y_true), np.array(y_pred)
    mask = y_true != 0
    if len(y_true[mask]) == 0:
        return 0
    return np.mean(np.abs((y_true[mask] - y_pred[mask]) / y_true[mask])) * 100


# =========================
# DETEKSI OUTLIER IQR
# =========================
def detect_outlier_iqr(df, column):
    """Deteksi outlier menggunakan metode IQR"""
    Q1 = df[column].quantile(0.25)
    Q3 = df[column].quantile(0.75)
    IQR = Q3 - Q1

    lower_bound = Q1 - 1.5 * IQR
    upper_bound = Q3 + 1.5 * IQR

    outliers = df[(df[column] < lower_bound) | (df[column] > upper_bound)]
    
    print(f"\nOutlier kolom {column}:")
    print(f"   Q1          : {Q1:,.2f}")
    print(f"   Q3          : {Q3:,.2f}")
    print(f"   IQR         : {IQR:,.2f}")
    print(f"   Batas bawah : {lower_bound:,.2f}")
    print(f"   Batas atas  : {upper_bound:,.2f}")
    print(f"   Jumlah outlier : {len(outliers)}")

    return outliers, lower_bound, upper_bound


# =========================
# HANDLING OUTLIER DENGAN CAPPING
# =========================
def handle_outliers_capping(df, column):
    """Menangani outlier dengan capping (bukan menghapus)"""
    Q1 = df[column].quantile(0.25)
    Q3 = df[column].quantile(0.75)
    IQR = Q3 - Q1
    
    lower_bound = Q1 - 1.5 * IQR
    upper_bound = Q3 + 1.5 * IQR
    
    outlier_count = len(df[(df[column] < lower_bound) | (df[column] > upper_bound)])
    
    if outlier_count > 0:
        df[column] = df[column].clip(lower=lower_bound, upper=upper_bound)
        print(f"   → Capping {outlier_count} outlier pada kolom {column} ke batas [{lower_bound:,.0f}, {upper_bound:,.0f}]")
    
    return df


# =========================
# FUNGSI IMPUTASI NILAI 0 DENGAN MEDIAN
# =========================
def impute_zero_with_median(data, column='total_penjualan'):
    """
    Mengganti nilai 0 dengan MEDIAN dari data yang > 0
    """
    global median_penjualan_cache, median_pesanan_cache
    
    # Ambil data yang > 0 untuk hitung median
    data_valid = data[data[column] > 0]
    
    if len(data_valid) > 0:
        median_value = data_valid[column].median()
        zero_count = len(data[data[column] == 0])
        
        if zero_count > 0:
            print(f"Imputasi: {zero_count} nilai 0 pada kolom '{column}' diganti dengan median: {median_value:,.0f}")
            data.loc[data[column] == 0, column] = median_value
        
        # Simpan ke global cache
        if column == 'total_penjualan':
            median_penjualan_cache = median_value
        else:
            median_pesanan_cache = median_value
            
        return median_value
    else:
        # Jika tidak ada data positif, gunakan default
        default_value = 100000 if column == 'total_penjualan' else 5
        print(f"Tidak ada data positif pada kolom '{column}', gunakan default: {default_value}")
        data.loc[data[column] == 0, column] = default_value
        
        if column == 'total_penjualan':
            median_penjualan_cache = default_value
        else:
            median_pesanan_cache = default_value
            
        return default_value


# =========================
# LOAD DATA DENGAN CLEANING LENGKAP
# =========================
def load_and_prepare_data():
    global median_penjualan_cache, median_pesanan_cache, rata_historis_cache
    
    print("\n" + "="*60)
    print("LOAD & CLEANING DATA")
    print("="*60)
    
    query = "SELECT tanggal, total_pesanan, total_penjualan FROM data_penjualan"
    data = pd.read_sql(query, engine)

    if data.empty:
        raise Exception("Data penjualan kosong")
    
    print(f"Data awal: {len(data)} baris")
    
    # ========== DATA PREPARATION ==========
    data.columns = data.columns.str.strip()
    data = data[['tanggal', 'total_pesanan', 'total_penjualan']]
    data['tanggal'] = pd.to_datetime(data['tanggal'], errors='coerce')
    data['total_pesanan'] = convert_to_numeric_indonesia(data['total_pesanan'])
    data['total_penjualan'] = convert_to_numeric_indonesia(data['total_penjualan'])
    # ======================================
    
    # ========== DATA CLEANING ==========
    jumlah_sebelum = len(data)
    
    # 1. Hapus duplikat
    jumlah_duplikat = data.duplicated().sum()
    if jumlah_duplikat > 0:
        data = data.drop_duplicates()
        print(f"Menghapus {jumlah_duplikat} data duplikat")
    
    # 2. Hapus missing penting
    data = data.dropna(subset=['tanggal', 'total_pesanan', 'total_penjualan'])
    
    # 3. Imputasi nilai 0 dengan median
    impute_zero_with_median(data, 'total_penjualan')
    impute_zero_with_median(data, 'total_pesanan')
    
    # 4. Drop NaN lagi jika masih ada
    data = data.dropna()
    
    jumlah_sesudah = len(data)
    print(f"Jumlah data sebelum cleaning: {jumlah_sebelum}")
    print(f"Jumlah data sesudah cleaning: {jumlah_sesudah}")
    
    # 5. Deteksi dan handle outlier (CAPPING - SAMA dengan Jupyter)
    print("\n" + "="*40)
    print("DETEKSI & HANDLING OUTLIER (CAPPING)")
    print("="*40)
    
    detect_outlier_iqr(data, 'total_penjualan')
    detect_outlier_iqr(data, 'total_pesanan')
    
    data = handle_outliers_capping(data, 'total_penjualan')
    data = handle_outliers_capping(data, 'total_pesanan')
    # ======================================
    
    print(f"\nMedian total_penjualan: Rp {median_penjualan_cache:,.0f}")
    print(f"Median total_pesanan: {median_pesanan_cache:.0f}")
    
    # ========== DATA TRANSFORMATION ==========
    data = data.sort_values('tanggal')
    data['hari'] = data['tanggal'].dt.dayofweek
    data['weekend'] = (data['hari'] >= 5).astype(int)
    data['bulan'] = data['tanggal'].dt.month
    data['tahun'] = data['tanggal'].dt.year
    
    X = data[['total_pesanan', 'hari', 'weekend', 'bulan', 'tahun']]
    y = data['total_penjualan']
    
    rata_historis_cache = float(y.mean())
    print(f"\nRata-rata historis: Rp {rata_historis_cache:,.0f}")
    print(f"Jumlah data setelah cleaning: {len(data)} baris")
    # ========================================
    
    return X, y


# =========================
# SPLIT DATA
# =========================
def split_data(X, y):
    global X_train, X_test, y_train, y_test
    print("\n" + "="*60)
    print("DATA SPLITTING")
    print("="*60)
    
    X_train, X_test, y_train, y_test = train_test_split(
        X, y, train_size=0.8, random_state=42
    )
    
    print(f"Data Training: {len(X_train)} ({len(X_train)/len(X)*100:.0f}%)")
    print(f"Data Testing: {len(X_test)} ({len(X_test)/len(X)*100:.0f}%)")


# =========================
# TRAIN MODEL (POLYNOMIAL REGRESSION)
# =========================
def train_model():
    global model, poly, rata_historis_cache
    
    try:
        X, y = load_and_prepare_data()
        rata_historis_cache = float(y.mean())

        split_data(X, y)

        # ========== POLYNOMIAL DEGREE 2 ==========
        poly = PolynomialFeatures(degree=2, include_bias=False)
        X_train_poly = poly.fit_transform(X_train)

        model = LinearRegression()
        model.fit(X_train_poly, y_train)
        # ========================================
        
        print("\n" + "="*60)
        print("MODELING / TRAINING")
        print("="*60)
        print("Model berhasil di-train dengan Polynomial Degree 2!")
        
        feature_names = ['total_pesanan', 'hari', 'weekend', 'bulan', 'tahun']
        print("Koefisien model (5 fitur pertama):")
        for name, coef in zip(feature_names, model.coef_[:5]):
            print(f"   - {name}: {coef:,.2f}")
        print(f"Intercept: Rp {model.intercept_:,.0f}")
        
        return True
        
    except Exception as e:
        print(f"Error training model: {e}")
        raise e


# =========================
# TEST MODEL DAN SIMPAN KE CACHE
# =========================
def test_model():
    global evaluasi_cache
    
    if model is None:
        train_model()

    X_test_poly = poly.transform(X_test)
    y_pred = model.predict(X_test_poly)

    evaluasi_cache = {
        "RMSE": float(np.sqrt(mean_squared_error(y_test, y_pred))),
        "MAE": float(mean_absolute_error(y_test, y_pred)),
        "MAPE": float(mean_absolute_percentage_error(y_test, y_pred)),
        "R2": float(r2_score(y_test, y_pred)),
        "jumlah_data_train": len(X_train),
        "jumlah_data_test": len(X_test),
        "train_size": 0.8,
        "last_trained": datetime.now().strftime("%Y-%m-%d %H:%M:%S")
    }
    
    print("\n" + "="*60)
    print("EVALUATION")
    print("="*60)
    print(f"RMSE : Rp {evaluasi_cache['RMSE']:,.2f}")
    print(f"MAE  : Rp {evaluasi_cache['MAE']:,.2f}")
    print(f"MAPE : {evaluasi_cache['MAPE']:.2f}%")
    print(f"R²   : {evaluasi_cache['R2']:.4f}")
    print(f"Data train: {len(X_train)} ({len(X_train)/len(X_test+X_train)*100:.0f}%)")
    print(f"Data test: {len(X_test)} ({len(X_test)/len(X_test+X_train)*100:.0f}%)")
    
    # Simpan evaluasi ke file backup
    try:
        with open('evaluasi_cache_backup.json', 'w') as f:
            json.dump(evaluasi_cache, f, indent=2)
        print("Evaluasi cache disimpan ke file backup")
    except Exception as e:
        print(f"Gagal simpan backup: {e}")
    
    # Simpan ke database history
    save_evaluation_history()


# =========================
# SIMPAN HISTORY EVALUASI KE DATABASE
# =========================
def save_evaluation_history():
    """Simpan evaluasi model ke database untuk monitoring drift"""
    global evaluasi_cache
    if evaluasi_cache:
        try:
            # Buat tabel jika belum ada
            create_table_query = """
                CREATE TABLE IF NOT EXISTS evaluasi_model_log (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    tanggal TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    rmse FLOAT,
                    mae FLOAT,
                    mape FLOAT,
                    r_squared FLOAT,
                    jumlah_data_train INT,
                    jumlah_data_test INT,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                )
            """
            with engine.connect() as conn:
                conn.execute(text(create_table_query))
                conn.commit()
            
            # Insert data
            with engine.connect() as conn:
                query = text("""
                    INSERT INTO evaluasi_model_log (rmse, mae, mape, r_squared, jumlah_data_train, jumlah_data_test)
                    VALUES (:rmse, :mae, :mape, :r2, :train, :test)
                """)
                conn.execute(query, {
                    'rmse': evaluasi_cache['RMSE'],
                    'mae': evaluasi_cache['MAE'],
                    'mape': evaluasi_cache['MAPE'],
                    'r2': evaluasi_cache['R2'],
                    'train': evaluasi_cache.get('jumlah_data_train', 0),
                    'test': evaluasi_cache.get('jumlah_data_test', 0)
                })
                conn.commit()
                print("History evaluasi disimpan ke database")
        except Exception as e:
            print(f"Gagal simpan history evaluasi: {e}")


# =========================
# LOAD DATA HISTORIS UNTUK PESANAN 1-5 (CACHE)
# =========================
def load_historical_cache():
    """Load rata-rata penjualan untuk pesanan 1-5 dari database"""
    global historical_cache
    
    print("\n" + "="*60)
    print("LOAD HISTORICAL CACHE (Pesanan 1-5)")
    print("="*60)
    
    for pesanan in range(1, 6):
        query = f"""
            SELECT AVG(total_penjualan) as avg_penjualan, COUNT(*) as jumlah
            FROM data_penjualan
            WHERE total_pesanan = {pesanan} AND total_penjualan > 0
        """
        result = pd.read_sql(query, engine)
        
        if result['avg_penjualan'].iloc[0] and result['avg_penjualan'].iloc[0] > 0:
            historical_cache[pesanan] = {
                'rata_rata': float(result['avg_penjualan'].iloc[0]),
                'jumlah_data': int(result['jumlah'].iloc[0])
            }
            print(f"Pesanan {pesanan}: rata-rata Rp {historical_cache[pesanan]['rata_rata']:,.0f} (dari {historical_cache[pesanan]['jumlah_data']} data)")
        else:
            # Jika tidak ada data, gunakan estimasi dari pesanan 1
            if pesanan == 1:
                historical_cache[pesanan] = {'rata_rata': 92570, 'jumlah_data': 0}
            else:
                historical_cache[pesanan] = {'rata_rata': historical_cache[pesanan-1]['rata_rata'] * pesanan, 'jumlah_data': 0}
            print(f"Pesanan {pesanan}: tidak ada data, gunakan estimasi Rp {historical_cache[pesanan]['rata_rata']:,.0f}")


# =========================
# PREDIKSI HYBRID (RATA-RATA HISTORIS + POLYNOMIAL MODEL)
# =========================
def predict_hybrid(total_pesanan, tanggal):
    """
    Prediksi hybrid:
    - Pesanan 1-5: pakai rata-rata historis langsung
    - Pesanan >5: pakai model polynomial
    """
    global model, poly, historical_cache
    
    # Untuk pesanan kecil (1-5), gunakan data historis langsung
    if 1 <= total_pesanan <= 5:
        if total_pesanan in historical_cache:
            historis = historical_cache[total_pesanan]['rata_rata']
            print(f"Prediksi untuk {total_pesanan} pesanan menggunakan DATA HISTORIS: Rp {historis:,.0f}")
            return historis
    
    # Untuk pesanan besar (>5), gunakan model polynomial
    input_df = pd.DataFrame([{
        'total_pesanan': total_pesanan,
        'hari': tanggal.dayofweek,
        'weekend': 1 if tanggal.dayofweek >= 5 else 0,
        'bulan': tanggal.month,
        'tahun': tanggal.year
    }])
    
    X_input_poly = poly.transform(input_df)
    prediksi = float(model.predict(X_input_poly)[0])
    prediksi = max(prediksi, 0)
    
    print(f"Prediksi untuk {total_pesanan} pesanan menggunakan MODEL POLYNOMIAL: Rp {prediksi:,.0f}")
    return prediksi


# =========================
# SIMPAN PREDIKSI KE TABEL PREDIKSIS
# =========================
def save_prediction_to_db(tanggal_prediksi, total_pesanan, hasil_prediksi, rata_rata_historis, status_prediksi):
    """Menyimpan hasil prediksi ke tabel prediksis"""
    try:
        # Buat tabel prediksis jika belum ada
        create_prediksi_table = """
            CREATE TABLE IF NOT EXISTS prediksis (
                id INT AUTO_INCREMENT PRIMARY KEY,
                tanggal_prediksi DATE NOT NULL,
                total_pesanan INT NOT NULL,
                hasil_prediksi DECIMAL(15, 2) NOT NULL,
                rata_rata_historis DECIMAL(15, 2),
                status_prediksi VARCHAR(50),
                metode VARCHAR(50),
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_tanggal (tanggal_prediksi),
                INDEX idx_pesanan (total_pesanan)
            )
        """
        with engine.connect() as conn:
            conn.execute(text(create_prediksi_table))
            conn.commit()
        
        # Konversi tanggal
        if isinstance(tanggal_prediksi, str):
            tanggal_prediksi = pd.to_datetime(tanggal_prediksi).date()
        elif hasattr(tanggal_prediksi, 'date'):
            tanggal_prediksi = tanggal_prediksi.date()
        
        # Insert prediksi
        query = text("""
            INSERT INTO prediksis (tanggal_prediksi, total_pesanan, hasil_prediksi, rata_rata_historis, status_prediksi, metode)
            VALUES (:tanggal, :pesanan, :prediksi, :rata, :status, :metode)
        """)
        
        with engine.connect() as conn:
            conn.execute(query, {
                'tanggal': tanggal_prediksi,
                'pesanan': int(total_pesanan),
                'prediksi': float(hasil_prediksi),
                'rata': float(rata_rata_historis),
                'status': status_prediksi,
                'metode': 'Hybrid_Polynomial_ES'
            })
            conn.commit()
            
        print(f"Prediksi disimpan ke tabel prediksis: {tanggal_prediksi} - {total_pesanan} pesanan = Rp {hasil_prediksi:,.0f}")
        return True
        
    except Exception as e:
        print(f"Gagal menyimpan prediksi: {e}")
        return False


# =========================
# =========================
#  EXPONENTIAL SMOOTHING & TREN PRODUK
# =========================
# =========================

def exponential_smoothing_product(data, alpha=0.3):
    """
    Exponential smoothing untuk satu produk
    """
    if len(data) < 1:
        return 0
    
    result = [data[0]]
    for i in range(1, len(data)):
        forecast = alpha * data[i-1] + (1 - alpha) * result[-1]
        result.append(forecast)
    
    # Prediksi next period
    if len(data) >= 2:
        next_forecast = alpha * data[-1] + (1 - alpha) * result[-1]
    else:
        next_forecast = data[-1]
    
    return next_forecast

def calculate_product_trend(data_series):
    """
    Hitung tren dari data series (positif/negatif/stabil)
    data_series: list nilai penjualan per bulan
    return: tren, persentase_perubahan, arah
    """
    if len(data_series) < 2:
        return "stabil", 0, ""
    
    # Bandingkan 3 bulan terakhir vs 3 bulan sebelumnya
    n = len(data_series)
    
    if n >= 6:
        recent_avg = np.mean(data_series[-3:])
        old_avg = np.mean(data_series[:3])
    elif n >= 4:
        recent_avg = np.mean(data_series[-2:])
        old_avg = np.mean(data_series[:2])
    else:
        recent_avg = data_series[-1]
        old_avg = data_series[0]
    
    if old_avg == 0:
        if recent_avg > 0:
            return "naik", 100, ""
        else:
            return "stabil", 0, ""
    
    persentase = ((recent_avg - old_avg) / old_avg) * 100
    
    if persentase > 20:
        return "naik", persentase, ""
    elif persentase < -20:
        return "turun", abs(persentase), ""
    else:
        return "stabil", abs(persentase), ""

def get_product_historical_data():
    """
    Ambil data historis semua produk untuk analisis tren
    """
    query = """
        SELECT 
            kode_produk,
            nama,
            DATE_FORMAT(tanggal_penjualan, '%%Y-%%m') as bulan,
            total_penjualan,
            total_pesanan,
            tanggal_penjualan
        FROM data_barang
        WHERE tanggal_penjualan IS NOT NULL
            AND nama IS NOT NULL
        ORDER BY kode_produk, tanggal_penjualan ASC
    """
    try:
        df = pd.read_sql(query, engine)
        return df
    except Exception as e:
        print(f"Error get_product_historical_data: {e}")
        return pd.DataFrame()

def get_most_popular_products(limit=10):
    """Produk paling diminati berdasarkan popularity score"""
    try:
        query = """
            SELECT 
                id,
                kode_produk,
                nama,
                kategori,
                stok,
                total_penjualan,
                total_dilihat,
                total_klik,
                total_pesanan,
                persentase_klik,
                tingkat_konversi,
                penjualan_per_pesanan
            FROM data_barang
            WHERE nama IS NOT NULL
        """
        df = pd.read_sql(query, engine)

        if df.empty:
            return []

        numeric_cols = ['stok', 'total_penjualan', 'total_dilihat', 'total_klik', 
                        'total_pesanan', 'persentase_klik', 'tingkat_konversi', 
                        'penjualan_per_pesanan']
        
        for col in numeric_cols:
            if col in df.columns:
                df[col] = pd.to_numeric(df[col], errors='coerce').fillna(0)

        max_penjualan = df['total_penjualan'].max() or 1
        max_ctr = df['persentase_klik'].max() or 1
        max_cr = df['tingkat_konversi'].max() or 1

        for idx, row in df.iterrows():
            if row['total_dilihat'] > 0:
                ctr = (row['total_klik'] / row['total_dilihat']) * 100
            else:
                ctr = 0
                
            if row['total_klik'] > 0 and row['total_pesanan'] > 0:
                cr_raw = (row['total_pesanan'] / row['total_klik']) * 100
                cr = min(100, cr_raw)
            elif row['total_pesanan'] > 0 and row['total_klik'] == 0:
                cr = 100
            else:
                cr = 0

            if row['total_dilihat'] > 0 and row['total_klik'] > 0:
                ctr_raw = (row['total_klik'] / row['total_dilihat']) * 100
                ctr = min(100, ctr_raw)
            elif row['total_klik'] > 0 and row['total_dilihat'] == 0:
                ctr = 100
            else:
                ctr = 0
            
            penjualan_score = (row['total_penjualan'] / max_penjualan) * 40
            ctr_score = (ctr / max_ctr) * 30 if max_ctr > 0 else 0
            cr_score = (cr / max_cr) * 30 if max_cr > 0 else 0
            
            popularity_score = penjualan_score + ctr_score + cr_score
            
            if row['stok'] <= 0 and cr > 5:
                popularity_score += 15
                stok_status = "HABIS STOK (Diminati)"
            elif row['stok'] <= 0:
                stok_status = "HABIS STOK"
            elif row['stok'] < 10:
                stok_status = "STOK MENIPIS"
            else:
                stok_status = "STOK AMAN"
            
            df.loc[idx, 'ctr'] = ctr
            df.loc[idx, 'cr'] = cr
            df.loc[idx, 'popularity_score'] = popularity_score
            df.loc[idx, 'stok_status'] = stok_status

        df_sorted = df.sort_values('popularity_score', ascending=False)
        
        hasil = []
        for _, row in df_sorted.head(limit).iterrows():
            if row['stok'] <= 0 and row['cr'] > 10:
                rekomendasi = "PRIORITAS RESTOCK - Produk sangat diminati!"
                aksi_prioritas = "RESTOCK DARURAT 150-200 pcs"
            elif row['stok'] <= 0 and row['cr'] > 5:
                rekomendasi = "Stok Habis - Produk cukup diminati"
                aksi_prioritas = "Restock 75-100 pcs"
            elif row['total_pesanan'] > 100:
                rekomendasi = "Best Seller - Pertahankan stok!"
                aksi_prioritas = "Fokus pada promosi dan stok"
            elif row['ctr'] > 10 and row['cr'] > 5:
                rekomendasi = "Potensi Besar - Tingkatkan stok"
                aksi_prioritas = "Tambah stok 2x lipat"
            elif row['ctr'] > 10 and row['cr'] < 3:
                rekomendasi = "Banyak dilihat - Perbaiki konversi"
                aksi_prioritas = "Optimasi foto & deskripsi"
            elif row['stok'] < 10 and row['cr'] > 3:
                rekomendasi = "Stok Menipis (Produk Laris)"
                aksi_prioritas = "Restock 100-150 pcs"
            elif row['stok'] < 10:
                rekomendasi = "Stok Menipis"
                aksi_prioritas = "Restock 50-100 pcs"
            else:
                rekomendasi = "Normal - Monitor berkala"
                aksi_prioritas = "Evaluasi setiap bulan"
            
            hasil.append({
                "nama": row['nama'],
                "kode_produk": str(row['kode_produk']) if row['kode_produk'] else '-',
                "kategori": row['kategori'],
                "stok": int(row['stok']),
                "stok_status": stok_status,
                "total_penjualan": float(row['total_penjualan']),
                "ctr": round(row['ctr'], 2),
                "cr": round(row['cr'], 2),
                "popularity_score": round(popularity_score, 2),
                "rekomendasi": rekomendasi,
                "aksi_prioritas": aksi_prioritas
            })
        
        return hasil
        
    except Exception as e:
        print(f"Error get_most_popular_products: {e}")
        traceback.print_exc()
        return []


def get_product_distribution_with_trend(prediksi, total_pesanan_input, rata_historis, alpha=0.3):
    """
    ANALISIS PRODUK DENGAN EXPONENTIAL SMOOTHING & TREN
    """
    try:
        print("="*60)
        print("ANALISIS PRODUK - EXPONENTIAL SMOOTHING & TREN")
        print("="*60)
        
        # Ambil data historis
        df_historis = get_product_historical_data()
        
        # Ambil data produk terbaru
        query = """
            SELECT 
                id,
                kode_produk,
                nama,
                kategori,
                stok,
                total_penjualan,
                total_dilihat,
                total_klik,
                total_pesanan,
                persentase_klik,
                tingkat_konversi,
                penjualan_per_pesanan
            FROM data_barang
            WHERE nama IS NOT NULL
            GROUP BY kode_produk
        """
        df_produk = pd.read_sql(query, engine)
        
        if df_produk.empty:
            return []
        
        # Konversi numeric
        numeric_cols = ['stok', 'total_penjualan', 'total_dilihat', 'total_klik', 
                        'total_pesanan', 'persentase_klik', 'tingkat_konversi', 
                        'penjualan_per_pesanan']
        
        for col in numeric_cols:
            if col in df_produk.columns:
                df_produk[col] = pd.to_numeric(df_produk[col], errors='coerce').fillna(0)
        
        hasil = []
        total_forecast_pesanan = 0
        produk_forecast = {}
        
        print("\nANALISIS TREN PER PRODUK (EXPONENTIAL SMOOTHING):")
        print("-" * 60)
        
        # Hitung forecast per produk
        for _, row in df_produk.iterrows():
            kode = row['kode_produk']
            nama = row['nama']
            
            # Filter data historis produk ini
            if not df_historis.empty:
                hist_data = df_historis[df_historis['kode_produk'] == kode].sort_values('bulan')
            else:
                hist_data = pd.DataFrame()
            
            if len(hist_data) >= 2:
                # Ada data historis → pakai Exponential Smoothing
                penjualan_list = hist_data['total_penjualan'].tolist()
                pesanan_list = hist_data['total_pesanan'].tolist()
                
                forecast_penjualan = exponential_smoothing_product(penjualan_list, alpha)
                forecast_pesanan = exponential_smoothing_product(pesanan_list, alpha)
                
                # Analisis tren
                tren, persen, ikon = calculate_product_trend(penjualan_list)
                
                print(f"   {ikon} {nama[:35]:<35} | Tren: {tren} ({persen:.0f}%) | Forecast: {forecast_pesanan:.0f} pcs")
                
            else:
                # Tidak ada data historis → pakai nilai terakhir
                forecast_pesanan = row['total_pesanan'] if row['total_pesanan'] > 0 else 1
                forecast_penjualan = row['total_penjualan']
                tren = "baru"
                persen = 0
                ikon = "🆕"
                
                print(f"   {ikon} {nama[:35]:<35} | Produk Baru | Forecast: {forecast_pesanan:.0f} pcs")
            
            produk_forecast[kode] = {
                'forecast_pesanan': max(1, forecast_pesanan),
                'forecast_penjualan': forecast_penjualan,
                'tren': tren,
                'persen_tren': persen,
                'ikon': ikon,
                'data_historis': len(hist_data)
            }
            
            total_forecast_pesanan += max(1, forecast_pesanan)
        
        print("-" * 60)
        print(f"TOTAL FORECAST PESANAN (ES): {total_forecast_pesanan:.0f} pcs")
        print("=" * 60)
        
        # Proses setiap produk untuk rekomendasi restock
        for _, row in df_produk.iterrows():
            kode = row['kode_produk']
            stok = int(row['stok']) if row['stok'] > 0 else 0
            
            f_data = produk_forecast.get(kode, {
                'forecast_pesanan': row['total_pesanan'] or 1,
                'tren': 'stabil',
                'persen_tren': 0,
                'ikon': ''
            })
            
            forecast_pesanan = f_data['forecast_pesanan']
            tren = f_data['tren']
            persen_tren = f_data['persen_tren']
            ikon_tren = f_data['ikon']
            
            # Hitung proporsi dinamis berdasarkan ES
            if total_forecast_pesanan > 0:
                proporsi = forecast_pesanan / total_forecast_pesanan
            else:
                proporsi = 1 / len(df_produk) if len(df_produk) > 0 else 0
            
            estimasi_laku = max(1, int(round(total_pesanan_input * proporsi)))
            
            # Penyesuaian berdasarkan tren
            if tren == "naik":
                faktor_penyesuaian = 1 + min(0.5, persen_tren / 100)
                estimasi_laku = int(estimasi_laku * faktor_penyesuaian)
                insight_tren = f"Tren Naik {persen_tren:.0f}% → tambah stok"
            elif tren == "turun":
                faktor_penyesuaian = max(0.7, 1 - (persen_tren / 100))
                estimasi_laku = int(estimasi_laku * faktor_penyesuaian)
                insight_tren = f"Tren Turun {persen_tren:.0f}% → kurangi stok"
            else:
                insight_tren = "Tren Stabil"
            
            sisa_stok = stok - estimasi_laku
            
            # Hitung CTR & CR
            if row['total_dilihat'] > 0:
                ctr = (row['total_klik'] / row['total_dilihat']) * 100
            else:
                ctr = 0
                
            if row['total_klik'] > 0:
                cr_raw = (row['total_pesanan'] / row['total_klik']) * 100
                cr = min(100, cr_raw)
            elif row['total_pesanan'] > 0 and row['total_klik'] == 0:
                cr = 100
            else:
                cr = 0
            
            # Buffer berdasarkan CR + TREN
            if cr > 10:
                buffer = 50
                buffer_desc = "CR > 10% (Super Laris)"
            elif cr > 5:
                buffer = 30
                buffer_desc = "CR > 5% (Laris)"
            elif cr > 3:
                buffer = 20
                buffer_desc = "CR > 3% (Potensial)"
            else:
                buffer = 10
                buffer_desc = "CR Rendah"
            
            if tren == "naik" and persen_tren > 30:
                buffer = int(buffer * 1.5)
                buffer_desc += " + Bonus tren naik"
            
            # Analisis restock - PERBAIKAN UTAMA
            if stok <= 0 and cr > 10:
                priority = 1
                status_restock = "KRITIS - Stok Habis (Produk Super Laris!)"
                urgensi = "Sangat Tinggi"
                rekomendasi_restock = max(25, estimasi_laku + buffer)
                aksi = f"RESTOCK DARURAT {rekomendasi_restock} pcs"
            elif stok <= 0:
                priority = 1
                status_restock = "KRITIS - Stok Habis"
                urgensi = "Tinggi"
                rekomendasi_restock = max(25, estimasi_laku + buffer)
                aksi = f"Restock segera {rekomendasi_restock} pcs"
            elif sisa_stok < 0:
                priority = 1
                status_restock = "Stok Tidak Cukup"
                urgensi = "Tinggi"
                rekomendasi_restock = max(25, abs(sisa_stok) + buffer)
                aksi = f"Restock {rekomendasi_restock} pcs"
            elif stok < 10 and (cr > 5 or tren == "naik"):
                priority = 2
                status_restock = " Stok Menipis (Produk Laris/Tren Naik)"
                urgensi = "Tinggi"
                rekomendasi_restock = max(20, estimasi_laku + buffer)
                aksi = f"Restock prioritas {rekomendasi_restock} pcs"
            elif stok < 20:
                priority = 3
                status_restock = "Stok Menipis"
                urgensi = "Sedang"
                rekomendasi_restock = max(15, estimasi_laku)
                aksi = f"Persiapkan restock {rekomendasi_restock} pcs"
            elif tren == "turun" and stok > estimasi_laku * 2:
                priority = 4
                status_restock = "Kelebihan Stok (Tren Turun)"
                urgensi = "Perhatian"
                rekomendasi_restock = 0
                aksi = "Diskon untuk mengurangi stok"
            else:
                priority = 4
                status_restock = "Stok Aman"
                urgensi = "Rendah"
                rekomendasi_restock = 0
                aksi = "Monitor stok"
            
            # Insight produk
            if ctr > 5 and cr > 5:
                insight = "Super Laris + CR Tinggi"
                kategori = "super_laris"
            elif ctr > 3 and cr > 3:
                insight = " Produk Laris"
                kategori = "laris"
            elif tren == "naik" and cr > 3:
                insight = f"Tren Naik {persen_tren:.0f}% - Potensi Besar!"
                kategori = "potensial"
            elif ctr > 5 and cr < 2:
                insight = "Banyak dilihat tapi jarang dibeli"
                kategori = "perlu_optimasi"
            elif tren == "turun":
                insight = f"Tren Turun {persen_tren:.0f}% - Evaluasi"
                kategori = "penurunan"
            else:
                insight = "Performa normal"
                kategori = "normal"
            
            hasil.append({
                "nama": row['nama'],
                "kode_produk": str(row['kode_produk']),
                "CTR_%": round(ctr, 2),
                "CR_%": round(cr, 2),
                "stok": stok,
                "estimasi_laku": estimasi_laku,
                "sisa_stok_setelah_prediksi": sisa_stok,
                "proporsi_kontribusi": round(proporsi * 100, 2),
                "tren_produk": {
                    "arah": tren,
                    "persen": round(persen_tren, 1),
                    "ikon": ikon_tren,
                    "insight": insight_tren
                },
                "forecast_es": {
                    "forecast_pesanan": round(forecast_pesanan, 0),
                    "data_historis": f_data.get('data_historis', 0),
                    "alpha": alpha
                },
                "insight": insight,
                "aksi": aksi,
                "kategori": kategori,
                "restock_prediction": {
                    "status": status_restock,
                    "priority": priority,
                    "urgensi": urgensi,
                    "rekomendasi_restock": rekomendasi_restock,
                    "conversion_rate": round(cr, 2),
                    "buffer_used": buffer,
                    "buffer_reason": buffer_desc
                }
            })
        
        hasil_sorted = sorted(hasil, key=lambda x: (x['restock_prediction']['priority'], -x['CR_%']))
        
        print(f"\n✅ Analisis ES selesai: {len(hasil_sorted)} produk diproses")
        print(f"   - Tren Naik: {len([p for p in hasil if p['tren_produk']['arah'] == 'naik'])} produk")
        print(f"   - Tren Turun: {len([p for p in hasil if p['tren_produk']['arah'] == 'turun'])} produk")
        print(f"   - Prioritas Restock: {len([p for p in hasil if p['restock_prediction']['priority'] <= 2])} produk")
        
        return hasil_sorted[:15]
        
    except Exception as e:
        print(f"❌ Error ES Analysis: {e}")
        traceback.print_exc()
        return []


def get_strategy_recommendations(prediksi, rata_historis, total_pesanan_input, produk_populer, produk_kritis):
    """Rekomendasi strategi berdasarkan prediksi"""
    target = prediksi
    selisih = prediksi - rata_historis
    persen_selisih = (selisih / rata_historis) * 100 if rata_historis > 0 else 0
    
    rekomendasi = {
        "target_penjualan": target,
        "rata_historis": rata_historis,
        "selisih": selisih,
        "persen_selisih": persen_selisih,
        "strategi_utama": [],
        "strategi_produk": [],
        "aksi_restock": []
    }
    
    if selisih > 0:
        rekomendasi["strategi_utama"].append({
            "judul": " Target Meningkat",
            "deskripsi": f"Target penjualan naik {persen_selisih:.1f}% dari rata-rata",
            "aksi": "Fokus pada produk dengan tren naik, tingkatkan stok"
        })
    else:
        rekomendasi["strategi_utama"].append({
            "judul": " Target Menurun",
            "deskripsi": f"Target turun {abs(persen_selisih):.1f}% dari rata-rata",
            "aksi": "Evaluasi produk dengan tren turun, berikan promo"
        })
    
    if produk_populer:
        top_produk = produk_populer[0]
        rekomendasi["strategi_produk"].append({
            "judul": "🏆 Produk Unggulan",
            "produk": top_produk['nama'],
            "popularity_score": top_produk['popularity_score'],
            "rekomendasi": top_produk['rekomendasi'],
            "aksi": top_produk['aksi_prioritas']
        })
    
    if produk_kritis:
        for kritis in produk_kritis[:5]:
            restock_info = kritis.get('restock_prediction', {})
            rekomendasi["aksi_restock"].append({
                "produk": kritis['nama'],
                "stok_saat_ini": kritis['stok'],
                "cr": restock_info.get('conversion_rate', 0),
                "estimasi_laku": kritis.get('estimasi_laku', 0),
                "rekomendasi_restock": restock_info.get('rekomendasi_restock', 0),
                "urgensi": restock_info.get('urgensi', 'Normal'),
                "buffer_reason": restock_info.get('buffer_reason', 'Standard')
            })
    
    return rekomendasi


# =========================
# ROUTE PREDICT (UTAMA) - Exponential Smoothing & Tren Produk
# =========================
@app.route('/predict', methods=['POST', 'OPTIONS'])
def predict():
    if request.method == 'OPTIONS':
        return jsonify({'status': 'ok'})
    
    try:
        global model, poly, evaluasi_cache, rata_historis_cache, median_pesanan_cache

        if model is None:
            train_model()
            test_model()
            load_historical_cache()

        data_input = request.get_json(force=True)
        
        if 'total_pesanan' not in data_input or 'tanggal' not in data_input:
            return jsonify({"error": "Parameter total_pesanan dan tanggal diperlukan"}), 400

        total_pesanan = float(data_input['total_pesanan'])
        alpha = float(data_input.get('alpha', 0.3))
        
        if total_pesanan <= 0 and median_pesanan_cache is not None:
            print(f"Input pesanan {total_pesanan}, diganti dengan median: {median_pesanan_cache:.0f}")
            total_pesanan = median_pesanan_cache
        
        tanggal = pd.to_datetime(data_input['tanggal'])

        # ========== HYBRID PREDICTION (Polynomial + Historis) ==========
        prediksi = predict_hybrid(total_pesanan, tanggal)
        # ================================================================

        if prediksi > rata_historis_cache:
            status = "Meningkat"
            strategi_umum = "Fokus pada produk dengan tren naik, tingkatkan stok"
        elif prediksi < rata_historis_cache:
            status = "Menurun"
            strategi_umum = "Evaluasi produk dengan tren turun, berikan promo"
        else:
            status = "Stabil"
            strategi_umum = "Pertahankan strategi, monitor tren produk"

        save_prediction_to_db(tanggal, total_pesanan, prediksi, rata_historis_cache, status)

        # ========== ANALISIS PRODUK DENGAN EXPONENTIAL SMOOTHING & TREN ==========
        rekomendasi_produk = get_product_distribution_with_trend(
            prediksi, total_pesanan, rata_historis_cache, alpha
        )
        # ==========================================================================
        
        produk_paling_diminati = get_most_popular_products(5)
        
        produk_kritis = [p for p in rekomendasi_produk if p['restock_prediction']['priority'] == 1]
        produk_terlaris = [p for p in rekomendasi_produk if p['kategori'] in ['super_laris', 'laris', 'potensial']]
        
        strategi_rekomendasi = get_strategy_recommendations(
            prediksi, rata_historis_cache, total_pesanan, produk_paling_diminati, produk_kritis
        )

        response = {
            "prediksi_total_penjualan": prediksi,
            "prediksi_format": f"Rp {prediksi:,.0f}",
            "rata_rata_historis": rata_historis_cache,
            "status": status,
            "strategi_umum": strategi_umum,
            "metode_prediksi": "Hybrid (Polynomial Degree 2 + Historis 1-5)",
            "metode_analisis_produk": "Exponential Smoothing + Tren Produk (Dinamis)",
            "alpha_used": alpha,
            "rekomendasi_produk": rekomendasi_produk,
            "produk_paling_diminati": produk_paling_diminati,
            "produk_terlaris": produk_terlaris[:5],
            "strategi_rekomendasi": strategi_rekomendasi,
            "evaluasi": evaluasi_cache,
            "ringkasan_tren": {
                "total_produk": len(rekomendasi_produk),
                "tren_naik": len([p for p in rekomendasi_produk if p.get('tren_produk', {}).get('arah') == 'naik']),
                "tren_turun": len([p for p in rekomendasi_produk if p.get('tren_produk', {}).get('arah') == 'turun']),
                "tren_stabil": len([p for p in rekomendasi_produk if p.get('tren_produk', {}).get('arah') in ['stabil', 'baru']]),
                "prioritas_restock": len(produk_kritis)
            }
        }
        
        print(f"\n✅ PREDIKSI: {total_pesanan} pesanan = Rp {prediksi:,.0f}")
        print(f"✅ Metode: Exponential Smoothing + Tren (Alpha={alpha})")
        
        return jsonify(response)

    except Exception as e:
        traceback.print_exc()
        return jsonify({"error": str(e)}), 500


# =========================
# ROUTE HEALTH CHECK
# =========================
@app.route('/health', methods=['GET'])
def health():
    return jsonify({
        "status": "healthy", 
        "model_loaded": model is not None,
        "evaluasi": evaluasi_cache,
        "metode_prediksi": "Hybrid (Polynomial Degree 2 + Historis 1-5)",
        "metode_analisis_produk": "Exponential Smoothing + Tren Produk",
        "metode_outlier": "Capping"
    })


# =========================
# RUN
# =========================
if __name__ == '__main__':
    try:
        print("=" * 70)
        print("🚀 STARTING FLASK API SERVER")
        print("=" * 70)
        print("📊 METODE:")
        print("   1. Prediksi Total: Hybrid (Polynomial Degree 2 + Historis 1-5)")
        print("   2. Analisis Produk: Exponential Smoothing + Tren Produk")
        print("   3. Outlier Handling: Capping")
        print("=" * 70)
        
        train_model()
        test_model()
        load_historical_cache()
        
        print("\n" + "=" * 70)
        print("✅ SERVER READY!")
        print("=" * 70)
        if evaluasi_cache:
            print(f"📈 Evaluasi Model:")
            print(f"   - MAPE : {evaluasi_cache['MAPE']:.2f}%")
            print(f"   - R²   : {evaluasi_cache['R2']:.4f}")
            print(f"   - RMSE : Rp {evaluasi_cache['RMSE']:,.0f}")
        print(f"\n📊 Statistik:")
        print(f"   - Rata-rata historis: Rp {rata_historis_cache:,.0f}")
        print(f"   - Median pesanan: {median_pesanan_cache:.0f}")
        
        print("\n🌐 SERVER RUNNING ON http://localhost:5000")
        print("=" * 70)
        print("Endpoint:")
        print("   POST /predict - Prediksi dengan Exponential Smoothing & Tren")
        print("   GET  /health  - Cek status server")
        print("=" * 70)
        
    except Exception as e:
        print(f"\n❌ ERROR: {e}")
        traceback.print_exc()
    
    app.run(debug=True, host='0.0.0.0', port=5000)