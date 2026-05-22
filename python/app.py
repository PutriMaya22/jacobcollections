# ====================================================================
# LAPORAN PROYEK: SISTEM PREDIKSI PENJUALAN & REKOMENDASI PRODUK
# ====================================================================
# Nama Proyek    : Jacob Collections - Sales Prediction API
# Metode         : Polynomial Regression (Degree 2) + Exponential Smoothing
# Database       : MySQL (jacobcollections)
# Framework      : Flask (Python)
# ====================================================================

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
import joblib
import hashlib

warnings.filterwarnings('ignore')

app = Flask(__name__)
CORS(app)

# =========================
# KONEKSI DATABASE
# =========================
engine = create_engine("mysql+mysqlconnector://jacobuser:admin@localhost/jacobcollections")

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
rekomendasi_cache = {
    'data': None,
    'last_updated': None,
    'data_hash': None
}


# ====================================================================
# 1. DATA UNDERSTANDING (Pemahaman Data)
# ====================================================================

def data_understanding():
    """Fungsi untuk melakukan DATA UNDERSTANDING"""
    print("\n" + "="*70)
    print(" 1. DATA UNDERSTANDING (Pemahaman Data)")
    print("="*70)
    
    query_penjualan = "SELECT tanggal, total_pesanan, total_penjualan FROM data_penjualan"
    query_barang = "SELECT kode_produk, nama, total_penjualan, total_pesanan, tanggal_penjualan FROM data_barang"
    
    try:
        df_penjualan = pd.read_sql(query_penjualan, engine)
        df_barang = pd.read_sql(query_barang, engine)
        
        print("\n A. INFORMASI DATASET")
        print("-" * 50)
        print(f"   Dataset Penjualan   : {len(df_penjualan)} baris, {len(df_penjualan.columns)} kolom")
        print(f"   Dataset Barang      : {len(df_barang)} baris, {len(df_barang.columns)} kolom")
        print(f"   Periode Data        : {df_penjualan['tanggal'].min()} s/d {df_penjualan['tanggal'].max()}")
        
        print("\n B. STRUKTUR DATA (Data Penjualan)")
        print("-" * 50)
        print(f"   {'Kolom':<20} {'Tipe Data':<15} {'Jumlah Non-Null':<15}")
        print("   " + "-" * 48)
        for col in df_penjualan.columns:
            print(f"   {col:<20} {str(df_penjualan[col].dtype):<15} {df_penjualan[col].count():<15}")
        
        print("\n C. STATISTIK DESKRIPTIF (Data Penjualan)")
        print("-" * 50)
        print(f"   {'Metrik':<20} {'Total Pesanan':<20} {'Total Penjualan (Rp)':<25}")
        print("   " + "-" * 65)
        print(f"   {'Mean':<20} {df_penjualan['total_pesanan'].mean():<20,.0f} {df_penjualan['total_penjualan'].mean():<25,.0f}")
        print(f"   {'Median':<20} {df_penjualan['total_pesanan'].median():<20,.0f} {df_penjualan['total_penjualan'].median():<25,.0f}")
        print(f"   {'Std Deviasi':<20} {df_penjualan['total_pesanan'].std():<20,.0f} {df_penjualan['total_penjualan'].std():<25,.0f}")
        print(f"   {'Min':<20} {df_penjualan['total_pesanan'].min():<20,.0f} {df_penjualan['total_penjualan'].min():<25,.0f}")
        print(f"   {'Max':<20} {df_penjualan['total_pesanan'].max():<20,.0f} {df_penjualan['total_penjualan'].max():<25,.0f}")
        
        print("\n D. INFORMASI MISSING VALUE")
        print("-" * 50)
        missing_penjualan = df_penjualan.isnull().sum()
        missing_barang = df_barang.isnull().sum()
        
        if missing_penjualan.sum() > 0:
            print("   Data Penjualan:")
            for col, val in missing_penjualan.items():
                if val > 0:
                    print(f"      - {col}: {val} missing ({val/len(df_penjualan)*100:.1f}%)")
        else:
            print("   Data Penjualan: Tidak ada missing value")
        
        if missing_barang.sum() > 0:
            print("   Data Barang:")
            for col, val in missing_barang.items():
                if val > 0:
                    print(f"      - {col}: {val} missing ({val/len(df_barang)*100:.1f}%)")
        else:
            print("   Data Barang: Tidak ada missing value")
        
        print("\n E. INFORMASI DUPLIKAT")
        print("-" * 50)
        duplikat_penjualan = df_penjualan.duplicated().sum()
        duplikat_barang = df_barang.duplicated().sum()
        print(f"   Data Penjualan: {duplikat_penjualan} baris duplikat")
        print(f"   Data Barang    : {duplikat_barang} baris duplikat")
        
        print("\n F. DISTRIBUSI DATA")
        print("-" * 50)
        
        pesanan_kategori = pd.cut(df_penjualan['total_pesanan'], 
                                   bins=[0, 5, 20, 50, 100, float('inf')],
                                   labels=['1-5', '6-20', '21-50', '51-100', '>100'])
        distribusi_pesanan = pesanan_kategori.value_counts()
        print("   Distribusi Total Pesanan:")
        for kategori, count in distribusi_pesanan.items():
            print(f"      - {kategori}: {count} ({count/len(df_penjualan)*100:.1f}%)")
        
        penjualan_kategori = pd.cut(df_penjualan['total_penjualan'], 
                                     bins=[0, 100000, 500000, 1000000, 5000000, float('inf')],
                                     labels=['<100rb', '100rb-500rb', '500rb-1jt', '1jt-5jt', '>5jt'])
        distribusi_penjualan = penjualan_kategori.value_counts()
        print("   Distribusi Total Penjualan:")
        for kategori, count in distribusi_penjualan.items():
            print(f"      - {kategori}: {count} ({count/len(df_penjualan)*100:.1f}%)")
        
        print("\n G. INFORMASI PRODUK")
        print("-" * 50)
        print(f"   Total Produk Unik   : {df_barang['kode_produk'].nunique()}")
        print(f"   Total Nama Produk   : {df_barang['nama'].nunique()}")
        print(f"   Produk Terlaris (Top 5 berdasarkan penjualan):")
        
        top_products = df_barang.groupby('nama')['total_penjualan'].sum().sort_values(ascending=False).head(5)
        for i, (nama, penjualan) in enumerate(top_products.items(), 1):
            print(f"      {i}. {nama[:40]:<40} Rp {penjualan:,.0f}")
        
        print("\n H. KORELASI ANTAR FITUR")
        print("-" * 50)
        korelasi = df_penjualan[['total_pesanan', 'total_penjualan']].corr()
        print(f"   Korelasi Pesanan vs Penjualan: {korelasi.iloc[0,1]:.4f}")
        if korelasi.iloc[0,1] > 0.7:
            print("   Korelasi sangat kuat (positif)")
        elif korelasi.iloc[0,1] > 0.5:
            print("   Korelasi cukup kuat (positif)")
        else:
            print("   Korelasi lemah")
        
        print("\n" + "="*70)
        print("DATA UNDERSTANDING SELESAI")
        print("="*70)
        
        return df_penjualan, df_barang
        
    except Exception as e:
        print(f"Error dalam Data Understanding: {e}")
        return None, None


