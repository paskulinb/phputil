#!/usr/bin/php
<?php
include("../Sql.php");
$data = [
	"num_int" => 22,
	"num_num" => 33.5,
	"num_float" => 11.4325,
	"ts1" => '2019-9-5 12:45:49',
	"time1" => '10:5:00',
	"range1" => [40,55],
	"range2" => [null,600],
	"range3" => [-120,null],
	"range4" => ['2019-9-5 12:45:49','2019-9-12 19:10:00'],
	"range5" => ['2019-9-5',null],
	"range6" => [null, '2020-4-19'],
	"bool1" => true,
	"bool2" => false,
	"str_arr" => ['ena','dve','tri'],
];

$conv = [
	"num_int" => ['tabx', null, Sql::T_INTEGER],
	"num_num" => ['tabY', 'numericanix', Sql::T_NUMERIC],
	"num_float" => [null, null, Sql::T_FLOAT],
	"ts1" => [null, null, Sql::T_TIMESTAMP],
	"time1" => [null, null, Sql::T_TIME],
	"range1" => [null, null, Sql::T_RANGE],
	"range2" => [null, null, Sql::T_RANGE],
	"range3" => [null, null, Sql::T_RANGE],
	"range4" => [null, null, Sql::T_RANGE],
	"range5" => [null, null, Sql::T_RANGE],
	"range6" => [null, null, Sql::T_RANGE],
	"bool1" => [null, null, Sql::T_BOOLEAN],
	"bool2" => [null, null, Sql::T_BOOLEAN],
	"str_arr" => [null, null, Sql::T_TEXT_ARRAY],
];

$CLLC = Sql::collect($data, $conv);
print_r($CLLC);
echo "\n\nJoin FIELDS:\n";
print_r(Sql::join_fields($CLLC));
echo "\n\nJoin VALUES:\n";
print_r(Sql::join_values($CLLC));
echo "\n\nJoin PAIRS/SET:\n";
print_r(Sql::join_pairs($CLLC));
echo "\n\nJoin WHERE:\n";
print_r(Sql::join_where($CLLC));
echo "\n";
