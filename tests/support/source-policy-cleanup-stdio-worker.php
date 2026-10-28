<?php
/** Fixed socket-free bounded-pipe fixture, never native sessions or WordPress. */
if (defined('ABSPATH')) { return; }
if(PHP_SAPI!=='cli'||count($argv)!==3||$argv[1]!=='--private-socket-free-stdio'||extension_loaded('mysqli')||extension_loaded('pdo_mysql'))exit(2);
if('overflow'===$argv[2]){echo str_repeat('x',16385);exit(0);}
if('stderr'===$argv[2]){fwrite(STDERR,str_repeat('private-secret-probe',400));exit(0);}
if('mysql-error-fragments'===$argv[2]){fwrite(STDERR,'ERROR 10');fflush(STDERR);usleep(20000);fwrite(STDERR,"64 (42000) private-secret SQL\n");exit(1);}
if('mysql-error-eof'===$argv[2]){fwrite(STDERR,'ERROR 1045 (28000)');exit(1);}
if('stderr-prefix-eof'===$argv[2]){fwrite(STDERR,'ERROR 10');exit(1);}
if('stdout-partial-stderr'===$argv[2]){fwrite(STDERR,'ERROR 10');fflush(STDERR);usleep(100000);echo "ready\n";fflush(STDOUT);usleep(100000);fwrite(STDERR,"64 (42000) private-secret SQL\n");exit(1);}
if('evidence-error'===$argv[2]){$line='ADBC-DIAG1|missing-table|'.getmypid()."|1|1,0,4,2,2,0,0,0,0,0,0|1,0,4,2,2,0,0,0,0,0,0|1,unavailable,0,1,1,1,1,0,1,1,1,1,1,1,closed,0,4,0,0,0,12\n";fwrite(STDERR,substr($line,0,40));fflush(STDERR);usleep(20000);fwrite(STDERR,substr($line,40)."private-secret SQL\n");exit(1);}
if('exit1'===$argv[2])exit(1);
if(!in_array($argv[2],array('normal','hold'),true))exit(2);
echo "ready\n";fflush(STDOUT);
$ack=fgets(STDIN,128);
if('hold'===$argv[2])exit(false===$ack?0:1);
if("ACK\n"!==$ack)exit(1);
echo "done\n";