def convert_to_numeric_indonesia(series):
    """Mengkonversi format angka Indonesia (Rp 1.000.000) ke numerik"""
    if pd.api.types.is_numeric_dtype(series):
        return pd.to_numeric(series, errors='coerce')
    s = series.astype(str).str.strip()
    s = s.str.replace(r'[^0-9,.\-]', '', regex=True)
    s = s.str.replace('.', '', regex=False)
    s = s.str.replace(',', '.', regex=False)
    return pd.to_numeric(s, errors='coerce')


# ====================================================================
# 2. DATA PREPARATION (Persiapan Data)
# ====================================================================

def mean_absolute_percentage_error(y_true, y_pred):
    """MAPE: Mean Absolute Percentage Error"""
    y_true, y_pred = np.array(y_true), np.array(y_pred)
    mask = y_true != 0
    if len(y_true[mask]) == 0:
        return 0
    return np.mean(np.abs((y_true[mask] - y_pred[mask]) / y_true[mask])) * 100


def detect_outlier_iqr(df, column):
    """Deteksi outlier menggunakan metode IQR"""
    Q1 = df[column].quantile(0.25)
    Q3 = df[column].quantile(0.75)
    IQR = Q3 - Q1
    lower_bound = Q1 - 1.5 * IQR
    upper_bound = Q3 + 1.5 * IQR
    outliers = df[(df[column] < lower_bound) | (df[column] > upper_bound)]
    
    print(f"\n   Outlier kolom {column}:")
    print(f"      Q1          : {Q1:,.2f}")
    print(f"      Q3          : {Q3:,.2f}")
    print(f"      IQR         : {IQR:,.2f}")
    print(f"      Batas bawah : {lower_bound:,.2f}")
    print(f"      Batas atas  : {upper_bound:,.2f}")
    print(f"      Jumlah outlier : {len(outliers)} ({len(outliers)/len(df)*100:.1f}%)")
    return outliers, lower_bound, upper_bound


def handle_outliers_capping(df, column):
    """Handling outlier dengan metode CAPPING"""
    Q1 = df[column].quantile(0.25)
    Q3 = df[column].quantile(0.75)
    IQR = Q3 - Q1
    lower_bound = Q1 - 1.5 * IQR
    upper_bound = Q3 + 1.5 * IQR
    outlier_count = len(df[(df[column] < lower_bound) | (df[column] > upper_bound)])
    
    if outlier_count > 0:
        df[column] = df[column].clip(lower=lower_bound, upper=upper_bound)
        print(f"      → Capping {outlier_count} outlier pada kolom {column}")
    return df


