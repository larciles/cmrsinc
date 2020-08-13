<?php 

/**
 * 
 */
class Firstendweekdaywname
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
     
        $days_name=["","Lunes","Martes","Miercoles","Jueves","Viernes","Sabado"];
        $FirstDay = date("m/d/Y", strtotime('sunday last week'));  
        $LastDay = date("m/d/Y", strtotime('sunday this week'));   


        if($date > $FirstDay && $date < $LastDay) {

        	$array1 = array();
        	for ($i=0; $i <8 ; $i++) { 
        	    $dia=	date('N', strtotime($date));
        	    if ($dia!=7) {
    				$char="/";
					$slashes_or=strpos($date,'/');
					
					if (!$slashes_or) {
						$char="-";
					}
					$datearr=explode($char, $date); 
					$year=$datearr[2];
					$month=$datearr[0];
					$day=$datearr[1];

					array_push($array1,date("m/d/Y",mktime(0,0,0,$month,$day,$year) ));

					$semana=date("W",mktime(0,0,0,$month,$day,$year));					
				}   

				$date=date('m/d/Y', strtotime($date. ' + 1 days'));     	    
        	}

			$arrayTmp = array(
				   'first' =>$array1[0]
				  ,'second'=>$array1[1]
				  ,'third' =>$array1[2]
				  ,'fourth'=>$array1[3]
				  ,'fifth' =>$array1[4]
				  ,'sixth' =>$array1[5]
				  ,'last'  =>$array1[6]
				  ,'week'  =>$semana);

			$arrayMain[]=$arrayTmp;
			self::setDates($arrayMain);        			
	
		
    	} else {


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
}

 ?>