<?php

# CONSTANTES

define('CHROME',1);
define('MULTI',2);
define('PNG',4);

# FONCTIONS COMMUNES
class Stegano {

	function imagergbcolorat($im, $_x, $_y) {
		$rgbdest = imagecolorat($im, $_x, $_y);
		return array(($rgbdest >> 16) & 0xFF,($rgbdest >> 8) & 0xFF,$rgbdest & 0xFF);
	}

	function asc2bin($temp) {
		$len = strlen($temp);
		for ($i=0; $i<$len; $i++) $data.=$this->hexbin(sprintf("%02x",ord(substr($temp,$i,1))));
		return $data;
	}

	function hexbin($hex){
		$bin='';
		for($i=0;$i<strlen($hex);$i++) $bin.=str_pad(decbin(hexdec($hex{$i})),4,'0',STR_PAD_LEFT);
		return $bin;
	} 

	# GENERATEUR DE MATRICE

	function matrice() { 	
		if(strlen($this -> binaryString)%8 OR !$this -> binaryString) return false;
		$bits = strlen($this -> binaryString)/3;
		$bits += (strlen($this -> binaryString)%3);
		$bits += 48; // 2 separateur de 24 bits;
		do $separateur = $this->asc2bin(chr(rand(0,255)).chr(rand(0,255)).chr(rand(0,255)));
		while(strpos($this -> binaryString,$separateur)!==false);
		$binaryString = $separateur.($this -> binaryString).$separateur;
		$i = $area = 1; // intialisation
		do {
			$width = ceil($i*$this -> ratio);
			$height = $i++;
		}
		while($height * $width < $bits);
		
		if($height < $this -> height) $height = $this -> height;
		if($width < $this -> width) $width = $this -> width;
		
		$area = $height * $width;
		
		$binaryString = str_pad($binaryString, $area * 3, "0");  
		return Array("w" => $width,"h" => $height,"s"=>$binaryString,"l"=>$bits*3);
	}

	# CLASS
	
	function Stegano() {
		$this -> setData("Content encoded into image");
		$this -> setOrigin = CHROME;
		$this -> setColor(255,128,200);
		$this -> setRatio(1);
		$this -> imSource = false;
	}
	
	function setOrigin($origin=CHROME) {
		$this -> origin = $origin;
		if(file_exists($origin)) {
			$this -> imSource = imagecreatefromjpeg($origin);
			$image_width = imagesx($this -> imSource);
			$image_height = imagesy($this -> imSource);
			$this -> ratio = $image_width/$image_height;
		}
		else $this -> imSource = false;
		return $this -> origin;
	}
	
	function setData($data=""){
		$this -> data = $data;
		$this -> binaryString = $this -> asc2bin(gzcompress($data));
		return $this -> binaryString;
	}
	
	function setColor($red=128,$green=128,$blue=128) {
		$this -> red = $red;
		$this -> green = $green;
		$this -> blue = $blue;
		return Array($red,$green,$blue);
	}
	
	function keepSize() {
		if(!$this -> imSource) return false;
		return $this -> setSize(imagesx($this -> imSource),imagesy($this -> imSource));
	}
	
	function setSize($width=1,$height=1) {
		$this -> width = $width;
		$this -> height = $height;
		$this -> ratio = $width/$height;
		return Array($this -> width,$this -> height,$this -> ratio);		
	}
	
	function setRatio($ratio=1) {
		$this -> width = 1;
		$this -> height = 1;
		$this -> ratio = $ratio;	
	}

