<?php

namespace App\Support;

class DeliveryAreas
{
    /**
     * Delivery/pickup areas mapped to their fee (in UGX).
     */
    public const FEES = [
        'Kampala Road' => 3500,
        'Nakasero' => 4000,
        'Old Kampala' => 3000,
        'Kisenyi' => 3500,
        'Wandegeya' => 3000,
        'Makerere' => 2000,
        'Ntinda' => 6000,
        'Naguru' => 5000,
        'Bugolobi' => 7000,
        'Nakawa' => 6500,
        'Kyambogo' => 7000,
        'Banda' => 10000,
        'Kiwatule' => 7000,
        'Namugongo' => 14000,
        'Kololo' => 5000,
        'Bukoto' => 5000,
        'Kamwokya' => 4000,
        'Acacia Area' => 4500,
        'Kisementi' => 3500,
        'Muyenga' => 7000,
        'Makindye' => 13000,
        'Kansanga' => 7000,
        'Ggaba' => 12500,
        'Munyonyo' => 14000,
        'Buziga' => 12000,
        'Zana' => 8000,
        'Bunamwaya' => 10000,
        'Najjanankumbi' => 7000,
        'Lubowa' => 7000,
        'Seguku' => 9000,
        'Kajjansi' => 14000,
        'Rubaga' => 4400,
        'Mengo' => 4000,
        'Namirembe' => 5000,
        'Kawempe' => 6000,
        'Bwaise' => 5000,
        'Kazo' => 5000,
        'Kanyanya' => 5000,
        'Maganjo' => 5500,
        'Kyaliwajjala' => 13000,
        'Kira' => 12500,
        'Najjera' => 10000,
        'Bulindo' => 15000,
    ];

    /**
     * All areas mapped to their fee.
     *
     * @return array<string, int>
     */
    public static function all(): array
    {
        return self::FEES;
    }

    /**
     * Whether the given area is a valid delivery/pickup area.
     */
    public static function has(?string $area): bool
    {
        return $area !== null && isset(self::FEES[$area]);
    }

    /**
     * Fee for the given area, or null when the area is unknown.
     */
    public static function fee(?string $area): ?int
    {
        return self::has($area) ? self::FEES[$area] : null;
    }
}
