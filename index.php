<?php
	error_reporting(E_ERROR);
	function rumbins(){
		//do nothing right now
		return false;
	}
	
	// Path to Guardian
	include '../../NG/NG.php';
	$Guard = new NarniaGD;
	
	//Set options in a bunch
	$Guard->setOption(array(			//Default (you can not set it):
		'logLevel'		=>	'error',	// Info
		// 'mode'			=>	'silent',	// Not developed yet
		'longLine'		=>	800,		// 140
		'memoryLimit'	=>	512, 		// Check server environment
		// 'maxDepth'		=>	0,			// -1, how deep through folder levels it will go
		// 'loopLength'	=>	,
		// 'loopOffset'	=>	,
		'loopStart'		=>	5,
		'loopEnd'		=>	5,
		// 'useRealPath'	=>	true, 	// true
		// 'nonExisting'=>'function'
	));
	
	
	/*
		Some testing functions, you can use them while testing on copy of files, because them will
		somehow corrupt, delete or skip them.
	*/
	// $Guard->cleanLineBreaks(true);
	// $Guard->setLoopLimiter(10);
	
	// $Guard->develop = true; //Will delete all other files than PHP for iterator optimization USE WITH SUPER CAUTION
	
	
	/*
		Advanced usage - if above options are not enough, you can register your own Regular Express
		pattern. Regex patterns could be overridden and I will be creative and just slightly change
		existing pattern:
	*/
	$Guard->registerRegexPattern('tonOfSpace','/(\s){99}/');
	
	
	/*
		Even further - you can register custom filter function, which tells whether given $string
		is good or bad. As bonus, you can use current $offset (in file) and $path, but don't rely
		on $offset as constant, because if there is more than one bad <?php ... ?> section, after
		first clean-up, $offset for rest will change.
		
		Here will show up even last single <?php... tag
		
		To decide if given string should be removed - return true or false from your function:
		You got only one job, don't f*ck up everything :)
	*/
	function myCustomFilter($string,$offset,$path){
		
		
		
		//Non-boolean values as strings, numbers etc. will be treated as true...
		return false;
	}
	
	/*
		Your function will be registered as first in check order
		To activate your custom callback, just
	*/
	$Guard->registerRemoveFilter('myCustomFilter');
	/*
		Magic goes off here!
	*/
	
	// Single file or root of files
	$Guard->cleanFiles('../../Pilskalns.lv/ng/index.php');