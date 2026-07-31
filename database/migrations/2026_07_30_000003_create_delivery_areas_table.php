<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        Schema::create('delivery_areas', function (Blueprint $table) {
            $table->id();
            $table->string('city', 100);
            $table->string('name', 255);
            $table->decimal('fee', 10, 0)->default(0);
            $table->timestamps();
        });

        $areas = [
            ['city' => 'Kampala', 'name' => 'Kampala Road', 'fee' => 3500],
            ['city' => 'Kampala', 'name' => 'Nakasero', 'fee' => 4000],
            ['city' => 'Kampala', 'name' => 'Old Kampala', 'fee' => 3000],
            ['city' => 'Kampala', 'name' => 'Kisenyi', 'fee' => 3500],
            ['city' => 'Kampala', 'name' => 'Wandegeya', 'fee' => 3000],
            ['city' => 'Kampala', 'name' => 'Makerere', 'fee' => 2000],
            ['city' => 'Kampala', 'name' => 'Ntinda', 'fee' => 6000],
            ['city' => 'Kampala', 'name' => 'Naguru', 'fee' => 5000],
            ['city' => 'Kampala', 'name' => 'Bugolobi', 'fee' => 7000],
            ['city' => 'Kampala', 'name' => 'Nakawa', 'fee' => 6500],
            ['city' => 'Kampala', 'name' => 'Kyambogo', 'fee' => 7000],
            ['city' => 'Kampala', 'name' => 'Banda', 'fee' => 10000],
            ['city' => 'Kampala', 'name' => 'Kiwatule', 'fee' => 7000],
            ['city' => 'Kampala', 'name' => 'Namugongo', 'fee' => 14000],
            ['city' => 'Kampala', 'name' => 'Kololo', 'fee' => 5000],
            ['city' => 'Kampala', 'name' => 'Bukoto', 'fee' => 5000],
            ['city' => 'Kampala', 'name' => 'Kamwokya', 'fee' => 4000],
            ['city' => 'Kampala', 'name' => 'Acacia Area', 'fee' => 4500],
            ['city' => 'Kampala', 'name' => 'Kisementi', 'fee' => 3500],
            ['city' => 'Kampala', 'name' => 'Muyenga', 'fee' => 7000],
            ['city' => 'Kampala', 'name' => 'Makindye', 'fee' => 13000],
            ['city' => 'Kampala', 'name' => 'Kansanga', 'fee' => 7000],
            ['city' => 'Kampala', 'name' => 'Ggaba', 'fee' => 12500],
            ['city' => 'Kampala', 'name' => 'Munyonyo', 'fee' => 14000],
            ['city' => 'Kampala', 'name' => 'Buziga', 'fee' => 12000],
            ['city' => 'Kampala', 'name' => 'Zana', 'fee' => 8000],
            ['city' => 'Kampala', 'name' => 'Bunamwaya', 'fee' => 10000],
            ['city' => 'Kampala', 'name' => 'Najjanankumbi', 'fee' => 7000],
            ['city' => 'Kampala', 'name' => 'Lubowa', 'fee' => 7000],
            ['city' => 'Kampala', 'name' => 'Seguku', 'fee' => 9000],
            ['city' => 'Kampala', 'name' => 'Kajjansi', 'fee' => 14000],
            ['city' => 'Kampala', 'name' => 'Rubaga', 'fee' => 4400],
            ['city' => 'Kampala', 'name' => 'Mengo', 'fee' => 4000],
            ['city' => 'Kampala', 'name' => 'Namirembe', 'fee' => 5000],
            ['city' => 'Kampala', 'name' => 'Kawempe', 'fee' => 6000],
            ['city' => 'Kampala', 'name' => 'Bwaise', 'fee' => 5000],
            ['city' => 'Kampala', 'name' => 'Kazo', 'fee' => 5000],
            ['city' => 'Kampala', 'name' => 'Kanyanya', 'fee' => 5000],
            ['city' => 'Kampala', 'name' => 'Maganjo', 'fee' => 5500],
            ['city' => 'Kampala', 'name' => 'Kyaliwajjala', 'fee' => 13000],
            ['city' => 'Kampala', 'name' => 'Kira', 'fee' => 12500],
            ['city' => 'Kampala', 'name' => 'Najjera', 'fee' => 10000],
            ['city' => 'Kampala', 'name' => 'Bulindo', 'fee' => 15000],
        ];

        DB::table('delivery_areas')->insert($areas);
    }

    public function down()
    {
        Schema::dropIfExists('delivery_areas');
    }
};