	function draw($output=false) {

		$matrice = $this -> matrice();

		$width = $matrice[w];
		$height = $matrice[h];
		$binaryString = $matrice[s];
		$binaryDataLenght = $matrice[l];
		
		$destination = imagecreatetruecolor($width, $height);
		
		if($this -> origin==CHROME) {
			$color = imagecolorallocate($destination,$this -> red,$this -> green,$this -> blue);
			imagefill($destination, 0, 0, $color);
		}
		elseif($this -> imSource) {
			imagecopyresampled($destination,$this -> imSource,0,0,0,0,$width,$height,imagesx($this -> imSource),imagesy($this -> imSource));
		}

		$array = Array();
		$writtenOffset = 0;
		
		for($_y=0;$_y<$height;$_y++) {
			$_x=-1;
			$line = substr($binaryString,$_y*$width*3,$width*3);
			if($writtenOffset>$binaryDataLenght && $this -> origin != MULTI) break;
			$pixels = str_split($line,3);
			foreach($pixels as $pixel) {
				$_x++;
				if($this -> origin == CHROME) { // Monocrhome Output
					$r = $this -> red;
					$g = $this -> green;
					$b = $this -> blue;
				}
				elseif($this -> imSource) { // Image masking
					$colors = $this->imagergbcolorat($destination, $_x, $_y);
					$r = $colors[0];
					$g = $colors[1];
					$b = $colors[2];
				}
				elseif($this -> origin == MULTI) { // Multi
					$r = mt_rand(0,255);
					$g = mt_rand(0,255);
					$b = mt_rand(0,255);
				}
				
				// Modification des 3 LSB (Last Signifiant Bit)
				
				$r = bindec(substr(decbin($r),0,-1).abs($pixel{0}));
				$g = bindec(substr(decbin($g),0,-1).abs($pixel{1}));
				$b = bindec(substr(decbin($b),0,-1).abs($pixel{2}));
				
				$color = imagecolorallocate($destination,$r,$g,$b);
				imagesetpixel($destination,$_x,$_y,$color);
			}
			$writtenOffset+=3;
		}
		$this -> imDestination = $destination;
		if($output==PNG) {
			header("Content-type: image/png");
			imagepng($this -> imDestination);
		}
		elseif($output) {
			imagepng($this -> imDestination,$output);
		}
		return true;
	}
	
	function getImage() {
		return $this -> imDestination;
	}

	# DECODAGE

	function decodeImageFile($file) {

		if(file_exists($file)) return $this->decodeImageRessource(imagecreatefrompng($file));
		else die("fichier introuvable");
	}

	function decodeImageRessource($im) {

		// Initialisation des variables
		$temp = "";
		$separateur = "";
		$break = false;
		$currentOctect = Array();
		$i=1;
		// Lecture du separateur
		
		while(strlen($separateur)<8) {
			for($_x=0;$_x<8;$_x++) {
				foreach($this->imagergbcolorat($im, $_x, $i++) as $color) {
					if(strlen($separateur)<8) {
						$separateur .= substr(decbin($color),-1,1);
					}
				}
			}
		}
		
		echo $separateur;
		
		// Découpage
		$splitedSeparateur = str_split($separateur,8);
		$currentSeparateurPart = 0;

		for($_y=0;$_y<imagesy($im);$_y++) { 
			for($_x=0;$_x<imagesx($im);$_x++) {
				// lecture d'un pixel
				foreach($this->imagergbcolorat($im, $_x, $_y) as $color) {
					$bit = substr(decbin($color),-1,1); // LSB
					$currentOctect[] = $bit;
					if(count($currentOctect)==8) { // un octet complet à été detecté
						if(join($currentOctect)==$splitedSeparateur[$currentSeparateurPart]) {
							$currentSeparateurPart++;
							if($currentSeparateurPart==3) {
								if($_x!=7 || $_y!=0) {// sedonde occurrence du separateur
									$break = true;
									break;
								}	
								// premiere occurrence du separateur
								// remise à zero du chercheur
								$currentSeparateurPart=0; 
							}
						}
						// le separateur n'a pas été detecter au
						// complet, remise à zero du chercheur
						else $currentSeparateurPart=0;
						// démarage d'un nouvel Octet
						$currentOctect = Array();
					}
					// Stokage du bit
					$temp .= $bit;
				}
				// fin de boucle
				if($break) break;
			}
			if($break) break;
		}
		$temp = str_replace($separateur,null,$temp);
		$len = strlen($temp)-24;
		for ($i=0;$i<$len;$i+=8) {
			$sub_bin = substr($temp,$i,8);
			if(strlen($sub_bin)<8) break;
			$data.=chr(bindec($sub_bin));
		}
		return gzuncompress($data);
	}

}

?>