def impute_zero_with_median(data, column='total_penjualan'):
    """Imputasi nilai 0 dengan MEDIAN"""
    global median_penjualan_cache, median_pesanan_cache
    
    data_valid = data[data[column] > 0]
    
    if len(data_valid) > 0:
        median_value = data_valid[column].median()
        zero_count = len(data[data[column] == 0])
        
        if zero_count > 0:
            print(f"   Imputasi: {zero_count} nilai 0 pada kolom '{column}' diganti dengan median: {median_value:,.0f}")
            data.loc[data[column] == 0, column] = median_value
        
        if column == 'total_penjualan':
            median_penjualan_cache = median_value
        else:
            median_pesanan_cache = median_value
            
        return median_value
    else:
        default_value = 100000 if column == 'total_penjualan' else 5
        print(f"   Tidak ada data positif, gunakan default: {default_value}")
        data.loc[data[column] == 0, column] = default_value
        
        if column == 'total_penjualan':
            median_penjualan_cache = default_value
        else:
            median_pesanan_cache = default_value
            
        return default_value


def load_and_prepare_data():
    """DATA CLEANING LENGKAP"""
    global median_penjualan_cache, median_pesanan_cache, rata_historis_cache
    
    print("\n" + "="*70)
    print("2. DATA PREPARATION")
    print("="*70)
    
    print("\n 2a. DATA CLEANING")
    print("-" * 50)
    
    query = "SELECT tanggal, total_pesanan, total_penjualan FROM data_penjualan"
    data = pd.read_sql(query, engine)

    if data.empty:
        raise Exception("Data penjualan kosong")
    
    print(f"   Data awal: {len(data)} baris")
    
    data.columns = data.columns.str.strip()
    data = data[['tanggal', 'total_pesanan', 'total_penjualan']]
    data['tanggal'] = pd.to_datetime(data['tanggal'], errors='coerce')
    data['total_pesanan'] = convert_to_numeric_indonesia(data['total_pesanan'])
    data['total_penjualan'] = convert_to_numeric_indonesia(data['total_penjualan'])
    print("   ✓ Konversi tipe data selesai")
    
    jumlah_duplikat = data.duplicated().sum()
    if jumlah_duplikat > 0:
        data = data.drop_duplicates()
        print(f"   ✓ Menghapus {jumlah_duplikat} data duplikat")
    
    data = data.dropna(subset=['tanggal', 'total_pesanan', 'total_penjualan'])
    
    impute_zero_with_median(data, 'total_penjualan')
    impute_zero_with_median(data, 'total_pesanan')
    
    data = data.dropna()
    
    print(f"   ✓ Data setelah cleaning: {len(data)} baris")
    
    print("\n 2b. DETEKSI & HANDLING OUTLIER")
    print("-" * 50)
    
    detect_outlier_iqr(data, 'total_penjualan')
    detect_outlier_iqr(data, 'total_pesanan')
    
    data = handle_outliers_capping(data, 'total_penjualan')
    data = handle_outliers_capping(data, 'total_pesanan')
    
    print(f"\n   ✓ Median total_penjualan: Rp {median_penjualan_cache:,.0f}")
    print(f"   ✓ Median total_pesanan: {median_pesanan_cache:.0f}")
    
    print("\n 2c. DATA TRANSFORMATION")
    print("-" * 50)
    
    data = data.sort_values('tanggal')
    data['hari'] = data['tanggal'].dt.dayofweek
    data['weekend'] = (data['hari'] >= 5).astype(int)
    data['bulan'] = data['tanggal'].dt.month
    data['tahun'] = data['tanggal'].dt.year
    
    print(f"   ✓ Fitur baru yang dibuat:")
    print(f"      - hari (0-6, 0=Senin, 6=Minggu)")
    print(f"      - weekend (1=Weekend, 0=Weekday)")
    print(f"      - bulan (1-12)")
    print(f"      - tahun")
    
    X = data[['total_pesanan', 'hari', 'weekend', 'bulan', 'tahun']]
    y = data['total_penjualan']
    
    rata_historis_cache = float(y.mean())
    print(f"\n   ✓ Rata-rata historis penjualan: Rp {rata_historis_cache:,.0f}")
    print(f"   ✓ Fitur yang digunakan: {list(X.columns)}")
    
    return X, y


def split_data(X, y):
    """SPLIT DATA: 80% Training, 20% Testing"""
    global X_train, X_test, y_train, y_test
    
    print("\n 2d. SPLIT DATA (80:20)")
    print("-" * 50)
    
    if len(X) < 5:
        print(f"   Data terlalu sedikit ({len(X)} baris)")
        X_train = X
        X_test = X
        y_train = y
        y_test = y
        return
    
    X_train, X_test, y_train, y_test = train_test_split(
        X, y, train_size=0.8, random_state=42
    )
    
    print(f"   ✓ Data Training: {len(X_train)} baris ({len(X_train)/len(X)*100:.0f}%)")
    print(f"   ✓ Data Testing : {len(X_test)} baris ({len(X_test)/len(X)*100:.0f}%)")


# ====================================================================
# 3. MODELLING (Pemodelan)
# ====================================================================

