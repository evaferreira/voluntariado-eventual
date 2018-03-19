<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class IncluyeUpdatedAtYCreatedAtEnPersona extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('Persona', 'created_at')) {
            DB::statement("UPDATE Persona SET fechaNacimiento = NULL WHERE fechaNacimiento = '0000-00-00 00:00:00'");
            DB::statement("UPDATE Persona SET fechaInscripcion = NULL WHERE fechaInscripcion = '0000-00-00 00:00:00'");
            DB::statement("UPDATE Persona SET ultimaEntrada = NULL WHERE ultimaEntrada = '0000-00-00 00:00:00'");
            DB::statement("UPDATE Persona SET ultimaActualizacion = NULL WHERE ultimaActualizacion = '0000-00-00 00:00:00'");
            Schema::table('Persona', function (Blueprint $table) {
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('Persona', function (Blueprint $table) {
            $table->dropColumn(['created_at', 'updated_at']);
        });
    }
}