<?php
/** Pure finite scenario names; safe during ordinary PHPUnit discovery. */
function policy_bootstrap_scenarios() {
	$cases=array('positive','positive-current','positive-observer','positive-ambient','default','bad-host','bad-port','bad-table','bad-database','bad-user','bad-password','bad-core','bad-charset','bad-collate','bad-core-source','bad-driver-source','bad-cache-source','external-cache','multisite','setup-mode','legacy','driver-selection','dropin','all','incompatible_sql_modes','unknown-observer','observer-source','observer-autoload','bad-client','bad-server','allocate-false','allocate-throw','allocate-alias','option-false','option-throw','option-warning','connect-false','connect-throw','connect-warning','connect-error','late-hook','late-global','late-handle','late-budget','late-clock','during-prepare','reentry','out-of-order','close-false','close-throw','close-warning','remove-false','remove-throw','close-and-remove','free-throw');
	foreach(array('identity','limits','mode','session','table','columns','indexes') as $step)foreach(array('false','throw','warning','error') as $fault)$cases[]=$step.'-'.$fault;
	foreach(array('identity','mode','session','table','columns','indexes') as $step)foreach(array('bad','extra','bad-row','oversize') as $fault)$cases[]=$step.'-'.$fault;
	foreach(array('db','ac','tx','statement_limit','row_limit','metadata_limit','client_charset','connection_charset','result_charset','collation','mode') as $field)$cases[]='readback-'.$field;
	foreach(array('name','type','nullable','key_kind','extra') as $field)$cases[]='column-'.$field;
	foreach(array('name','non_unique','sequence','column_name','prefix_length') as $field)$cases[]='index-'.$field;
	foreach(array('init','charset','mode-helper') as $step)foreach(array('throw','warning','error') as $fault)$cases[]=$step.'-'.$fault;
	$cases[]='object-reentry';
	return $cases;
}