def save_model_to_file(model, poly, filepath='model_polynomial.joblib'):
    """Menyimpan model ke file joblib"""
    try:
        model_data = {
            'model': model,
            'poly': poly,
            'feature_names': ['total_pesanan', 'hari', 'weekend', 'bulan', 'tahun'],
            'created_at': datetime.now().strftime("%Y-%m-%d %H:%M:%S")
        }
        joblib.dump(model_data, filepath)
        print(f"   Model berhasil disimpan ke {filepath}")
        return True
    except Exception as e:
        print(f"   Gagal menyimpan model: {e}")
        return False


def load_model_from_file(filepath='model_polynomial.joblib'):
    """Memuat model dari file joblib"""
    try:
        if os.path.exists(filepath):
            model_data = joblib.load(filepath)
            print(f"   Model berhasil dimuat dari {filepath}")
            print(f"      - Dibuat pada: {model_data['created_at']}")
            return model_data['model'], model_data['poly']
        else:
            print(f"   File {filepath} tidak ditemukan, akan training model baru")
            return None, None
    except Exception as e:
        print(f"   Gagal memuat model: {e}")
        return None, None


def train_model():
    """TRAINING MODEL: Polynomial Regression Degree 2"""
    global model, poly, rata_historis_cache, X_train, X_test, y_train, y_test
    
    try:
        print("\n" + "="*70)
        print("3. MODELLING")
        print("="*70)
        
        print("\n METODE: Polynomial Regression (Degree 2)")
        print("-" * 50)
        print("   Alasan: Menangkap hubungan non-linear antara pesanan dan penjualan")
        print("   Formula: y = β₀ + β₁x + β₂x² + ... + interaksi fitur")
        
        loaded_model, loaded_poly = load_model_from_file()
        
        if loaded_model is not None and loaded_poly is not None:
            model = loaded_model
            poly = loaded_poly
            print("\n   ✓ Model berhasil diload (tanpa training ulang)")
            return True
        
        print("\n   MEMULAI TRAINING MODEL BARU...")
        X, y = load_and_prepare_data()
        rata_historis_cache = float(y.mean())
        split_data(X, y)
        
        if len(X_train) == 0:
            raise Exception("Data training kosong")
        
        print("\n   FEATURE ENGINEERING:")
        poly = PolynomialFeatures(degree=2, include_bias=False)
        X_train_poly = poly.fit_transform(X_train)
        print(f"      ✓ Fitur asli: {X_train.shape[1]} fitur")
        print(f"      ✓ Fitur hasil transformasi polynomial: {X_train_poly.shape[1]} fitur")
        
        print("\n   TRAINING LINEAR REGRESSION:")
        model = LinearRegression()
        model.fit(X_train_poly, y_train)
        print("      ✓ Model berhasil dilatih")
        
        save_model_to_file(model, poly)
        
        print("\n" + "="*50)
        print(" MODEL TRAINING COMPLETED")
        print("="*50)
        
        print("\n MODEL COEFFICIENTS:")
        feature_names = ['total_pesanan', 'hari', 'weekend', 'bulan', 'tahun']
        print(f"   Intercept: Rp {model.intercept_:,.0f}")
        print("   Koefisien 5 fitur pertama:")
        for name, coef in zip(feature_names, model.coef_[:5]):
            print(f"      - {name}: {coef:,.2f}")
        
        return True
        
    except Exception as e:
        print(f"   Error training model: {e}")
        traceback.print_exc()
        raise e


# ====================================================================
# 4. EVALUATION (Evaluasi Model)
# ====================================================================

