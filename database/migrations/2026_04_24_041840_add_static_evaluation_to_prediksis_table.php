<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('prediksis', function (Blueprint $table) {
            // Kolom evaluasi statis (tidak berubah setelah realisasi diupdate)
            $table->decimal('static_mape', 10, 2)->nullable()->after('mape');
            $table->decimal('static_rmse', 15, 2)->nullable()->after('rmse');
            $table->decimal('static_r_squared', 10, 4)->nullable()->after('r_squared');
            $table->decimal('static_error', 15, 2)->nullable()->after('error');
            $table->timestamp('evaluasi_captured_at')->nullable()->after('static_error');
        });
    }

    public function down()
    {
        Schema::table('prediksis', function (Blueprint $table) {
            $table->dropColumn([
                'static_mape',
                'static_rmse',
                'static_r_squared',
                'static_error',
                'evaluasi_captured_at'
            ]);
        });
    }
};