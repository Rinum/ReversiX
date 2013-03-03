<?php
class User extends AppModel{
	public $name = 'User';
	
	public $vowels = array('a','e','i','o','u');
	public $consonants = array('b','c','d','f','g','h','j','k','l','m','n','p','q','r','s','t','v','w','x','y','z');
	public $patterns = array('CVC','CVCVV','CVC CVCVV','CVCVCCVC','CVVCVC','VCCVC','CVCC','CVVCVC VCCVC');
	
	public function rand_vowel(){
		return $this->vowels[array_rand($this->vowels, 1)];
	}

	public function rand_consonant(){
		return $this->consonants[array_rand($this->consonants, 1)];
	}
	
	public function generate_username(){
		$pattern = str_split($this->patterns[array_rand($this->patterns, 1)]);
		
		$name = '';
		
		foreach($pattern as $l){
			switch($l){
				case 'C':
					$name .= $this->rand_consonant();
					break;
				case 'V':
					$name .= $this->rand_vowel();
					break;
				case ' ':
					$name .= ' ';
					break;
			}
		}
		
		return ucwords($name);
	}
	
	public function romanize($integer){
		$table = array('M'=>1000, 'CM'=>900, 'D'=>500, 'CD'=>400, 'C'=>100, 'XC'=>90, 'L'=>50, 'XL'=>40, 'X'=>10, 'IX'=>9, 'V'=>5, 'IV'=>4, 'I'=>1); 
		$return = ''; 
		while($integer > 0) {
			foreach($table as $rom=>$arb) {
				if($integer >= $arb) {
					$integer -= $arb; 
					$return .= $rom; 
					break; 
				} 
			} 
		} 

		return $return; 		
	}
}
?>