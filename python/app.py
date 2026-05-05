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
from datetime import datetime
import warnings
import json
import os

warnings.filterwarnings('ignore')

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
historical_cache = {}


# =========================
# FUNGSI KONVERSI NUMERIK 
# =========================
def convert_to_numeric_indonesia(series):
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


def save_evaluation_history():
    global evaluasi_cache
    if evaluasi_cache:
        try:
            create_table_query = """
                CREATE TABLE IF NOT EXISTS evaluasi_model_log (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    tanggal TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    rmse FLOAT, mae FLOAT, mape FLOAT, r_squared FLOAT,
                    jumlah_data_train INT, jumlah_data_test INT
                )
            """
            with engine.connect() as conn:
                conn.execute(text(create_table_query))
                conn.commit()
            with engine.connect() as conn:
                query = text("""
                    INSERT INTO evaluasi_model_log (rmse, mae, mape, r_squared, jumlah_data_train, jumlah_data_test)
                    VALUES (:rmse, :mae, :mape, :r2, :train, :test)
                """)
                conn.execute(query, {
                    'rmse': evaluasi_cache['RMSE'], 'mae': evaluasi_cache['MAE'],
                    'mape': evaluasi_cache['MAPE'], 'r2': evaluasi_cache['R2'],
                    'train': evaluasi_cache.get('jumlah_data_train', 0),
                    'test': evaluasi_cache.get('jumlah_data_test', 0)
                })
                conn.commit()
                print("History evaluasi disimpan ke database")
        except Exception as e:
            print(f"Gagal simpan history evaluasi: {e}")


def load_historical_cache():
    global historical_cache
    print("\n" + "="*60)
    print("LOAD HISTORICAL CACHE (Pesanan 1-5)")
    print("="*60)
    for pesanan in range(1, 6):
        query = f"""
            SELECT AVG(total_penjualan) as avg_penjualan, COUNT(*) as jumlah
            FROM data_penjualan WHERE total_pesanan = {pesanan} AND total_penjualan > 0
        """
        result = pd.read_sql(query, engine)
        if result['avg_penjualan'].iloc[0] and result['avg_penjualan'].iloc[0] > 0:
            historical_cache[pesanan] = {
                'rata_rata': float(result['avg_penjualan'].iloc[0]),
                'jumlah_data': int(result['jumlah'].iloc[0])
            }
            print(f"Pesanan {pesanan}: Rp {historical_cache[pesanan]['rata_rata']:,.0f}")
        else:
            if pesanan == 1:
                historical_cache[pesanan] = {'rata_rata': 92570, 'jumlah_data': 0}
            else:
                historical_cache[pesanan] = {'rata_rata': historical_cache[pesanan-1]['rata_rata'] * pesanan, 'jumlah_data': 0}
            print(f"Pesanan {pesanan}: estimasi Rp {historical_cache[pesanan]['rata_rata']:,.0f}")

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
# EXPONENTIAL SMOOTHING & TREN PRODUK 
# =========================

def exponential_smoothing_product(data, alpha=0.3):
    if len(data) < 1:
        return 0
    result = [data[0]]
    for i in range(1, len(data)):
        forecast = alpha * data[i-1] + (1 - alpha) * result[-1]
        result.append(forecast)
    if len(data) >= 2:
        next_forecast = alpha * data[-1] + (1 - alpha) * result[-1]
    else:
        next_forecast = data[-1]
    return next_forecast


def calculate_product_trend(data_series):
    if len(data_series) < 2:
        return "stabil", 0
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
        return ("naik", 100) if recent_avg > 0 else ("stabil", 0)
    persentase = ((recent_avg - old_avg) / old_avg) * 100
    if persentase > 20:
        return "naik", persentase
    elif persentase < -20:
        return "turun", abs(persentase)
    else:
        return "stabil", abs(persentase)


def get_product_historical_data():
    query = """
        SELECT kode_produk, nama,
            DATE_FORMAT(tanggal_penjualan, '%%Y-%%m') as bulan,
            total_penjualan, total_pesanan
        FROM data_barang
        WHERE tanggal_penjualan IS NOT NULL AND nama IS NOT NULL
        ORDER BY kode_produk, tanggal_penjualan ASC
    """
    try:
        return pd.read_sql(query, engine)
    except Exception as e:
        print(f"Error get_product_historical_data: {e}")
        return pd.DataFrame()


