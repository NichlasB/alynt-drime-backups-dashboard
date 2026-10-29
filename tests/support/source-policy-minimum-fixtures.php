<?php
/** Synthetic byte fixtures only; never an executable or archive on disk. */
function minimum_test_entry($path, $size=1, $kind='file') {
	return array('path'=>$path,'kind'=>$kind,'size'=>$size,'compressed'=>$size);
}
function minimum_test_pe($dll=false) {
	$bytes=str_repeat("\0",1024);
	foreach(array(0=>'MZ',60=>pack('V',128),128=>"PE\0\0",132=>pack('vvVVVvv',0x8664,1,0,0,0,240,$dll?0x2002:2),152=>pack('v',0x20b),260=>pack('V',16),272=>pack('VV',0x1000,40),392=>str_pad('.rdata',8,"\0").pack('VVVV',512,0x1000,512,512),512=>pack('VVVVV',0x1080,0,0,0x1040,0x1090),576=>"KERNEL32.dll\0") as $offset=>$value) $bytes=substr_replace($bytes,$value,$offset,strlen($value));
	return $bytes;
}
function minimum_test_lines($run,$image) {
	$prefix='ADBM0|'.$run.'|'.$image.'|';
	return array($prefix."inventory|2\n",$prefix."pe|3|x64|PE32+\n",$prefix."source|cc101b64012b16d087780657a2b828ccd7794a63|a3bd7a4074a4c95c8af3b969346726273d68e167\n",$prefix."profile|7.4.33|7.4.33|6.0|0|no\n",$prefix."closed|no-extraction|no-execution\n");
}
