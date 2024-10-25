<?php

namespace Database\Seeders;

use App\Models\TimeZone;
use Illuminate\Database\Seeder;

class TimeZonesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        TimeZone::truncate();

        foreach (phpDefaultTimeZones() as $timezone)
        {
            TimeZone::insert(
                [
                    'zone' => $timezone['zone'],
                    'gmt'  => $timezone['diff_from_GMT'],
                ]
            );
        }
        // TimeZone::truncate();
        // TimeZone::insert([
        //     [
        //         'id'   => 1,
        //         'zone' => 'Africa/Abidjan',
        //         'gmt'  => '0',
        //     ],
        //     [
        //         'id'   => 2,
        //         'zone' => 'Africa/Accra',
        //         'gmt'  => '0',
        //     ],
        //     [
        //         'id'   => 3,
        //         'zone' => 'Africa/Addis_Ababa',
        //         'gmt'  => '+3',
        //     ],
        //     [
        //         'id'   => 4,
        //         'zone' => 'Africa/Algiers',
        //         'gmt'  => '+1',
        //     ],
        //     [
        //         'id'   => 5,
        //         'zone' => 'Africa/Asmara',
        //         'gmt'  => '+3',
        //     ],
        //     [
        //         'id'   => 6,
        //         'zone' => 'Africa/Bamako',
        //         'gmt'  => '0',
        //     ],
        //     [
        //         'id'   => 7,
        //         'zone' => 'Africa/Bangui',
        //         'gmt'  => '+1',
        //     ],
        //     [
        //         'id'   => 8,
        //         'zone' => 'Africa/Banjul',
        //         'gmt'  => '0',
        //     ],
        //     [
        //         'id'   => 9,
        //         'zone' => 'Africa/Bissau',
        //         'gmt'  => '0',
        //     ],
        //     [
        //         'id'   => 10,
        //         'zone' => 'Africa/Blantyre',
        //         'gmt'  => '+2',
        //     ],
        //     [
        //         'id'   => 11,
        //         'zone' => 'Africa/Brazzaville',
        //         'gmt'  => '+1',
        //     ],
        //     [
        //         'id'   => 12,
        //         'zone' => 'Africa/Bujumbura',
        //         'gmt'  => '+2',
        //     ],
        //     [
        //         'id'   => 13,
        //         'zone' => 'Africa/Cairo',
        //         'gmt'  => '+2',
        //     ],
        //     [
        //         'id'   => 14,
        //         'zone' => 'Africa/Casablanca',
        //         'gmt'  => '0',
        //     ],
        //     [
        //         'id'   => 15,
        //         'zone' => 'Africa/Ceuta',
        //         'gmt'  => '1',
        //     ],
        //     [
        //         'id'   => 16,
        //         'zone' => 'Africa/Conakry',
        //         'gmt'  => '0',
        //     ],
        //     [
        //         'id'   => 17,
        //         'zone' => 'Africa/Dakar',
        //         'gmt'  => '0',
        //     ],
        //     [
        //         'id'   => 18,
        //         'zone' => 'Africa/Dar_es_Salaam',
        //         'gmt'  => '+3',
        //     ],
        //     [
        //         'id'   => 19,
        //         'zone' => 'Africa/Djibouti',
        //         'gmt'  => '+3',
        //     ],
        //     [
        //         'id'   => 20,
        //         'zone' => 'Africa/Douala',
        //         'gmt'  => '+1',
        //     ],

        //     [
        //         'id'   => 21,
        //         'zone' => 'Africa/El_Aaiun',
        //         'gmt'  => '0',
        //     ],
        //     [
        //         'id'   => 22,
        //         'zone' => 'Africa/Freetown',
        //         'gmt'  => '0',
        //     ],
        //     [
        //         'id'   => 23,
        //         'zone' => 'Africa/Gaborone',
        //         'gmt'  => '+2',
        //     ],
        //     [
        //         'id'   => 24,
        //         'zone' => 'Africa/Harare',
        //         'gmt'  => '+2',
        //     ],
        //     [
        //         'id'   => 25,
        //         'zone' => 'Africa/Johannesburg',
        //         'gmt'  => '+2',
        //     ],
        //     [
        //         'id'   => 26,
        //         'zone' => 'Africa/Juba',
        //         'gmt'  => '+3',
        //     ],
        //     [
        //         'id'   => 27,
        //         'zone' => 'Africa/Kampala',
        //         'gmt'  => '+3',
        //     ],
        //     [
        //         'id'   => 28,
        //         'zone' => 'Africa/Khartoum',
        //         'gmt'  => '+2',
        //     ],
        //     [
        //         'id'   => 29,
        //         'zone' => 'Africa/Kigali',
        //         'gmt'  => '+2',
        //     ],
        //     [
        //         'id'   => 30,
        //         'zone' => 'Africa/Kinshasa',
        //         'gmt'  => '+1',
        //     ],
        //     [
        //         'id'   => 31,
        //         'zone' => 'Africa/Lagos',
        //         'gmt'  => '+1',
        //     ],
        //     [
        //         'id'   => 32,
        //         'zone' => 'Africa/Libreville',
        //         'gmt'  => '+1',
        //     ],
        //     [
        //         'id'   => 33,
        //         'zone' => 'Africa/Lome',
        //         'gmt'  => '0',
        //     ],
        //     [
        //         'id'   => 34,
        //         'zone' => 'Africa/Luanda',
        //         'gmt'  => '+1',
        //     ],
        //     [
        //         'id'   => 35,
        //         'zone' => 'Africa/Lubumbashi',
        //         'gmt'  => '+2',
        //     ],
        //     [
        //         'id'   => 36,
        //         'zone' => 'Africa/Lusaka',
        //         'gmt'  => '+2',
        //     ],
        //     [
        //         'id'   => 37,
        //         'zone' => 'Africa/Malabo',
        //         'gmt'  => '+1',
        //     ],
        //     [
        //         'id'   => 38,
        //         'zone' => 'Africa/Maputo',
        //         'gmt'  => '+2',
        //     ],
        //     [
        //         'id'   => 39,
        //         'zone' => 'Africa/Maseru',
        //         'gmt'  => '+2',
        //     ],
        //     [
        //         'id'   => 40,
        //         'zone' => 'Africa/Mbabane',
        //         'gmt'  => '+2',
        //     ],
        //     [
        //         'id'   => 41,
        //         'zone' => 'Africa/Mogadishu',
        //         'gmt'  => '+3',
        //     ],
        //     [
        //         'id'   => 42,
        //         'zone' => 'Africa/Monrovia',
        //         'gmt'  => '0',
        //     ],
        //     [
        //         'id'   => 43,
        //         'zone' => 'Africa/Nairobi',
        //         'gmt'  => '+3',
        //     ],
        //     [
        //         'id'   => 44,
        //         'zone' => 'Africa/Ndjamena',
        //         'gmt'  => '+1',
        //     ],
        //     [
        //         'id'   => 45,
        //         'zone' => 'Africa/Niamey',
        //         'gmt'  => '+1',
        //     ],
        //     [
        //         'id'   => 46,
        //         'zone' => 'Africa/Nouakchott',
        //         'gmt'  => '0',
        //     ],
        //     [
        //         'id'   => 47,
        //         'zone' => 'Africa/Ouagadougou',
        //         'gmt'  => '0',
        //     ],
        //     [
        //         'id'   => 48,
        //         'zone' => 'Africa/Porto-Novo',
        //         'gmt'  => '+1',
        //     ],
        //     [
        //         'id'   => 49,
        //         'zone' => 'Africa/Sao_Tome',
        //         'gmt'  => '+1',
        //     ],
        //     [
        //         'id'   => 50,
        //         'zone' => 'Africa/Tripoli',
        //         'gmt'  => '+2',
        //     ],
        //     [
        //         'id'   => 51,
        //         'zone' => 'Africa/Tunis',
        //         'gmt'  => '+1',
        //     ],
        //     [
        //         'id' => 52,
        //         'zone' => 'Africa/Windhoek',
        //         'gmt'  => '+2',
        //     ],
        //     [
        //         'id'   => 53,
        //         'zone' => 'America/Adak',
        //         'gmt'  => '-10',
        //     ],
        //     [
        //         'id'   => 54,
        //         'zone' => 'America/Anchorage',
        //         'gmt'  => '-9',
        //     ],
        //     [
        //         'id'   => 55,
        //         'zone' => 'America/Anguilla',
        //         'gmt'  => '-4',
        //     ],
        //     [
        //         'id'   => 56,
        //         'zone' => 'America/Antigua',
        //         'gmt'  => '-4',
        //     ],

        //     [
        //         'id'   => 57,
        //         'zone' => 'America/Araguaina',
        //         'gmt'  => '-3',
        //     ],
        //     [
        //         'id'   => 58,
        //         'zone' => 'America/Argentina/Buenos_Aires',
        //         'gmt'  => '-3',
        //     ],
        //     [
        //         'id'   => 59,
        //         'zone' => 'America/Argentina/Catamarca',
        //         'gmt'  => '-3',
        //     ],

        //     [
        //         'id'   => 60,
        //         'zone' => 'America/Argentina/Cordoba',
        //         'gmt'  => '-3',
        //     ],
        //     [
        //         'id'   => 61,
        //         'zone' => 'America/Argentina/Jujuy',
        //         'gmt'  => '-3',
        //     ],
        //     [
        //         'id'   => 62,
        //         'zone' => 'America/Argentina/La_Rioja',
        //         'gmt'  => '-3',
        //     ],

        //     [
        //         'id'   => 63,
        //         'zone' => 'America/Argentina/Mendoza',
        //         'gmt'  => '-3',
        //     ],
        //     [
        //         'id'   => 64,
        //         'zone' => 'America/Argentina/Rio_Gallego',
        //         'gmt'  => '-3',
        //     ],

        //     [
        //         'id'   => 65,
        //         'zone' => 'America/Argentina/Salta',
        //         'gmt'  => '-3',
        //     ],
        //     [
        //         'id'   => 66,
        //         'zone' => 'America/Argentina/San_Juan',
        //         'gmt'  => '-3',
        //     ],

        //     [
        //         'id'   => 67,
        //         'zone' => 'America/Argentina/San_Luis',
        //         'gmt'  => '-3',
        //     ],
        //     [
        //         'id'   => 68,
        //         'zone' => 'America/Argentina/Tucuman',
        //         'gmt'  => '-3',
        //     ],

        //     [
        //         'id' => 69,
        //         'zone' => 'America/Argentina/Ushuaia',
        //         'gmt'  => '-3',
        //     ],
        //     [
        //         'id'   => 70,
        //         'zone' => 'America/Aruba',
        //         'gmt'  => '-4',
        //     ],
        //     [
        //         'id'   => 71,
        //         'zone' => 'America/Asuncion',
        //         'gmt'  => '-4',
        //     ],
        //     [
        //         'id'   => 72,
        //         'zone' => 'America/Atikokan',
        //         'gmt'  => '-5',
        //     ],

        //     [
        //         'id'   => 73,
        //         'zone' => 'America/Bahia',
        //         'gmt'  => '-3',
        //     ],
        //     [
        //         'id'   => 74,
        //         'zone' => 'America/Bahia_Banderas',
        //         'gmt'  => '-6',
        //     ],
        //     [
        //         'id'   => 75,
        //         'zone' => 'America/Barbados',
        //         'gmt'  => '-5',
        //     ],
        //     [
        //         'id'   => 76,
        //         'zone' => 'America/Belem',
        //         'gmt'  => '-3',
        //     ],
        //     [
        //         'id'   => 77,
        //         'zone' => 'America/Belize',
        //         'gmt'  => '-6',
        //     ],
        //     [
        //         'id'   => 78,
        //         'zone' => 'America/Blanc-Sablon',
        //         'gmt'  => '-4',
        //     ],
        //     [
        //         'id'   => 79,
        //         'zone' => 'America/Boa_Vista',
        //         'gmt'  => '-4',
        //     ],

        //     [
        //         'id'   => 80,
        //         'zone' => 'America/Bogota',
        //         'gmt'  => '-5',
        //     ],

        //     [
        //         'id'   => 81,
        //         'zone' => 'America/Boise',
        //         'gmt'  => '-7',
        //     ],

        //     [
        //         'id'   => 82,
        //         'zone' => 'America/Cambridge_Bay',
        //         'gmt'  => '-7',
        //     ],

        //     [
        //         'id'   => 83,
        //         'zone' => 'America/Campo_Grande',
        //         'gmt'  => '-3',
        //     ],

        //     [
        //         'id'   => 84,
        //         'zone' => 'America/Cancun',
        //         'gmt'  => '-5',
        //     ],

        //     [
        //         'id'   => 85,
        //         'zone' => 'America/Caracas',
        //         'gmt'  => '-4',
        //     ],

        //     [
        //         'id'   => 86,
        //         'zone' => 'America/Cayenne',
        //         'gmt'  => '-3',
        //     ],

        //     [
        //         'id'   => 87,
        //         'zone' => 'America/Cayman',
        //         'gmt'  => '-5',
        //     ],

        //     [
        //         'id'   => 88,
        //         'zone' => 'America/Chicago',
        //         'gmt'  => '-6',
        //     ],

        //     [
        //         'id'   => 89,
        //         'zone' => 'America/Chihuahua',
        //         'gmt'  => '-7',
        //     ],

        //     [
        //         'id'   => 90,
        //         'zone' => 'America/Costa_Rica',
        //         'gmt'  => '-6',
        //     ],

        //     [
        //         'id'   => 91,
        //         'zone' => 'America/Creston',
        //         'gmt'  => '-7',
        //     ],

        //     [
        //         'id'   => 92,
        //         'zone' => 'America/Cuiaba',
        //         'gmt'  => '-4',
        //     ],

        //     [
        //         'id'   => 93,
        //         'zone' => 'America/Curacao',
        //         'gmt'  => '-4',
        //     ],

        //     [
        //         'id'   => 94,
        //         'zone' => 'America/Danmarkshavn',
        //         'gmt'  => '0',
        //     ],

        //     [
        //         'id'   => 95,
        //         'zone' => 'America/Dawson',
        //         'gmt'  => '-8',
        //     ],

        //     [
        //         'id'   => 96,
        //         'zone' => 'America/Dawson_Creek',
        //         'gmt'  => '-7',
        //     ],

        //     [
        //         'id'   => 97,
        //         'zone' => 'America/Denver',
        //         'gmt'  => '-7',
        //     ],

        //     [
        //         'id'   => 98,
        //         'zone' => 'America/Detroit',
        //         'gmt'  => '-5',
        //     ],

        //     [
        //         'id'   => 99,
        //         'zone' => 'America/Dominica',
        //         'gmt'  => '-4',
        //     ],

        //     [
        //         'id'   => 100,
        //         'zone' => 'America/Edmonton',
        //         'gmt'  => '-7',
        //     ],

        //     [
        //         'id'   => 101,
        //         'zone' => 'America/Eirunepe',
        //         'gmt'  => '-5',
        //     ],
        //     [
        //         'id'   => 102,
        //         'zone' => 'America/El_Salvador',
        //         'gmt'  => '-5',
        //     ],
        //     [
        //         'id'   => 103,
        //         'zone' => 'America/Fort_Nelson',
        //         'gmt'  => '-3',
        //     ],

        //     [
        //         'id'   => 104,
        //         'zone' => 'America/Fortaleza',
        //         'gmt'  => '-3',
        //     ],

        //     [
        //         'id'   => 105,
        //         'zone' => 'America/Glace_Bay',
        //         'gmt'  => '-4',
        //     ],

        //     [
        //         'id'   => 106,
        //         'zone' => 'America/Godthab',
        //         'gmt'  => '-3',
        //     ],

        //     [
        //         'id'   => 107,
        //         'zone' => 'America/Goose_Bay',
        //         'gmt'  => '-4',
        //     ],

        //     [
        //         'id'   => 108,
        //         'zone' => 'America/Grand_Turk',
        //         'gmt'  => '-8',
        //     ],

        //     [
        //         'id'   => 109,
        //         'zone' => 'America/Grenada',
        //         'gmt'  => '-4',
        //     ],

        //     [
        //         'id'   => 110,
        //         'zone' => 'America/Guadeloupe',
        //         'gmt'  => '-4',
        //     ],

        //     [
        //         'id'   => 111,
        //         'zone' => 'America/Guatemala',
        //         'gmt'  => '-6',
        //     ],

        //     [
        //         'id'   => 112,
        //         'zone' => 'America/Guayaquil',
        //         'gmt'  => '-5',
        //     ],

        //     [
        //         'id'   => 113,
        //         'zone' => 'America/Guyana',
        //         'gmt'  => '-4',
        //     ],

        //     [
        //         'id'   => 114,
        //         'zone' => 'America/Eirunepe',
        //         'gmt'  => '-7',
        //     ],

        //     [
        //         'id'   => 115,
        //         'zone' => 'America/Havana',
        //         'gmt'  => '-5',
        //     ],

        //     [
        //         'id'   => 116,
        //         'zone' => 'America/Hermosillo',
        //         'gmt'  => '-7',
        //     ],

        //     [
        //         'id'   => 117,
        //         'zone' => 'America/Indiana/Indianapo',
        //         'gmt'  => '-5',
        //     ],

        //     [
        //         'id'   => 118,
        //         'zone' => 'America/Indiana/Knox',
        //         'gmt'  => '-6',
        //     ],

        //     [
        //         'id'   => 119,
        //         'zone' => 'America/Indiana/Marengo',
        //         'gmt'  => '-5',
        //     ],

        //     [
        //         'id'   => 120,
        //         'zone' => 'America/Indiana/Petersburg',
        //         'gmt'  => '-5',
        //     ],
        //     [
        //         'id'   => 121,
        //         'zone' => 'America/Indiana/Tell_City',
        //         'gmt'  => '-6',
        //     ],
        //     [
        //         'id'   => 122,
        //         'zone' => 'America/Indiana/Vevay',
        //         'gmt'  => '-5',
        //     ],

        //     [
        //         'id'   => 123,
        //         'zone' => 'America/Indiana/Vincennes',
        //         'gmt'  => '-5',
        //     ],
        //     [
        //         'id'   => 124,
        //         'zone' => 'AAmerica/Indiana/Winamac',
        //         'gmt'  => '-5',
        //     ],
        //     [
        //         'id'   => 125,
        //         'zone' => 'America/Inuvik',
        //         'gmt'  => '-7',
        //     ],

        //     [
        //         'id'   => 126,
        //         'zone' => 'America/Iqaluit',
        //         'gmt'  => '-5',
        //     ],
        //     [
        //         'id'   => 127,
        //         'zone' => 'America/Jamaica',
        //         'gmt'  => '-5',
        //     ],
        //     [
        //         'id'   => 128,
        //         'zone' => 'America/Juneau',
        //         'gmt'  => '-9',
        //     ],
        //     [
        //         'id'   => 129,
        //         'zone' => 'America/Kentucky/Louisville',
        //         'gmt'  => '-9',
        //     ],
        //     [
        //         'id'   => 130,
        //         'zone' => 'America/Kentucky/Monticello',
        //         'gmt'  => '-5',
        //     ],

        //     [
        //         'id'   => 131,
        //         'zone' => 'America/Kralendijk',
        //         'gmt'  => '-5',
        //     ],
        //     [
        //         'id'   => 132,
        //         'zone' => 'America/La_Paz',
        //         'gmt'  => '-5',
        //     ],
        //     [
        //         'id'   => 133,
        //         'zone' => 'America/Lima',
        //         'gmt'  => '-5',
        //     ],

        //     [
        //         'id'   => 134,
        //         'zone' => 'America/Los_Angeles',
        //         'gmt'  => '-8',
        //     ],
        //     [
        //         'id'   => 135,
        //         'zone' => 'America/Lower_Princes',
        //         'gmt'  => '-5',
        //     ],
        //     [
        //         'id'   => 136,
        //         'zone' => 'America/Maceio',
        //         'gmt'  => '-3',
        //     ],
        //     [
        //         'id'   => 137,
        //         'zone' => 'America/Managua',
        //         'gmt'  => '-6',
        //     ],

        //     [
        //         'id'   => 138,
        //         'zone' => 'America/Manaus',
        //         'gmt'  => '-3',
        //     ],

        //     [
        //         'id'   => 139,
        //         'zone' => 'America/Marigot',
        //         'gmt'  => '-4',
        //     ],

        //     [
        //         'id'   => 140,
        //         'zone' => 'America/Martinique',
        //         'gmt'  => '-4',
        //     ],

        //     [
        //         'id'   => 141,
        //         'zone' => 'America/Matamoros',
        //         'gmt'  => '-6',
        //     ],

        //     [
        //         'id'   => 142,
        //         'zone' => 'America/Mazatlan',
        //         'gmt'  => '-7',
        //     ],

        //     [
        //         'id'   => 143,
        //         'zone' => 'America/Menominee',
        //         'gmt'  => '-6',
        //     ],

        //     [
        //         'id'   => 144,
        //         'zone' => 'America/Merida',
        //         'gmt'  => '-6',
        //     ],

        //     [
        //         'id'   => 145,
        //         'zone' => 'America/Metlakatlae',
        //         'gmt'  => '-6',
        //     ],
        //     [
        //         'id'   => 146,
        //         'zone' => 'America/Mexico_City',
        //         'gmt'  => '-6',
        //     ],

        //     [
        //         'id'   => 147,
        //         'zone' => 'America/Miquelon',
        //         'gmt'  => '-3',
        //     ],

        //     [
        //         'id'   => 148,
        //         'zone' => 'America/Moncton',
        //         'gmt'  => '-4',
        //     ],

        //     [
        //         'id'   => 149,
        //         'zone' => 'America/Monterrey',
        //         'gmt'  => '-6',
        //     ],

        //     [
        //         'id'   => 150,
        //         'zone' => 'America/Montevideo',
        //         'gmt'  => '-3',
        //     ],

        //     [
        //         'id'   => 151,
        //         'zone' => 'America/Montserrat',
        //         'gmt'  => '-4',
        //     ],

        //     [
        //         'id'   => 152,
        //         'zone' => 'America/Nassau',
        //         'gmt'  => '-5',
        //     ],

        //     [
        //         'id'   => 153,
        //         'zone' => 'America/New_York',
        //         'gmt'  => '-5',
        //     ],

        //     [
        //         'id'   => 154,
        //         'zone' => 'America/Nipigon',
        //         'gmt'  => '-7',
        //     ],

        //     [
        //         'id'   => 155,
        //         'zone' => 'America/Nome',
        //         'gmt'  => '-9',
        //     ],
        //     [
        //         'id'   => 156,
        //         'zone' => ' America/Noronha',
        //         'gmt'  => '-6',
        //     ],
        //     [
        //         'id'   => 157,
        //         'zone' => 'America/North_Dakota/Beulah',
        //         'gmt'  => '-6',
        //     ],

        //     [
        //         'id'   => 158,
        //         'zone' => 'America/North_Dakota/Center',
        //         'gmt'  => '-6',
        //     ],

        //     [
        //         'id'   => 159,
        //         'zone' => 'America/North_Dakota/New_Salem',
        //         'gmt'  => '-6',
        //     ],

        //     [
        //         'id'   => 160,
        //         'zone' => 'America/Ojinaga',
        //         'gmt'  => '-7',
        //     ],
        // ]);

    }
}
