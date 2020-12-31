<?php
	
	public function run_stemmer($target, $array, $lang)
	{
		$lenghts = array();
		        
        $exploded = preg_split("/([\ \"\'\.\,\?])/", $target, -1, PREG_SPLIT_DELIM_CAPTURE);
        $requests[] = $exploded;
        $lenghts[] = count($exploded);
        
        foreach($array AS $k => $t)
        {
	        $exploded = preg_split("/([\ \"\'\.\,\?])/", $t, -1, PREG_SPLIT_DELIM_CAPTURE);
	        $requests[] = $exploded;
	        $lenghts[] = count($exploded);
        }
        
	    $descriptorspec = array(0 => array("pipe", "r"), 1 => array("pipe", "w"));
		$process = proc_open('/w/lib/libstemmer/stemwords -l ' . $lang, $descriptorspec, $pipes);
		
		if(is_resource($process))
		{
			$raw_in = '';
			foreach($requests AS $ke => $re)
				foreach($re as $k => $r)
					if($k % 2 == 0)
						$raw_in .= "{$r}\n";
			
			fwrite($pipes[0], $raw_in);
			fclose($pipes[0]);
			
			$raw_out = stream_get_contents($pipes[1]);
			
			fclose($pipes[1]);

			$return_value = proc_close($process);
			
			$lto = 0;
			$lof = 0;
			$raw_out = explode("\n", $raw_out);
			foreach($raw_out as $key => $line)
			{	
				if($lto == 0 && $key > $lof + intval($lenghts[$lto] / 2))
				{
					// If this is the end of the title we can't match an answer's title just yet
					$lto++;
					$lof = $key + 0;
				}
				else if($key > $lof + intval($lenghts[$lto] / 2))
				{
					$stem_map = array();
					
					foreach($title_stemmed as $title_stem)
						foreach($answer_stemmed_bit AS $ask => $asb)
							if($answer_stemmed_bit[$ask] == $title_stem)
								$stem_map[$ask] = 1;
					
					$title_out_tmp = '';
					foreach($answer_stemmed_bit AS $ask => $asb)
					{
						if(isset($stem_map[$ask]))
							$title_out_tmp .= "<b>" . $requests[$lto][$ask * 2] . "</b>";
						else
							$title_out_tmp .= $requests[$lto][$ask * 2];
							
						if(isset($requests[$lto][($ask * 2) + 1]))
							$title_out_tmp .= $requests[$lto][($ask * 2) + 1];
					}
					$array[$lto] = $title_out_tmp;
												
					$answer_stemmed[] = $answer_stemmed_bit;
					$answer_stemmed_bit = array();
					$lto++;
					if($lto == count($lenghts))
						break;
					$lof = $key + 0;
				}
				
				if($lto != 0)
					$answer_stemmed_bit[] = $line;
				else
					$title_stemmed[] = $line;
			}
		}
		
        array_shift($array);
        return $array;
	}
