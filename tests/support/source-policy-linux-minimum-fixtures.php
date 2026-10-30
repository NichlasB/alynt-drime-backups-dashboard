<?php
/** Independent synthetic DATA; no observations, grants, IO or runtime loading. */
function linux_minimum_tuple_fixture() {
	return array(
		'format'=>'ADBLMT1','platform'=>'Linux','arch'=>'x86_64','php'=>'7.4.33','client'=>'mysqlnd 7.4.33','core'=>'6.0',
		'commit'=>'cc101b64012b16d087780657a2b828ccd7794a63','tree'=>'a3bd7a4074a4c95c8af3b969346726273d68e167',
		'runtime_image'=>'sha256:08a1cb1fbe022cd08d5d19e6f95ab549194927959a297da41590d4f2d884f7b4',
		'server_image'=>'sha256:161be354206906ea8584929bde5ac59cdce0770fbfd5dd76131429f7dd80aab5',
		'server'=>'10.11.18-MariaDB-ubu2204','transport'=>'tcp_loopback','host'=>'127.0.0.1','port'=>3306,'socket'=>null,'db_host'=>'127.0.0.1:3306','initial_mode'=>0,
	);
}
function linux_minimum_correlation_fixture() {
	return array('event'=>'tuple-data','head'=>str_repeat('a',40),'source_image'=>str_repeat('b',64),'run'=>str_repeat('c',32),'pid'=>123,
		'tuple_hash'=>hash('sha256',json_encode(linux_minimum_tuple_fixture(),JSON_UNESCAPED_SLASHES)));
}
