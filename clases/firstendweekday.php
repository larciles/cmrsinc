<?php 

/**
 * 
 */
class Firstendweekday
{
	private $arrayDate = array();

	function __construct()
	{
		
	}

	public function getDates($date){
		self::calDates($date);
		return $this->arrayDate;
	}

	public function setDates($date){
		$this->arrayDate =$date;
	}


	private function calDates($date){
		$arrayMain = array();
		$char="/";
		$slashes_or=strpos($date,'/');
		
		if (!$slashes_or) {
			$char="-";
		}
		$datearr=explode($char, $date); 
		$year=$datearr[2];
		$month=$datearr[0];
		$day=$datearr[1];

		$semana=date("W",mktime(0,0,0,$month,$day,$year));

		$diaSemana=date("w",mktime(0,0,0,$month,$day,$year));

		if($diaSemana==0)
		    $diaSemana=7;

		$primerDia=date("m/d/Y",mktime(0,0,0,$month,$day-$diaSemana+1,$year));
		$segundoDia=date("m/d/Y",mktime(0,0,0,$month,$day-$diaSemana+2,$year));
		$tercerDia=date("m/d/Y",mktime(0,0,0,$month,$day-$diaSemana+3,$year));
		$cuartoDia=date("m/d/Y",mktime(0,0,0,$month,$day-$diaSemana+4,$year));
		$quintoDia=date("m/d/Y",mktime(0,0,0,$month,$day-$diaSemana+5,$year));
		$sextoDia=date("m/d/Y",mktime(0,0,0,$month,$day-$diaSemana+6,$year));
		$ultimoDia=date("m/d/Y",mktime(0,0,0,$month,$day+(7-$diaSemana),$year));

		$arrayTmp = array('first'  =>$primerDia
						  ,'second'=>$segundoDia
						  ,'third' =>$tercerDia
						  ,'fourth'=>$cuartoDia
						  ,'fifth' =>$quintoDia
						  ,'sixth' =>$sextoDia
						  ,'last'  =>$ultimoDia
						  ,'week'  =>$semana);

        $arrayMain[]=$arrayTmp;
        self::setDates($arrayMain);
	}
}

 ?>