def test_model():
    """EVALUASI MODEL dengan metrik RMSE, MAPE, R²"""
    global evaluasi_cache, X_test, y_test, X_train, y_train, model, poly
    
    if model is None:
        print("   Model belum ada, skip evaluasi")
        return
    
    print("\n" + "="*70)
    print("4. EVALUATION (Evaluasi Model)")
    print("="*70)
    
    print("\n METRIK EVALUASI:")
    print("-" * 40)
    print("   1. RMSE - Root Mean Square Error (sensitif terhadap outlier)")
    print("   2. MAPE - Mean Absolute Percentage Error (error dalam persen)")
    print("   3. R²   - Coefficient of Determination (keakuratan model)")
    
    if X_test is None or len(X_test) == 0:
        print("   X_test kosong, reload data...")
        X, y = load_and_prepare_data()
        split_data(X, y)
    
    if X_test.isna().any().any():
        valid_mask = ~X_test.isna().any(axis=1)
        X_test_clean = X_test[valid_mask]
        y_test_clean = y_test[valid_mask]
    else:
        X_test_clean = X_test
        y_test_clean = y_test
    
    if len(X_test_clean) == 0:
        print("   Tidak ada data test yang valid")
        return
    
    try:
        X_test_poly = poly.transform(X_test_clean)
        y_pred = model.predict(X_test_poly)
        
        rmse = np.sqrt(mean_squared_error(y_test_clean, y_pred))
        mape = mean_absolute_percentage_error(y_test_clean, y_pred)
        r2 = r2_score(y_test_clean, y_pred)
        
        evaluasi_cache = {
            "RMSE": float(rmse),
            "MAPE": float(mape),
            "R2": float(r2),
            "jumlah_data_train": len(X_train) if X_train is not None else 0,
            "jumlah_data_test": len(X_test_clean),
            "train_size": 0.8,
            "last_trained": datetime.now().strftime("%Y-%m-%d %H:%M:%S")
        }
        
        print("\n" + "="*60)
        print(" HASIL EVALUASI MODEL")
        print("="*60)
        print(f"\n{'Metrik':<15} {'Nilai':<20} {'Interpretasi':<35}")
        print("-" * 70)
        print(f"{'RMSE':<15} Rp {rmse:,.2f}      {'Semakin kecil semakin baik':<35}")
        print(f"{'MAPE':<15} {mape:.2f}%          {'Error dalam persen':<35}")
        print(f"{'R²':<15} {r2:.4f}            {'1 = sempurna, 0 = acak':<35}")
        print("-" * 70)
        
        print("\n INTERPRETASI HASIL:")
        print("-" * 40)
        
        if r2 >= 0.8:
            print(f"   R² = {r2:.4f} (Sangat Baik)")
            print(f"       Model mampu menjelaskan {r2*100:.1f}% variasi data penjualan")
        elif r2 >= 0.6:
            print(f"   R² = {r2:.4f} (Cukup Baik)")
            print(f"       Model mampu menjelaskan {r2*100:.1f}% variasi data penjualan")
        else:
            print(f"   R² = {r2:.4f} (Perlu Perbaikan)")
            print(f"       Model hanya menjelaskan {r2*100:.1f}% variasi data")
        
        if mape <= 10:
            print(f"   MAPE = {mape:.2f}% (Sangat Akurat)")
            print(f"       Tingkat error prediksi < 10%")
        elif mape <= 20:
            print(f"   MAPE = {mape:.2f}% (Cukup Akurat)")
            print(f"       Tingkat error prediksi antara 10-20%")
        else:
            print(f"   MAPE = {mape:.2f}% (Kurang Akurat)")
            print(f"       Tingkat error prediksi > 20%, perlu perbaikan model")
        
        print(f"\n INFORMASI TAMBAHAN:")
        print(f"   - Jumlah Data Training: {evaluasi_cache['jumlah_data_train']} baris")
        print(f"   - Jumlah Data Testing : {evaluasi_cache['jumlah_data_test']} baris")
        print(f"   - Rasio Train:Test    : 80:20")
        print(f"   - Terakhir dilatih    : {evaluasi_cache['last_trained']}")
        
        save_evaluation_history()
        
    except Exception as e:
        print(f"   Error evaluasi: {e}")
        traceback.print_exc()


# ====================================================================
# 5. PREDIKSI HYBRID
# ====================================================================

def load_historical_cache():
    """Cache data historis untuk pesanan 1-5"""
    global historical_cache
    print("\n" + "="*70)
    print(" LOAD HISTORICAL CACHE (Pesanan 1-5)")
    print("="*70)
    
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
            print(f"   Pesanan {pesanan}: Rp {historical_cache[pesanan]['rata_rata']:,.0f} (dari {historical_cache[pesanan]['jumlah_data']} data)")
        else:
            if pesanan == 1:
                historical_cache[pesanan] = {'rata_rata': 92570, 'jumlah_data': 0}
            else:
                historical_cache[pesanan] = {'rata_rata': historical_cache[pesanan-1]['rata_rata'] * pesanan, 'jumlah_data': 0}
            print(f"   Pesanan {pesanan}: estimasi Rp {historical_cache[pesanan]['rata_rata']:,.0f} (data tidak cukup)")


def predict_hybrid(total_pesanan, tanggal):
    """
    PREDIKSI HYBRID:
    - Pesanan 1-5: menggunakan rata-rata historis
    - Pesanan >5: menggunakan model Polynomial Regression
    """
    global model, poly, historical_cache
    
    if 1 <= total_pesanan <= 5:
        if total_pesanan in historical_cache:
            return historical_cache[total_pesanan]['rata_rata']
    
    input_df = pd.DataFrame([{
        'total_pesanan': total_pesanan,
        'hari': tanggal.dayofweek,
        'weekend': 1 if tanggal.dayofweek >= 5 else 0,
        'bulan': tanggal.month,
        'tahun': tanggal.year
    }])
    
    X_input_poly = poly.transform(input_df)
    prediksi = float(model.predict(X_input_poly)[0])
    return max(prediksi, 0)


# ====================================================================
# 6. REKOMENDASI PRODUK (UNTUK ENDPOINT /predict)
# ====================================================================

def get_data_hash():
    """Hash untuk deteksi perubahan data"""
    try:
        query = "SELECT COUNT(*) as count, MAX(updated_at) as last_updated FROM data_barang"
        result = pd.read_sql(query, engine)
        count = result.iloc[0, 0]
        last_updated = str(result.iloc[0, 1]) if result.iloc[0, 1] else datetime.now().strftime("%Y-%m-%d %H:%M:%S")
        hash_str = f"{count}_{last_updated}"
        return hashlib.md5(hash_str.encode()).hexdigest()
    except:
        return datetime.now().strftime("%Y%m%d%H%M%S")


