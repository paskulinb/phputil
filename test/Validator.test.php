#!/usr/bin/php
<?php
require('../Validator.php');

fn_test(
	'is_bool',
	[
        [true, true],
        [false, true],
        [0, false],
        ['str', false],
        [12.43, false],
        [[], false],
        [['-1'], false],
    ]
);

fn_test(
	'is_num',
	[
        [-9.3, true],
        [-1, true],
        [0, true],
        [55, true],
        [12.43, true],
        ['2', true],
        ['-1', true],
        ['', false],
        ['abc', false],
        ['', false],
    ]
);

fn_test(
	'is_id_num',
	[
        [1, true],
        [55, true],
        [0, false],
        [-1, false],
        ['2', true],
        ['-1', false],
        ['', false],
    ]
);

fn_test(
	'is_id_num_array',
	[
        [[1,8,12], true],
        [[], true],
        [[-2,0,4.21], false],
        [[1,8,76.9], false],
        [[3,'a'], false],
        [['', 43], false],
    ]
);

fn_test(
	'is_time',
	[
        ['10:45:12', true],
        ['10:45:12.323', true],
        ['1:5:2', true],
        ['10:45', true],
        ['-10:45', false],
        ['10 45.34', false],
        ['', false],
        ['q:3:r', false],
    ]
);

fn_test(
	'is_date',
	[
        ['2019-2-1', true],
        ['2019-21-17', true],
        ['11-3-2', false],
        ['2020 3 19', false],
        ['', false],
        ['q-3-r', false],
    ]
);

fn_test(
	'is_timestamp',
	[
        ['2019-2-17T23:22:1.3411', true],
        ['2019-2-17 3:2:1.4545', true],
        ['2019-2-17 23:22:1', true],
        ['2019-2-17Y23:22:1', false],
        ['209-2-17T23:22:12.1', false],
        ['2019-2-17 23:12', false],
    ]
);

fn_test(
	'is_timestamp_range',
	[
        [['2019-2-17T0:0:0', '2019-2-17 23:22:1.3411'], true],
        [['', '2019-2-17 23:22:1.3411'], true],
        [[null, '2019-2-17 23:22:1.3411'], true],
        [['2019-2-17T0:0:0', ''], true],
        [['2019-2-17T0:0:0', null], true],
        [['', ''], true],
        [[null, null], true],
        [['2019-2-17T0:0:0'], false],
        [[], false],
        [12, false],
    ]
);

fn_test(
	'is_not_empty_string',
	[
        ['jaz nisem prazen string', true],
        ['', false],
        [234, false],
        [[2,5,'test',90], false],
    ]
);

fn_test(
	'is_string',
	[
        ['jaz sem string', true],
        ['', true],
        [234, false],
        [[2,5,'test',90], false],
    ]
);

fn_test(
	'is_string_alphanumeric',
	[
        ['', true],
        ['alfanum', true],
        ['234', true],
        ['alfanum234', true],
        //['čšžćđČŠŽĆĐ', true],
        ['alfanum 234', false],
        ['(43 str).', false],
        ['2*3+4=3 rezultat', false],
        [234, false],
        [['2',5,'test',90], false],
    ]
);

fn_test(
	'is_string_alpha',
	[
        ['', true],
        ['alfanum', true],
        ['234', false],
        ['alfanum234', false],
        //['čšžćđČŠŽĆĐ', true],
        ['alfanum 234', false],
        ['(43 str).', false],
        ['2*3+4=3 rezultat', false],
        [234, false],
        [['2',5,'test',90], false],
    ]
);

fn_test(
	'is_string_numeric',
	[
        ['', true],
        ['alfanum', false],
        ['234', true],
        ['alfanum234', false],
        //['čšžćđČŠŽĆĐ', true],
        ['alfanum 234', false],
        ['(43 str).', false],
        ['2*3+4=3 rezultat', false],
        [234, false],
        [['2',5,'test',90], false],
    ]
);

fn_test(
	'is_ip_address',
	[
        ['192.0.32.0', true],
        ['1.2.0', false],
        ['1.2.0.1234', false],
        ['1.2 89', false],
        ['127.2.er.4', false],
        ['', false],
    ]
);


