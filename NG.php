<?php

Class NarniaGD
	{
		/*
		Some of this should be done for sustainability & safe deploy environment
		TODO:
		* Configurable function checkKnownPatterns() with possibility to select which patterns count as removable and which as notice
		* Write Log2File (append, roll, overwrite)
		* Silent-notice mode enhancement - logs in file as overwrite, warning email notice when vulnerable things detected
		* File ignore paths
		* Notice ignore (path, line, column, pattern)
		* Apache deploy enviroment test, i.e. safe config rules
			Directory browsing off
		* Nginx deploy enviroment test, i.e. safe config rules
			# nginx configuration
			location ~ \.(txt|dat|log)$ {
				deny all;
			}
		
		Cool ideas, but in near future would not spend time to develop this,
		HIGH-END TODO:
		* Screen output GUI - filterable  by log-level to ease log readability and analyse
		* Screen output GUI - ajax calls to add patterns
		* First-time run feature: random & unpredictable name for DB file (ignore, notice dismiss etc.) for case when location / access rules fail
			If so, name of it would not be quick guessable with GET request
		
		*/
	protected $selfpath = null;
	protected $blacklist = null;
	protected $blackfilelist = null;
	protected $uniquelist = array();
	
	protected $useRealPath = true;
	protected $logLevels = array('verbose','info','notice','error','debug','debug2',-1,0,1,2,3,4);
	protected $modes = array('screen','file','both','silent');
	protected $patterns = array(
			'manyHEX'	=>	'/(\\.{2,3}){20,}/i',
			'manyHEX2'	=>	'/(%.{2,3}){20,}/i',
			'manyNumbers'=>	'/(,[0-9]+){20,}/i',
			'connectedArray'=>'/\[\d\]\.\$/i',
			'connectedArray2'=>'/(?!\;)(\$\S{1,10}\[\d\]\.)+/iU',
			'tonOfSpace'=>	'/(\s){100}/',
			);
	protected $filters = array();
	protected $hazards = array('manyHEX','manyHEX2','manyNumbers','connectedArray2','tonOfSpace','checkAgainstLibrary');
	protected $alert = array('screen','silent');
	
	function __construct() {
		
		date_default_timezone_set('Europe/Riga');
		// ini_set('memory_limit', '512M');
		$this->selfpath = realpath(dirname(__FILE__));
		$this->blacklist = file($this->selfpath.'/blacklist.dat', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
		$this->vulnerable = file($this->selfpath.'/vulnerable.dat', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

		$this->longLine = 140;
		$this->parsedfiles = 0;
		$this->recursive = true;
		$this->mode='screen'; // Output to screen per default
		$this->logLevel=1; //Default log level set to Notice
		
		
		$this->time_start = (float) array_sum(explode(' ',microtime()));
	}
	function logSuccess($root, $string){
		$escaped = preg_replace('/[^A-Za-z0-9_\-]/', '_', $root);
		file_put_contents($this->selfpath.'/main.log',date('Y-m-d G:i').' '.$string.PHP_EOL, FILE_APPEND);
		file_put_contents($this->selfpath.'/root-'.$escaped.'.log',date('Y-m-d G:i').' '.$string.PHP_EOL, FILE_APPEND);
	}
	
	function setLoopLimiter($len, $offset){
		/* 	This function lets shorten script execution
			and allow to filter portion of files
			
			When files from $root are iterated over,
			then they can be skipped by limits set here
			$len is required
			$offset is optional
		*/
		$this->loopStart=($offset ? $offset : 0 );
		$this->loopEnd=$this->loopStart+$len;
	}
	/* Regex patterns */
	public function registerRegexPattern($name,$reg){
		$this->patterns[$name]=$reg;
		return true;
	}
	
	public function getRegexPatterns(){
		return $this->patterns;
	}
	
	public function clearRegexPatterns(){
		$this->patterns=array();
		return true;
	}
	/* Filter functions */
	public function registerFilter($name,$function){
		$this->filters[$name]=$function;
		return true;
	}
	
	public function getFilters(){
		return $this->filters;
	}

	public function clearFilters(){
		$this->filters=array();
		return true;
	}
	
	/* Things to remove */
	public function getHazards(){
		return $this->hazards;
	}
	
	public function registerHazard($name){
		if(function_exists($name) or method_exists($this,$name) or in_array($name, $this->patterns) or in_array($name, $this->filters)){
			if (!in_array($name,$this->hazards)){
				array_unshift($this->hazards,$name);
			}
			return true;
		}
	}
	
	public function clearHazards(){
		$this->hazards=array();
		return true;
	}
	
	/* Things to notice */
	public function getAlerts(){
		return $this->alert;
	}
	
	public function registerAlert($name){
		if(function_exists($name) or method_exists($this,$name) or in_array($name, $this->patterns) or in_array($name, $this->filters)){
			if (!in_array($name,$this->alerts)){
				array_unshift($this->alerts,$name);
			}
			return true;
		}
	}
	
	public function clearAlerts(){
		$this->alert=array();
		return true;
	}
	
	
	
	function constructOptions(){
		if(isset($this->memoryLimit)){
			ini_set('memory_limit', $this->memoryLimit.'M');
		}
		
		if(in_array($this->logLevel,$this->logLevels)){
			$this->logLevel = $this->string2level($this->logLevel);
		}
		
		// Not tested yet:
		// $this->setLoopLimiter($this->loopLenght,$this-loopOffset);	
	}
	
	function checkOption($option, $value){
		$return = false;
		switch ($option) {
			case 'maxDepth':
			case 'longLine':
			case 'memoryLimit':
			case 'loopLength':
			case 'loopOffset':
			case 'loopEnd':
			case 'loopStart':
				if(is_numeric(intval($value)) && floor($value) == $value) $return = true;
				break;
			case 'mode':
			case 'logLevel':
				$t=$option.'s';
				if(in_array($value,$this->$t)) $return = true;
				break;
			case 'useRealPath':
				if(is_bool($value)) $return = true;
				break;
			default:
				echo 'Unknown option '.$option.' and/or '.$value.PHP_EOL;
				$return = false;
		}
		return $return;
	}
	
	public function setOption($options){
		foreach($options as $key => $value){
			if($this->checkOption($key,$value)){
				$this->$key=$value;
			}
		}
		$this->constructOptions();
	}
	
	public function setLogLevel($level){
		$this->logLevel = $this->string2level($level);
	}
	
	function string2level($string){
		$level = 0;
		switch (strtolower($string)) {
			case 'verbose':
				$level = -1;
				break;
			case 'info':
				$level = 0;
				break;
			case 'notice':
				$level = 1;
				break;
			case 'error':
				$level = 2;
				break;
			case 'debug':
				$level = 3;
				break;
			case 'debug2':
				$level = 4;
				break;
			case -1: 
				$level = -1;
				break;
			case 0: 
				$level = 0;
				break;
			case 1: 
				$level = 1;
				break;
			case 2: 
				$level = 2;
				break;
			case 3: 
				$level = 3;
				break;
			case 4: 
				$level = 4;
				break;
			default: echo 'Unknown log level '.$string;
		}
		return $level;
	}
	function level2string($level){
		switch (strtolower($level)) {
			case 'verbose':
				return 'verbose';
				break;
			case 'info':
				return 'info';
				break;
			case 'error':
				return 'error';
				break;
			case 'notice':
				return 'notice';
				break;
			case 'debug':
				return 'debug';
				break;
			case 'debug2':
				return 'debug2';
				break;
			case -1:
				return 'verbose';
				break;
			case 0: 
				return 'info';
				break;
			case 1: 
				return 'notice';
				break;
			case 2: 
				return 'error';
				break;
			case 3: 
				return 'debug';
				break;
			case 4: 
				return 'debug2';
				break;
			default: return 'info';
		}
	}
	
	function e($string, $msglevel){
		/*	Log levels:
		   -1: Verbose	Output message straight to screen, if allowed
			0: Info		Just files processed
			1: Notice	If manual check needed
			2: Error	If error occured and where
			3: Debug	Show all info. Suggested together with $this->setLoopLimiter()
			4: Debug2	Level of badness... Show everything possible to debug NG
			
			Mode options:
			0:	Screen	Output to user
			1:	File	Write to file
			2:	Both	Do both of above
			3:	Silent	Do nothing
		*/
		// If in silent mode, then screw everything
		if ($this->mode!=='silent'){
			
			
				// If required log level is met
			if($this->string2level($msglevel) <= $this->logLevel){
				
				$msg = $string.PHP_EOL;
				if($msglevel>=0){
					$msg = strtoupper($this->level2string($msglevel).': ').$msg;
				}
				switch($this->mode){
					case 'screen':
						echo $msg;
						break;
					case 'file':
						echo 'FILE: '.$msg;
						break;
					case 'both':
						echo $msg;
						break;
					default: break;
				}
			}
		}
	}
	
	function contains($string, array $array){
		$return = false;
		foreach($array as $a) {
			if (stripos($string,$a) !== false) $return = $a.' ';
		}
		return $return;
	}
	
	function cleanit($dirty,$path){
		$clean = $dirty;
		if( preg_match_all('/<\?php(.*)\?>/smiU', $dirty ,$serkocini,PREG_OFFSET_CAPTURE)) {
			// var_dump($serkocini);
			/*
				$serkocini = array( //Results
								array( //Outer Texts
									array( $outerText, $offset),
									array( $outerText, $offset) ...),
								array(
									array( $innerText, $offset),
									array( $innerText, $offset) ...)
							);
			*/
			$last = $serkocini[0][count($serkocini[0])-1];
			
			$trueLastOutter = substr($dirty,$last[1]+strlen($last[0]));
			//offset+len of last
			$trueLastOutter = array($trueLastOutter,$last[1]+strlen($last[0]));
			
			$trueLastInner = substr($trueLastOutter[0],strlen('<?php'));
			$trueLastInner = array($trueLastInner,$trueLastOutter[1]+strlen('<?php'));
			
			if(stripos($dirty,'<?php',$trueLastOutter[1])){
				$serkocini[0][]=$trueLastOutter;
				$serkocini[1][]=$trueLastInner;
			}
			
			foreach($serkocini[1] as $key => $chunk){
				$match = $this->checkKnownPatterns($chunk[0],$chunk[1],$path);
				if($match){
					$this->e('File has been cleaned by \''.$match.'\' regex/function',1);
					
					$start = $serkocini[0][$key][1];
					$len = strlen($serkocini[0][$key][0]);
					$clean = substr_replace($dirty,'',$start,$len);
					$clean = $this->cleanit($clean,$path);
					break;
				}
			}
		}
		
		if($this->cleanLineBreaks){
				$wide = preg_replace('/((\n|\R){2,})/',PHP_EOL.PHP_EOL,$clean);
				if($wide && $wide<>$clean){
					$clean = $wide;
					$this->e('File has been compressed from odd line breaks',3);
				}
		}
		return $clean;
	}
	
	
	function checkKnownPatterns($string,$offset,$path){
		
		foreach($this->hazards as $pattern){
			/*
				So... i get $pattern name, which could be anything:
				0: I check whether regex pattern exists with given name
				1: Maybe is it myCustomFilter with this name?
				2: Or last chance find it between built-in functions
				4: If not, then I will debug it
			*/
			// echo $pattern.PHP_EOL;
			if(array_key_exists($pattern,$this->patterns)){
				$this->e('Pattern \''.$pattern.'\' recognized as regex pattern!',4);
				if(preg_match_all($this->patterns[$pattern],$string)){
					$return = $pattern;
				}
			}elseif(function_exists($pattern)){
				$this->e('Pattern \''.$pattern.'\' recognized as external hook!',4);
				$func = $pattern($string,$offset,$path);
				if ($func){
					$return = $pattern;
				}
			}elseif(method_exists($this,$pattern)){
				$this->e('Pattern \''.$pattern.'\' recognized as builtin hook!',4);
				$func = $this->$pattern($string,$offset,$path);
				if ($func){
					$return = $pattern;
				}
			}else {
				$this->e('Pattern \''.$pattern.'\' is not recognized as regex or function!',2);
			}
		}
			
		return $return;
	}
	
	function checkAgainstLibrary($string){
		
		$return = null;
		foreach ($this->blacklist as $bad){
			if(strpos($string,$bad)>-1){
				$this->e('Sample matches against \''.htmlentities($bad).'\'',3);
				$return=true;
				break;
			}
		}
		return $return;
	}

	function removeComments($string){
		// First will match multi-line comments OR it will match single line comments, both fill $serkocini with valid offsets etc.
		if(preg_match('/\/\*(.*)\*\//sU', $string, $serkocini,PREG_OFFSET_CAPTURE) or preg_match('/\/\/(.*)/', $string, $serkocini,PREG_OFFSET_CAPTURE)){

			$comlines = explode("\n", $serkocini[0][0]);
			$count = count($comlines);
			$start = $serkocini[0][1];
			$len = strlen($serkocini[0][0]);
			// echo 'Linijas '.$count.' Start '.$start.' Len '.$len.PHP_EOL;
			// $this->e('Replace block comment '.$count.' lines',3);
			$string = substr_replace($string,str_repeat(PHP_EOL,$count-1),$start,$len);
			
			$string = $this->removeComments($string);
			
		}
		// $comlines = explode("\n", $serkocini[0][0]);
		return $string;
	}
	
	function checkVulnerable($string){
		// $this->getUnique($strings[0],$path);
		$string = $this->removeComments($string);
		// var_dump(htmlentities($string));
		$ln = 0;

		$return = array();
		$strings = explode("\n", $string);
		foreach($strings as $line){
			$ln += 1;
			foreach($this->vulnerable as $word){
				$pos = strpos($line, $word);
				if($pos>-1){
					$pos +=1;
					$this->e('found \''.$word.'\' on line '.$ln.' column '.$pos,1);
					$return[] = array( $word, $ln, $pos	);
				}
			}
			if(strlen($line)>$this->longLine){
				$this->e('Extraordinary long line '.$ln.' with '.strlen($line).' chars, starts with '.substr(htmlentities($line),0,$this->longLine).'...',1);
			}
		}
		
		
		return $return;
	}
	
	function getUnique($string, $path){
		$strings = explode("\n", $string);
		if (!in_array($strings[0], $this->uniquelist)) {
			$this->uniquelist[$strings[0]]=array($strings[0],$path);
			$this->e('First unique '.$path.' '.htmlentities(substr($strings[0],0,100)).'...<br>');
		}
	}
	
	function parseFile($fpath){
		// $this->e('Input file '.$fpath);
		$dirty=file_get_contents($fpath);
		$clean = $this->cleanit($dirty,$fpath);
		if ($dirty<>$clean){
			if($this->logLevel!==3){
				$saved = file_put_contents($fpath,$clean);
				$this->e('N!B! Skipping SAVE for \''.$fpath.'\'!!!',3);
			}
			$this->e('Cleaned it and saved: '.json_encode($saved).PHP_EOL);
		}
		if ($this->logLevel>0){
			// $this->checkVulnerable($clean);
			$this->getUnique($clean,$fpath);
		}
	}
	
	public function cleanFiles($root){
			$this->e('<pre>Narnia Guardian',-1);
			$this->e('Log level set to \''.strtoupper($this->level2string($this->logLevel)).'\'',-1);
			$this->e('Mode set to \''.strtoupper($this->mode).'\'',-1);
			$this->e('Inspecting file/\'s under \''.$root.'\'',-1);
			if(isset($this->loopStart)){
				$this->e('Iterator restricted loop from '.$this->loopStart.' to '.$this->loopEnd.PHP_EOL,-1);
				
			}
			
			
			$count= $this->parsedfiles;
			
			if(!is_file($root)){
				
				$loop = 0;
				$iter = new RecursiveIteratorIterator(
						new RecursiveDirectoryIterator($root, RecursiveDirectoryIterator::SKIP_DOTS),
						RecursiveIteratorIterator::SELF_FIRST,
						RecursiveIteratorIterator::CATCH_GET_CHILD // Ignore "Permission denied"
					);

				if(isset($this->maxDepth)){
					$iter->setMaxDepth($this->maxDepth);
					$this->e('Max recursive depth is '.$this->maxDepth);
				}

				foreach ($iter as $path) {
					if ($this->develop && $path->getExtension()!=='php') {
						unlink($path);
					}
					if($this->loopStart){
						if ($this->loopEnd<$loop){
							$this->e('Ending cycle because of loopEnd at '.$this->loopEnd);
							break;
						}
						if ($this->loopStart>$loop){
							$loop++;
							continue;
						}
					}
					if ($path->getExtension()=='php') {
						
						$fpath = $path;
						if($this->useRealPath){
							$fpath = realpath($fpath);
						}
						// Important, magic ported to core-single function
						$this->parseFile($fpath);
						$this->e('',-1);
						$count++; $loop++;
					}
				}
			
			} elseif(pathinfo($root)['extension']=='php'){
				$this->parseFile($root);
			}
			$this->parsedfiles += $count;
	}
	
	function __destruct() {
		$time_end = (float) array_sum(explode(' ',microtime()));
		$time_diff = "Processing time: ". sprintf("%.4f", ($time_end-$this->time_start))." seconds";
		
		$this->e('We have checked '.$this->parsedfiles.' files!');
		$this->e($time_diff);
		$output = null;	$fileput = null;
		foreach ($this->uniquelist as $line){
			$output.=$line[0].PHP_EOL;
			$fileput.=$line[1].PHP_EOL;
		}
		file_put_contents($this->selfpath.'/uniqueList.dat',$output);
		file_put_contents($this->selfpath.'/uniqueFileList.dat',$fileput);
	}
}