def exponential_smoothing_product(data, alpha=0.3):
    """
    EXPONENTIAL SMOOTHING untuk forecast pesanan produk
    Formula: Ft = α * Yt-1 + (1-α) * Ft-1
    """
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
    """
    ANALISIS TREN PRODUK
    - Naik: peningkatan > 20%
    - Turun: penurunan > 20%
    - Stabil: perubahan ≤ 20%
    """
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
    """Mengambil data historis produk dari database"""
    query = """
        SELECT kode_produk, nama,
            DATE_FORMAT(tanggal_penjualan, '%Y-%m') as bulan,
            total_penjualan, total_pesanan
        FROM data_barang
        WHERE tanggal_penjualan IS NOT NULL AND nama IS NOT NULL
        ORDER BY kode_produk, tanggal_penjualan ASC
    """
    try:
        df = pd.read_sql(query, engine)
        print(f"   📊 Load {len(df)} baris data historis produk")
        return df
    except Exception as e:
        print(f"   Error: {e}")
        return pd.DataFrame()


def get_product_distribution_with_trend(prediksi, total_pesanan_input, rata_historis, alpha=0.3, force_refresh=False):
    """
    MENGHASILKAN REKOMENDASI PRODUK:
    - Forecast pesanan menggunakan Exponential Smoothing
    - Tren produk (Naik/Turun/Stabil)
    - Diurutkan berdasarkan forecast tertinggi
    """
    global rekomendasi_cache
    
    try:
        current_hash = get_data_hash()
        cache_valid = (
            not force_refresh 
            and rekomendasi_cache['data'] is not None 
            and rekomendasi_cache['data_hash'] == current_hash
        )
        
        if cache_valid and rekomendasi_cache['data']:
            print(f"   Menggunakan cache rekomendasi (data tidak berubah)")
            return rekomendasi_cache['data']
        
        print(f"   Membaca ulang data dari database...")
        
        df_historis = get_product_historical_data()
        
        query = """
            SELECT DISTINCT kode_produk, nama 
            FROM data_barang 
            WHERE nama IS NOT NULL AND nama != ''
        """
        df_produk = pd.read_sql(query, engine)
        
        if df_produk.empty:
            return []
        
        hasil = []
        
        for _, row in df_produk.iterrows():
            kode = row['kode_produk']
            nama = row['nama']
            
            actual_alpha = min(0.8, alpha) if force_refresh else alpha
            
            if not df_historis.empty:
                hist_data = df_historis[df_historis['kode_produk'] == kode].sort_values('bulan')
            else:
                hist_data = pd.DataFrame()
            
            if len(hist_data) >= 2:
                pesanan_list = hist_data['total_pesanan'].tolist()
                penjualan_list = hist_data['total_penjualan'].tolist()
                
                forecast_pesanan = exponential_smoothing_product(pesanan_list, actual_alpha)
                forecast_pesanan = max(1, round(forecast_pesanan))
                tren, persen_tren = calculate_product_trend(penjualan_list)
                
                if tren == "naik":
                    tren_text = f"📈 Naik {persen_tren:.0f}%"
                elif tren == "turun":
                    tren_text = f"📉 Turun {persen_tren:.0f}%"
                else:
                    tren_text = f"➡️ Stabil"
            else:
                forecast_pesanan = 1
                tren_text = "🆕 Baru"
                persen_tren = 0
            
            hasil.append({
                "nama": nama,
                "kode_produk": str(kode),
                "forecast_pesanan": forecast_pesanan,
                "tren": tren_text,
                "persentase_tren": round(persen_tren, 1)
            })
        
        hasil_sorted = sorted(hasil, key=lambda x: x['forecast_pesanan'], reverse=True)
        
        rekomendasi_cache['data'] = hasil_sorted[:15]
        rekomendasi_cache['last_updated'] = datetime.now().strftime("%Y-%m-%d %H:%M:%S")
        rekomendasi_cache['data_hash'] = current_hash
        
        return hasil_sorted[:15]
        
    except Exception as e:
        print(f"   Error: {e}")
        return rekomendasi_cache['data'] if rekomendasi_cache['data'] else []


# ====================================================================
# 7. RESTOCK REKOMENDASI DENGAN EXPONENTIAL SMOOTHING (TANPA FALLBACK)
# ====================================================================

def exponential_smoothing_calculate(data, alpha=0.3):
    """
    Exponential Smoothing untuk forecast
    Formula: Ft = α * Yt-1 + (1-α) * Ft-1
    """
    if len(data) < 2:
        return data[-1] if data else 0
    
    result = [data[0]]
    for i in range(1, len(data)):
        forecast = alpha * data[i-1] + (1 - alpha) * result[-1]
        result.append(forecast)
    
    # Forecast next period
    next_forecast = alpha * data[-1] + (1 - alpha) * result[-1]
    
    return next_forecast