def get_product_distribution_with_trend(prediksi, total_pesanan_input, rata_historis, alpha=0.3):
    """
    ANALISIS PRODUK - EXPONENTIAL SMOOTHING + TREN (TANPA RESTOCK)
    """
    try:
        print("="*60)
        print("ANALISIS PRODUK - EXPONENTIAL SMOOTHING & TREN")
        print("="*60)
        
        df_historis = get_product_historical_data()
        
        query = """
            SELECT DISTINCT kode_produk, nama
            FROM data_barang WHERE nama IS NOT NULL
        """
        df_produk = pd.read_sql(query, engine)
        if df_produk.empty:
            return []
        
        hasil = []
        
        print("\n" + "="*70)
        print("📊 HASIL FORECAST EXPONENTIAL SMOOTHING + TREN")
        print("="*70)
        print(f"{'No':<4} {'Nama Produk':<50} {'Forecast':<12} {'Tren':<15}")
        print("-"*70)
        
        no = 1
        for _, row in df_produk.iterrows():
            kode = row['kode_produk']
            nama = row['nama']
            
            if not df_historis.empty:
                hist_data = df_historis[df_historis['kode_produk'] == kode].sort_values('bulan')
            else:
                hist_data = pd.DataFrame()
            
            if len(hist_data) >= 2:
                pesanan_list = hist_data['total_pesanan'].tolist()
                penjualan_list = hist_data['total_penjualan'].tolist()
                
                # Exponential Smoothing
                forecast_pesanan = exponential_smoothing_product(pesanan_list, alpha)
                forecast_pesanan = max(1, round(forecast_pesanan))
                
                # Hitung Tren
                tren, persen_tren = calculate_product_trend(penjualan_list)
                
                if tren == "naik":
                    ikon = "📈"
                    tren_text = f"{ikon} Naik {persen_tren:.0f}%"
                elif tren == "turun":
                    ikon = "📉"
                    tren_text = f"{ikon} Turun {persen_tren:.0f}%"
                else:
                    ikon = "➡️"
                    tren_text = f"{ikon} Stabil"
                
                data_historis = len(hist_data)
            else:
                forecast_pesanan = 1
                tren_text = "🆕 Baru"
                data_historis = 0
            
            nama_display = nama[:47] + "..." if len(nama) > 50 else nama
            print(f"{no:<4} {nama_display:<50} {forecast_pesanan:<12} {tren_text:<15}")
            
            hasil.append({
                "nama": nama,
                "kode_produk": str(kode),
                "forecast_pesanan": forecast_pesanan,
                "data_historis": data_historis,
                "tren": tren_text if len(hist_data) >= 2 else "🆕 Baru"
            })
            no += 1
        
        print("-"*70)
        print(f"\n✅ Analisis selesai: {len(hasil)} produk diproses")
        print("="*70)
        
        hasil_sorted = sorted(hasil, key=lambda x: x['forecast_pesanan'], reverse=True)
        return hasil_sorted[:15]
        
    except Exception as e:
        print(f"❌ Error: {e}")
        traceback.print_exc()
        return []


# =========================
# ROUTE PREDICT
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
            total_pesanan = median_pesanan_cache
        
        tanggal = pd.to_datetime(data_input['tanggal'])

        prediksi = predict_hybrid(total_pesanan, tanggal)

        if prediksi > rata_historis_cache:
            status = "Meningkat"
        elif prediksi < rata_historis_cache:
            status = "Menurun"
        else:
            status = "Stabil"

        save_prediction_to_db(tanggal, total_pesanan, prediksi, rata_historis_cache, status)

        rekomendasi_produk = get_product_distribution_with_trend(
            prediksi, total_pesanan, rata_historis_cache, alpha
        )

        response = {
            "prediksi_total_penjualan": prediksi,
            "prediksi_format": f"Rp {prediksi:,.0f}",
            "rata_rata_historis": rata_historis_cache,
            "status": status,
            "alpha_used": alpha,
            "rekomendasi_produk": rekomendasi_produk,
            "evaluasi": evaluasi_cache
        }
        
        print(f"\n✅ PREDIKSI: {total_pesanan} pesanan = Rp {prediksi:,.0f}")
        
        return jsonify(response)

    except Exception as e:
        traceback.print_exc()
        return jsonify({"error": str(e)}), 500


@app.route('/health', methods=['GET'])
def health():
    return jsonify({
        "status": "healthy", 
        "model_loaded": model is not None,
        "evaluasi": evaluasi_cache
    })


if __name__ == '__main__':
    try:
        print("=" * 70)
        print("🚀 STARTING FLASK API SERVER")
        print("=" * 70)
        print("📊 METODE:")
        print("   1. Prediksi Total: Hybrid (Polynomial Degree 2 + Historis 1-5)")
        print("   2. Analisis Produk: Exponential Smoothing + Tren")
        print("=" * 70)
        
        train_model()
        test_model()
        load_historical_cache()
        
        print("\n" + "=" * 70)
        print("✅ SERVER READY!")
        print("=" * 70)
        if evaluasi_cache:
            print(f"📈 Evaluasi Model: MAPE={evaluasi_cache['MAPE']:.2f}%, R²={evaluasi_cache['R2']:.4f}")
        
        print("\n🌐 SERVER RUNNING ON http://localhost:5000")
        print("=" * 70)
        print("Endpoint: POST /predict , GET /health")
        print("=" * 70)
        
    except Exception as e:
        print(f"\n❌ ERROR: {e}")
        traceback.print_exc()
    
    app.run(debug=True, host='0.0.0.0', port=5000)