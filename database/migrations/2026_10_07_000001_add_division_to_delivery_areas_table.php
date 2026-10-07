<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        Schema::table('delivery_areas', function (Blueprint $table) {
            $table->string('division', 100)->nullable()->after('city');
        });

        // Kampala's 5 divisions.
        $kampalaDivisions = ['Central', 'Kawempe', 'Makindye', 'Nakawa', 'Rubaga'];

        // Seed division only where it is still empty, keeping admin edits intact.
        $map = [
            // Central Division
            'Kampala Road' => 'Central', 'Nakasero' => 'Central', 'Old Kampala' => 'Central',
            'Kisenyi' => 'Central', 'Mengo' => 'Central', 'Namirembe' => 'Central',
            'Kololo' => 'Central', 'Kamwokya' => 'Central', 'Acacia Area' => 'Central',
            'Kisementi' => 'Central', 'Bukoto' => 'Central', 'Naguru' => 'Central',
            // Kawempe Division
            'Wandegeya' => 'Kawempe', 'Makerere' => 'Kawempe', 'Kawempe' => 'Kawempe',
            'Bwaise' => 'Kawempe', 'Kazo' => 'Kawempe', 'Kanyanya' => 'Kawempe', 'Maganjo' => 'Kawempe',
            // Makindye Division
            'Muyenga' => 'Makindye', 'Makindye' => 'Makindye', 'Kansanga' => 'Makindye',
            'Ggaba' => 'Makindye', 'Munyonyo' => 'Makindye', 'Buziga' => 'Makindye',
            'Zana' => 'Makindye', 'Bunamwaya' => 'Makindye', 'Najjanankumbi' => 'Makindye',
            'Lubowa' => 'Makindye', 'Seguku' => 'Makindye', 'Kajjansi' => 'Makindye', 'Rubaga' => 'Makindye',
            // Nakawa Division
            'Ntinda' => 'Nakawa', 'Bugolobi' => 'Nakawa', 'Nakawa' => 'Nakawa',
            'Kyambogo' => 'Nakawa', 'Banda' => 'Nakawa', 'Kiwatule' => 'Nakawa',
            'Namugongo' => 'Nakawa', 'Kyaliwajjala' => 'Nakawa', 'Kira' => 'Nakawa',
            'Najjera' => 'Nakawa', 'Bulindo' => 'Nakawa',
            // Rubaga Division
            'Busega' => 'Rubaga', 'Nansana' => 'Rubaga', 'Kasubi' => 'Rubaga', 'Nabulagala' => 'Rubaga',
        ];

        foreach ($map as $name => $division) {
            DB::table('delivery_areas')
                ->where('name', $name)
                ->whereNull('division')
                ->update(['division' => $division]);
        }

        // Anything in Kampala city still without a division gets Central as a safe default.
        DB::table('delivery_areas')
            ->where('city', 'Kampala')
            ->whereNull('division')
            ->update(['division' => 'Central']);
    }

    public function down()
    {
        Schema::table('delivery_areas', function (Blueprint $table) {
            $table->dropColumn('division');
        });
    }
};