@app.route('/restock', methods=['GET'])
def get_restock_rekomendasi():
    try:
        alpha = float(request.args.get('alpha', 0.3))
        limit = int(request.args.get('limit', 30))
        
        query_produk = f"""
            SELECT 
                kode_produk,
                nama,
                COALESCE(total_pesanan, 0) as total_terjual,
                COALESCE(stok, 0) as stok,
                tanggal_penjualan
            FROM data_barang
            WHERE nama IS NOT NULL AND total_pesanan > 0
            ORDER BY total_pesanan DESC
            LIMIT {limit}
        """
        
        df_produk = pd.read_sql(query_produk, engine)
        hasil = []
        today = datetime.now().date()
        
        for _, row in df_produk.iterrows():
            kode_produk = row['kode_produk']
            nama_produk = row['nama']
            total_terjual = int(row['total_terjual']) if pd.notna(row['total_terjual']) else 0
            stok = int(row['stok']) if pd.notna(row['stok']) else 0
            tgl_terakhir = row['tanggal_penjualan']
            
            # Hitung hari terakhir
            hari_terakhir = 999
            if tgl_terakhir and pd.notna(tgl_terakhir):
                if isinstance(tgl_terakhir, str):
                    tgl_terakhir = datetime.strptime(tgl_terakhir, '%Y-%m-%d').date()
                hari_terakhir = (today - tgl_terakhir).days
            
            # Ambil history untuk Exponential Smoothing
            query_history = f"""
                SELECT total_pesanan
                FROM data_barang
                WHERE kode_produk = {int(kode_produk)}
                    AND tanggal_penjualan IS NOT NULL
                    AND total_pesanan > 0
                ORDER BY tanggal_penjualan ASC
            """
            
            df_history = pd.read_sql(query_history, engine)
            pesanan_list = df_history['total_pesanan'].tolist() if not df_history.empty else []
            
            # Exponential Smoothing
            if len(pesanan_list) >= 2:
                forecast_next = exponential_smoothing_calculate(pesanan_list, alpha)
                forecast_next = max(1, round(forecast_next))
                forecast_harian = forecast_next / 30
                restock_recommended = max(0, int(forecast_harian * 14 - stok))
                method_used = f"Exponential Smoothing (α={alpha})"
                
                # Tren analysis
                if len(pesanan_list) >= 3:
                    recent_avg = sum(pesanan_list[-3:]) / 3
                    older_avg = sum(pesanan_list[:3]) / 3 if len(pesanan_list) >= 6 else pesanan_list[0]
                    if older_avg > 0:
                        trend_pct = ((recent_avg - older_avg) / older_avg) * 100
                        if trend_pct > 20:
                            tren_status = f"📈 Naik ({trend_pct:.0f}%)"
                        elif trend_pct < -20:
                            tren_status = f"📉 Turun ({abs(trend_pct):.0f}%)"
                        else:
                            tren_status = "➡️ Stabil"
                    else:
                        tren_status = "📈 Produk Baru"
                else:
                    tren_status = "🆕 Data Minim"
            else:
                restock_recommended = max(0, int((pesanan_list[-1] if pesanan_list else 1) / 30 * 14 - stok))
                method_used = "Simple Average (data < 2)"
                tren_status = "🆕 Data Minim"
            
            # Prioritas
            if stok <= 0 and hari_terakhir <= 7:
                prioritas, rekomendasi = 1, "🚨 RESTOCK DARURAT - Stok Habis, Masih Laku!"
            elif stok <= 0:
                prioritas, rekomendasi = 2, "⚠️ Stok Habis - Perlu Restock"
            elif restock_recommended > 50:
                prioritas, rekomendasi = 2, "✅ Restock Besar"
            elif restock_recommended > 0:
                prioritas, rekomendasi = 3, "📦 Restock Normal"
            elif hari_terakhir > 30:
                prioritas, rekomendasi = 4, "⚠️ Tidak Laku >30 hari - Evaluasi"
            else:
                prioritas, rekomendasi = 4, "➡️ Stok Cukup"
            
            hasil.append({
                "kode_produk": str(kode_produk),
                "nama_produk": nama_produk if nama_produk else '-',
                "total_terjual": total_terjual,  # <-- INTEGER
                "stok": stok,  # <-- INTEGER
                "jumlah_restock": restock_recommended,  # <-- INTEGER
                "terakhir_jual": tgl_terakhir.strftime('%Y-%m-%d') if tgl_terakhir else '-',
                "hari_terakhir": hari_terakhir,
                "tren": tren_status,
                "rekomendasi": rekomendasi,
                "prioritas": prioritas,
                "metode": method_used
            })
        
        # Sorting
        hasil.sort(key=lambda x: (x['prioritas'], x['stok']))
        
        return jsonify({
            "status": "success",
            "tanggal_update": datetime.now().strftime("%Y-%m-%d %H:%M:%S"),
            "alpha_used": alpha,
            "metode": "Exponential Smoothing",
            "total_produk": len(hasil),
            "rekomendasi_restock": hasil[:15]
        })
        
    except Exception as e:
        return jsonify({"status": "error", "message": str(e)}), 500

# ====================================================================
# 8. API ENDPOINTS UTAMA
# ====================================================================