echo "Function: apply\n";
$err = Validator::apply(
    [
        's1'   => 12,
        'tsr1' => ['2019-2-1 21:2:3',null],
        'tsr2' => ['2019-2-1 21:2:3'],
        'termin1' => '2019-2-1',
        'termin2' => ['2019-2-1T21:2:3',null],
        'termin3' => [null,'2019-2-1 21:2:3'],
        'termin4' => ['2019-2-1 21:2:3',''],
        'termin5' => ['','2019-2-1 21:2:3'],
        'termin6' => ['',''],
        'termin7' => 'kar nekaj',
        'ip1'  => 132.3,
        'ip2'  => '192.0.32.0',
    ],
    [
        'x'    => ['required', 'is_date'],
        's1'   => ['is_string'],
        'tsr1' => ['is_timestamp_range'],
        'tsr2' => ['is_timestamp_range'],
        'termin1' => ['is_timestamp_range|is_date'],
        'termin2' => ['is_timestamp_range'],
        'termin3' => ['is_timestamp_range'],
        'termin4' => ['is_timestamp_range'],
        'termin5' => ['is_timestamp_range'],
        'termin6' => ['is_timestamp_range'],
        'termin7' => ['is_timestamp_range'],
        'ip1'  => ['is_ip_address', 'required'],
        'ip2'  => ['is_ip_address'],
    ]
);

print_r($err);

echo "Function: apply\n";
$err = Validator::apply(
    [
        'termin1' => '2019-2-1',
        'termin2' => ['2019-2-1 21:2:3',null],
        'termin3' => 'kar nekaj',
    ],
    [
        'termin1' => ['is_timestamp_range|is_date'],
        'termin2' => ['is_date|is_timestamp_range'],
        'termin3' => ['is_timestamp_range|is_date'],
    ]
);

print_r($err);


echo "Function: apply\n";
$err = Validator::apply(
    [
        'choices1' => 'cho1',
        'choices2' => ['cho2',null],
        'choices3' => ['kar nekaj','cho3'],
    ],
    [
        'choices1' => ['is_string_array'],
        'choices2' => ['is_string_array'],
        'choices3' => ['is_string_array'],
    ]
);

print_r($err);


echo "Function: apply\n";
$err = Validator::apply(
	[],
	[
		'ticket_id' => ['is_id_num'],
		'tip_naziv' => ['required', 'is_string'],
		'oseba_id'  => ['required', 'is_id_num'],
		'naslov'    => ['required', 'is_not_empty_string'],
		'opis'      => ['is_string'],
		'termin'    => ['is_timestamp_range|is_date'],
		'izpad_tip_id'  => ['is_id_num'],
		'izpad_obdobje' => ['is_timestamp_range'],
		'lokacija_id'   => ['is_id_num_array'],
		'unit_lokacija_id' => ['is_id_num_array'],
		'servis_lokacija_id'  => ['is_id_num_array'],
	]
);
print_r($err);

echo "Function: apply\n";
$err = Validator::apply(
	[
		"tip_id"=> 2,
		"oseba_id"=> 2,
		"naslov"=> "NASLOV2",
		"opis"=> "oblačno vreme",
		"termin"=> [null, '2019-3-1 9:1:14.96765+01'],
        "izpad_tip_id"=> 1,
		"izpad_obdobje"=> ['2018-05-13T2:07:10', '2019-3-1 9:1:14.96765+01'],
        "lokacija_id"=> [],
        "naprava_lokacija_id"=> [1,3,2],
        "servis_lokacija_id"=> [4]
	],
	[
		'ticket_id' => ['is_id_num'],
		'tip_naziv' => ['required', 'is_string'],
		'oseba_id'  => ['required', 'is_id_num'],
		'naslov'    => ['required', 'is_not_empty_string'],
		'opis'      => ['is_string'],
		'termin'    => ['is_timestamp_range|is_date'],
		'izpad_tip_id'  => ['is_id_num'],
		'izpad_obdobje' => ['is_timestamp_range'],
		'lokacija_id'   => ['is_id_num_array'],
		'unit_lokacija_id' => ['is_id_num_array'],
		'servis_lokacija_id'  => ['is_id_num_array'],
	]
);
print_r($err);


###########################################
function fn_test($fn_name, $test_cases)
{
    $err = false;
	echo "Function: $fn_name\n";
	$ret = null;
	foreach ($test_cases as $case) {
		$ret = Validator::$fn_name($case[0]);
		if ($ret === $case[1]) {}//echo "OK\n";
		else {
            $err = true;
			echo "FAIL: ";
            print_r($case[0]);
			echo "\nRETURNED ";
			var_dump($ret);
		}
	}
    if (!$err) echo "OK\n";
}
