<?php
/** Source-derived API DATA from hash-reviewed pinned files, not executed core evidence.
 * Both parsers return four positional fields; 6.0 keeps the regex port string,
 * 7.1.3 uses absint. No parser implementation, runtime loading, IO or native grant.
 */
function policy_parser_tuple_data() {
	return array(
		'6.0'=>array(
			'commit'=>'cc101b64012b16d087780657a2b828ccd7794a63',
			'file'=>'wp-db.php',
			'sha256'=>'f07bc6403ab6db30195cca8e47a384de11d582a66a64cb50449d4335edcc24cf',
			'tuples'=>array(
				'127.0.0.1:3306'=>array('127.0.0.1','3306',null,false),
				'127.0.0.1:10004'=>array('127.0.0.1','10004',null,false),
				'127.0.0.1:1'=>array('127.0.0.1','1',null,false),
				'127.0.0.1:65535'=>array('127.0.0.1','65535',null,false),
				'[::1]:10004'=>array('::1','10004',null,true),
				'localhost:/tmp/owned.sock'=>array('localhost',null,'/tmp/owned.sock',false),
			),
		),
		'7.1.3'=>array(
			'commit'=>'fc9832bef919c2a08541248db23af90d14ffb082',
			'file'=>'class-wpdb.php',
			'sha256'=>'e15403e90032dd0508811301505e491d4212de5152581a98f51b744a1a3903bd',
			'tuples'=>array(
				'127.0.0.1:3306'=>array('127.0.0.1',3306,null,false),
				'127.0.0.1:10004'=>array('127.0.0.1',10004,null,false),
				'127.0.0.1:1'=>array('127.0.0.1',1,null,false),
				'127.0.0.1:65535'=>array('127.0.0.1',65535,null,false),
				'[::1]:10004'=>array('::1',10004,null,true),
				'localhost:/tmp/owned.sock'=>array('localhost',null,'/tmp/owned.sock',false),
			),
		),
	);
}