@app.route('/predict', methods=['POST', 'OPTIONS'])
def predict():
    """Endpoint utama untuk prediksi penjualan"""
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
        alpha = float(data_input.get('alpha', 0.7))
        force_refresh = data_input.get('force_refresh', False)
        
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
            prediksi, total_pesanan, rata_historis_cache, alpha, force_refresh
        )

        response = {
            "prediksi_total_penjualan": prediksi,
            "prediksi_format": f"Rp {prediksi:,.0f}",
            "rata_rata_historis": rata_historis_cache,
            "status": status,
            "alpha_used": alpha,
            "force_refresh": force_refresh,
            "rekomendasi_produk": rekomendasi_produk,
            "evaluasi": evaluasi_cache
        }
        
        return jsonify(response)

    except Exception as e:
        traceback.print_exc()
        return jsonify({"error": str(e)}), 500


def save_prediction_to_db(tanggal_prediksi, total_pesanan, hasil_prediksi, rata_rata_historis, status_prediksi):
    """Menyimpan hasil prediksi ke database"""
    try:
        create_prediksi_table = """
            CREATE TABLE IF NOT EXISTS prediksis (
                id INT AUTO_INCREMENT PRIMARY KEY,
                tanggal_prediksi DATE NOT NULL,
                total_pesanan INT NOT NULL,
                hasil_prediksi DECIMAL(15, 2) NOT NULL,
                rata_rata_historis DECIMAL(15, 2),
                status_prediksi VARCHAR(50),
                metode VARCHAR(50),
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )
        """
        with engine.connect() as conn:
            conn.execute(text(create_prediksi_table))
            conn.commit()
        
        if isinstance(tanggal_prediksi, str):
            tanggal_prediksi = pd.to_datetime(tanggal_prediksi).date()
        elif hasattr(tanggal_prediksi, 'date'):
            tanggal_prediksi = tanggal_prediksi.date()
        
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
                'metode': 'Hybrid_Polynomial'
            })
            conn.commit()
    except Exception as e:
        print(f"Gagal simpan prediksi: {e}")


@app.route('/refresh/rekomendasi', methods=['POST', 'GET'])
def refresh_rekomendasi():
    """Endpoint untuk memaksa refresh rekomendasi"""
    try:
        global rekomendasi_cache
        
        rekomendasi_cache = {
            'data': None,
            'last_updated': None,
            'data_hash': None
        }
        
        if request.method == 'POST':
            data_input = request.get_json(force=True) or {}
            alpha = float(data_input.get('alpha', 0.8))
        else:
            alpha = 0.8
        
        dummy_prediksi = 1000000
        dummy_total_pesanan = 10
        dummy_rata_historis = 500000
        
        hasil = get_product_distribution_with_trend(
            dummy_prediksi, dummy_total_pesanan, dummy_rata_historis, 
            alpha=alpha, force_refresh=True
        )
        
        return jsonify({
            "status": "success",
            "message": "Rekomendasi berhasil direfresh",
            "total_produk": len(hasil)
        })
    except Exception as e:
        return jsonify({"error": str(e)}), 500


@app.route('/rekomendasi/status', methods=['GET'])
def rekomendasi_status():
    """Cek status cache rekomendasi"""
    return jsonify({
        "cache_available": rekomendasi_cache['data'] is not None,
        "last_updated": rekomendasi_cache.get('last_updated'),
        "total_produk_in_cache": len(rekomendasi_cache['data']) if rekomendasi_cache['data'] else 0
    })


@app.route('/health', methods=['GET'])
def health():
    """Cek kesehatan server"""
    return jsonify({
        "status": "healthy", 
        "model_loaded": model is not None,
        "evaluasi": evaluasi_cache
    })


# ====================================================================
# 9. MAIN (EKSEKUSI UTAMA)
# ====================================================================
if __name__ == '__main__':
    try:
        if os.path.exists('model_polynomial.joblib'):
            os.remove('model_polynomial.joblib')
        
        print("=" * 70)
        print("LAPORAN PROYEK - JACOB COLLECTIONS")
        print("=" * 70)
        print("SISTEM PREDIKSI PENJUALAN & REKOMENDASI PRODUK")
        print("=" * 70)
        
        df_penjualan, df_barang = data_understanding()
        
        X, y = load_and_prepare_data()
        split_data(X, y)
        
        train_model()
        
        test_model()
        
        load_historical_cache()
        
        print("\n" + "=" * 70)
        print("🚀 SERVER READY")
        print("=" * 70)
        if evaluasi_cache:
            print(f"📈 FINAL EVALUATION: MAPE={evaluasi_cache['MAPE']:.2f}%, R²={evaluasi_cache['R2']:.4f}")
        
        print("\n🌐 RUNNING ON http://jacobcollections.my.id:5000")
        print("=" * 70)
        print("📌 ENDPOINTS:")
        print("   POST /predict              - Prediksi penjualan")
        print("   POST /refresh/rekomendasi  - Force refresh rekomendasi")
        print("   GET  /rekomendasi/status   - Cek status cache")
        print("   GET  /restock              - Rekomendasi restock (Exponential Smoothing)")
        print("   POST /restock/refresh      - Refresh restock")
        print("   GET  /health               - Cek kesehatan server")
        print("=" * 70)
        
    except Exception as e:
        print(f"\n❌ ERROR: {e}")
        traceback.print_exc()
    
    app.run(debug=True, host='0.0.0.0', port=5000)