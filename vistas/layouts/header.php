<?php
error_reporting(E_ERROR | E_WARNING | E_PARSE);
$actualDir=getcwd();

  $url  = @( $_SERVER["HTTPS"] != 'on' ) ? 'http://'.$_SERVER["SERVER_NAME"] :  'https://'.$_SERVER["SERVER_NAME"];
  $url2 = ( $_SERVER["SERVER_PORT"] !== 80 ) ? ":".$_SERVER["SERVER_PORT"] : "";
  $url3 = $_SERVER["REQUEST_URI"];

 if("/vav/main.php"==$url3){
     require_once('models/user_model.inc.php');
 }else if("/vav/vistas/pedidos/"==$url3){
     require_once('../../models/user_model.inc.php');
 }
//$curserver="192.130.74.2:8080";
require_once dirname(__FILE__)."/../../db/config.php";
$curserver=SERVER_HOST; 

session_start();
if(!isset($_SESSION['username'])){
    header("Location:vistas/login/login.php");
}else{
    $user=$_SESSION['username'];
    $ip=$_SERVER['HTTP_CLIENT_IP'];
}
?>
    <div id="wrapper1">

        <!-- Navigation -->
        <nav class="navbar navbar-default navbar-static-top personalizado" role="navigation" >
            <div class="navbar-header">
                <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
                    <span class="sr-only">Toggle navigation</span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </button>
				<?php  //echo var_dump( $url.$url2.'/cma/main.php' ); ?>
                <!-- <a class="navbar-brand" href="http://192.130.74.2:8080/cma/main.php" >CMA v2.0</a> -->
                <a class="navbar-brand" href="http://<?php echo $curserver.'/cma/main.php' ; ?>">CMA v2.0</a>
               
            </div>

          <!-- inicio -->
              <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
      <ul class="nav navbar-nav">
    
         <?php  if($_SESSION['codperfil']=='01' || $_SESSION['codperfil']=='03' || $_SESSION['access']=='5' || $_SESSION['access']=='6'  ){
          echo ' <li class="dropdown"> ';        
          echo ' <a href="#" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Inventario<span class="caret"></span></a>';
          echo ' <ul class="dropdown-menu">';
          echo '   <li><a href="http://'.$curserver.'/cma/vistas/pedidos">Nuevos Pedidos</a></li>';
          echo '   <li><a href="http://'.$curserver.'/cma/vistas/productos/productos.php"><!--<img src="http://'.$curserver.'/cma/img/underconstruction.png" alt="under construction " >-->Productos</a></li>';
          echo '   <li><a href="http://'.$curserver.'/cma/vistas/invservicios/productos.php"><!--<img src="http://'.$curserver.'/cma/img/underconstruction.png" alt="under construction " >-->Servicios</a></li>';
          echo '   <li><a href="http://'.$curserver.'/cma/vistas/inventario/inventario.php">Inventario</a></li>';
          echo '   <li><a href="http://'.$curserver.'/cma/vistas/reportes/ivalorizado.php" target="_blank" >Inventario Valorizado</a></li>';
          echo '   <li><a href="http://'.$curserver.'/cma/vistas/reportes/listadeprecios.php">Baremo</a></li>';
          echo '   <li><a href="http://'.$curserver.'/cma/vistas/productos/compras.php">Compras</a></li>';
          echo '   <li><a href="http://'.$curserver.'/cma/vistas/ajustes/ajustes.php">Ajustes</a></li>';
          echo '   <li><a href="http://'.$curserver.'/cma/vistas/posuero"><img src="http://'.$curserver.'/cma/img/underconstruction.png" alt="under construction " >Pedidos Suero</a></li>';

           echo '   <li><a href="http://'.$curserver.'/cma/vistas/exos_mov/exos_move.php"><img src="http://'.$curserver.'/cma/img/underconstruction.png" alt="under construction " >MOVIMIENTOS AMNISOMA-CM</a></li>';
          ?>
<!--             <li><a href="#">Another action</a></li>
            <li><a href="#">Something else here</a></li>
            <li role="separator" class="divider"></li>
            <li><a href="#">Separated link</a></li>
            <li role="separator" class="divider"></li>
            <li><a href="#">One more separated link</a></li> -->
        <?php   
         echo '</ul>';  
         echo '</li>';
        }?>
        <!-- <li class="active"><a href="vistas/pacientes/lista.php">Pacientes<span class="sr-only">(current)</span></a></li> -->
         <?php if($_SESSION['codperfil']=='01' || $_SESSION['codperfil']=='02' || $_SESSION['codperfil']=='06' || $_SESSION['codperfil']=='05' ){
        echo '<li class="dropdown"> ';
        echo '  <a href="#"  data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Pacientes <span class="caret"></span></a> ';
        echo '    <ul class="dropdown-menu">';
        echo '        <li><a href="http://'.$curserver.'/cma/vistas/pacientes/create.php">Crear nuevos pacientes</a></li>';
        echo '        <li><a href="http://'.$curserver.'/cma/vistas/pacientes/lista.php">Consultar - Editar</a></li> ';
        echo '        <li><a href="http://'.$curserver.'/cma/vistas/reportes/gastosmedicos.php">Certificación de Gastos </a></li> ';
        }?>
        <?php  if($_SESSION['codperfil']=='01' || $_SESSION['access']=='10' ){
        echo '        <li><a href="http://'.$curserver.'/cma/vistas/clientes/cliente_fusionar.php"><img src="http://'.$curserver.'/cma/img/underconstruction.png" alt="under construction "   >Fusionar Records </a></li> ';
        }?>
        <?php 
        echo '    </ul>';
        echo ' </li> ';
        ?>
        
        <li class="dropdown">
          <!-- <a href="#"   data-toggle="dropdown" ro'.$curserver.'haspopup="true" aria-expanded="false">Citas <span class="caret"></span></a>  -->
          <?php if($_SESSION['codperfil']=='01' || $_SESSION['codperfil']=='02' || $_SESSION['codperfil']=='06' || $_SESSION['codperfil']=='05' ){
             echo '<a href="#"   data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Citas <span class="caret"></span></a> ';
          }?>
            
              <?php if($_SESSION['codperfil']=='01' || $_SESSION['codperfil']=='02' || $_SESSION['codperfil']=='06' || $_SESSION['codperfil']=='05' ){
              // /
               echo '<ul class="dropdown-menu">';
               if($_SESSION['codperfil']=='01' || $_SESSION['codperfil']=='02' || $_SESSION['codperfil']=='06' || $_SESSION['CODVENDE']=='1' && $_SESSION['controlcita']=='1'){
                  echo '  <li><a href="http://'.$curserver.'/cma/vistas/control/control.php">Control de citas</a></li> '; 
               }
               
                if( $_SESSION['controlcita']=='0' && ( $_SESSION['codperfil']=='01' || $_SESSION['codperfil']=='05' || $_SESSION['codperfil']=='02' || $_SESSION['codperfil']=='06') ||  $_SESSION['permisobusqueda']=='1'){
                  echo '<li role="separator" class="divider"></li>';
                  echo ' <li><a href="http://'.$curserver.'/cma/vistas/atencion/atencion.php">Atención al Paciente</a></li> '; 
                  echo ' <li><a href="http://'.$curserver.'/cma/vistas/atencionconsultas/atencion.php">Atención al Paciente Médico</a></li> '; 
                  echo ' <li><a href="http://'.$curserver.'/cma/vistas/atencionlaser/">Atención  Laser (B-qa)</a></li> '; 		   
               }
               
			   if( $_SESSION['controlcita']=='0' && ( $_SESSION['codperfil']=='01' || $_SESSION['codperfil']=='02') || $_SESSION['codperfil']=='05'  ||  $_SESSION['permisobusqueda']=='1'){
          echo '<li role="separator" class="divider"></li>';
			   echo ' <li><a href="http://'.$curserver.'/cma/vistas/atencion/repasistidos.php">Reporte</a></li> ';
			   echo ' <li><a href="http://'.$curserver.'/cma/vistas/laserrep/lasersaldo.php">Lasers pendientes</a></li> '; 	

           if (strtolower($user)=="dcampos" || strtolower($user)=="la"  || strtolower($user)=="nortiz") {
                     echo ' <li><a href="http://'.$curserver.'/cma/vistas/entrega/">Entregas de AMNISOMA y CM</a></li> ';                  

           }
           echo ' <li><a href="http://'.$curserver.'/cma/vistas/entrega/listdelivery.php">Lista Entregas de AMNISOMA y CM</a></li> ';
           echo ' <li><a href="http://'.$curserver.'/cma/vistas/entrega/xscm.php">Salidas de AMNISOMA y CM - Detalle</a></li> '; 

           echo ' <li><a href="http://'.$curserver.'/cma/vistas/entrega/mensuales.php">Ventas mensuales de AMNISOMA y CM</a></li> '; 

           echo ' <li><a href="http://'.$curserver.'/cma/vistas/entrega/vitcmensuales.php">Ventas mensuales de Vitamina C</a></li> '; 

           echo ' <li><a href="http://'.$curserver.'/cma/vistas/entrega/porentregar.php ">AMNISOMA y CM Por Entregar</a></li> ';

               
      
			   }
               echo '</ul>';
             }?>
            
        </li>
                <!-- STARTS INVOICING -->
		<?php  if($_SESSION['codperfil']=='01' || $_SESSION['codperfil']=='05'  ||  ($_SESSION['codperfil']=='02' && $_SESSION['controlcita']=='0') ){
                echo '<li class="dropdown">';
					echo '<a href="#" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Facturación <span class="caret"></span></a>';
					echo '<ul class="dropdown-menu">';
						if($_SESSION['codperfil']=='01' || $_SESSION['codperfil']=='02' && $_SESSION['controlcita']=='1'){
							//echo '  <li><a href="#">en progreso</a></li> '; 
						}
						 //FACTURACION 
            if($_SESSION['codperfil']=='01' || $_SESSION['codperfil']=='02' || $_SESSION['codperfil']=='05' && $_SESSION['controlcita']=='0'){
              echo ' <li><a href="http://'.$curserver.'/cma/vistas/invoservices/invoice.php">Consultas</a></li> ';              
            }

            if($_SESSION['codperfil']=='01' || ($_SESSION['codperfil']=='05' ||  $_SESSION['codperfil']=='02' && $_SESSION['access']=='1') || $_SESSION['access']=='6' ){
              echo ' <li><a href="http://'.$curserver.'/cma/vistas/msprods/msprods-add.php">Productos MS LLC</a></li> ';              
            }
            if($_SESSION['codperfil']=='01' || $_SESSION['codperfil']=='02' || $_SESSION['codperfil']=='05' && $_SESSION['controlcita']=='0'){
              echo ' <li><a href="http://'.$curserver.'/cma/vistas/mslaser/mslaser-add.php">Laser</a></li> ';              
            }

            if($_SESSION['codperfil']=='01' || $_SESSION['codperfil']=='02' || $_SESSION['codperfil']=='05' && $_SESSION['controlcita']=='0'){
               
               if (strtolower($_SESSION['username'])=='michelle') {
                 echo ' <li><a href="http://'.$curserver.'/cma/vistas/msservicios/msservicios-add.php">Suero  & AMNISOMA </a></li> '; 
               } else {
                   echo ' <li><a href="http://'.$curserver.'/cma/vistas/msservicios/msservicios-add.php">Suero  & AMNISOMA </a></li> '; 
               }
               

                      

             


            }

            echo '<li role="separator" class="divider"></li>';

            if($_SESSION['codperfil']=='01' || $_SESSION['codperfil']=='02' || $_SESSION['codperfil']=='02' && $_SESSION['controlcita']=='0'){
              echo ' <li><a href="http://'.$curserver.'/cma/vistas/invoservices/returnlist.php">Consultas Lista de Devoluciones</a></li> '; 
            }

            echo '<li role="separator" class="divider"></li>';

            //CUADRE
            if($_SESSION['codperfil']=='01' || $_SESSION['codperfil']=='02' || $_SESSION['codperfil']=='05' && $_SESSION['controlcita']=='0'){           
              echo ' <li><a href="http://'.$curserver.'/cma/vistas/cuadreconsultas/index.php">Cuadre consultas</a></li> ';
            }
            if($_SESSION['codperfil']=='01' || ($_SESSION['codperfil']=='05' || $_SESSION['access']=='6' || $_SESSION['access']=='1')){             
              echo ' <li><a href="http://'.$curserver.'/cma/vistas/cuadreproducts/index.php">Cuadre Productos</a></li> ';             
            }

            if($_SESSION['codperfil']=='01' || $_SESSION['codperfil']=='02' || $_SESSION['codperfil']=='05' && $_SESSION['controlcita']=='0'){             
              echo ' <li><a href="http://'.$curserver.'/cma/vistas/cuadrelaser/index.php">Cuadre Laser</a></li> '; 
            }
            if($_SESSION['codperfil']=='01' || $_SESSION['codperfil']=='02' || $_SESSION['codperfil']=='05' && $_SESSION['controlcita']=='0'){
              echo ' <li><a href="http://'.$curserver.'/cma/vistas/cuadresuero/index.php">Cuadre Suero Terapia</a></li> ';
            }


            if($_SESSION['codperfil']=='01' || $_SESSION['codperfil']=='02' || $_SESSION['codperfil']=='05' && $_SESSION['controlcita']=='0'){           
              echo ' <li><a href="http://'.$curserver.'/cma/vistas/cuadregeneral/index.php"><strong>Cuadre General</strong></a></li> ';
            }



            echo '<li role="separator" class="divider"></li>';
              if($_SESSION['codperfil']=='01' || $_SESSION['codperfil']=='02' || $_SESSION['codperfil']=='05' && $_SESSION['controlcita']=='0'){
                echo ' <li><a href="http://'.$curserver.'/cma/vistas/presupuesto/index.php">Presupuesto</a></li> ';

                            echo '<li role="separator" class="divider"></li>';
            //NOTAS DE ENTREGA 
            
              echo ' <li><a href="http://'.$curserver.'/cma/vistas/notasentrega/notasdeentrega.php">Notas de entrega</a></li> ';               
            

            echo '<li role="separator" class="divider"></li>';              
            }



            //NEW EXOS TERA 
            if($_SESSION['codperfil']=='01' || ($_SESSION['codperfil']=='05' && $_SESSION['access']=='1')){            
              echo ' <li><a href="http://'.$curserver.'/cma/vistas/msservicios/msservicios-add.php">AMNISOMA-SUERO-BLOQUEO</a></li> ';               
            }
            echo '<li role="separator" class="divider"></li>';
            //REPORTES
            if($_SESSION['codperfil']=='01' || ($_SESSION['codperfil']=='05' && $_SESSION['access']=='1')){             
              echo ' <li><a href="http://'.$curserver.'/cma/vistas/notasentrega/repnotaentrega.php">Notas entrega Valorizadas</a></li> ';
              echo ' <li><a href="http://'.$curserver.'/cma/vistas/reportes/ventasxitem.php">Ventas por Item</a></li> ';
            }
     
					echo '</ul>';
				echo '</li>';
		} ?>
        <!-- ENDS INVOICING -->

      <!--   <li><a href="#">Link</a></li> -->
        <?php  if($_SESSION['codperfil']=='01' || $_SESSION['codperfil']=='02'){
        echo '<li class="dropdown">';
        echo '  <a href="#"  data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">SMS <span class="caret"></span></a> ';
        echo '  <ul class="dropdown-menu">';
        echo '    <li><a href="http://'.$curserver.'/cma/vistas/replies">Respuestas recibidas</a></li> ';
        echo '    <li><a href="http://'.$curserver.'/cma/vistas/sents">Mensajes enviados</a></li>';
        ?>
            <!--<li><a href="#">Something else here</a></li>
             <li role="separator" class="divider"></li>
            <li><a href="#">Separated link</a></li>
            <li role="separator" class="divider"></li>
            <li><a href="#">One more separated link</a></li> -->
        <?php 
        echo ' </ul>';
        echo ' </li>';

      }?>

      <?php  if($_SESSION['codperfil']=='01' ||  ( $_SESSION['access']=='6' || $_SESSION['codperfil']=='03' ||  strtolower($_SESSION['username'])=='michelle' ||  strtolower($_SESSION['username'])=='wortiz'  ) ){
        echo '<li class="dropdown">';
        echo '  <a href="#" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Reportes <span class="caret"></span></a> ';
        echo '  <ul class="dropdown-menu">';
        echo '    <li><a href="http://'.$curserver.'/cma/vistas/reportes/comparativo.php">Comparativo</a></li> ';
        echo '    <li><a href="http://'.$curserver.'/cma/vistas/reportes/consolidado.php">Consolidado</a></li> ';
        echo '    <li><a href="http://'.$curserver.'/cma/vistas/stats/">
        <strong>
        Estadísticas de seguimientos</strong>
        </a></li> ';
        
        echo '    <li><a href="http://'.$curserver.'/cma/vistas/reportes/salesxhours.php">Ventas por Hora</a></li> ';

         }?>

         <?php  if($_SESSION['codperfil']=='01'  ){
         echo '    <li><a href="http://'.$curserver.'/cma/vistas/calendario/">Agenda de Eventos </a></li> ';
          }?>
  <?php  if($_SESSION['codperfil']=='01' ||  ( $_SESSION['access']=='6' || $_SESSION['codperfil']=='03' ||  strtolower($_SESSION['username'])=='michelle' ||  strtolower($_SESSION['username'])=='wortiz'  ) ){

    
        echo '    <li><a href="http://'.$curserver.'/cma/vistas/reportes/emt.php">Estadisticas Medicos Totales</a></li> ';
        echo '    <li><a href="http://'.$curserver.'/cma/vistas/reportes/estadisticasmedicos.php">Estadísticas Medicos</a></li>';
        echo '    <li><a href="http://'.$curserver.'/cma/vistas/reportes/repeated.php">Repeated</a></li> ';
        echo '    <li><a href="http://'.$curserver.'/cma/vistas/reportes/bloqueo.php">Bloqueo</a></li> '; 

        
        echo '    <li><a href="http://'.$curserver.'/cma/vistas/reportes/ventascintron.php">Nuevo Rep Cintron ( EN PROGRESO )</a></li> ';


        echo '    <li><a href="http://'.$curserver.'/cma/vistas/reportes/ventascelma.php">Ventas AMNISOMA </a></li> ';
        echo '    <li><a href="http://'.$curserver.'/cma/vistas/reportes/pacientesvistos.php"><img src="http://'.$curserver.'/cma/img/new2.png" alt="new" >Pacientes Vistos </a></li> ';

           echo '    <li><a href="http://'.$curserver.'/cma/vistas/reportes/prescvsventa.php"><img src="http://'.$curserver.'/cma/img/new2.png" alt="new" >Prescripción Vs. Venta </a></li> ';

        echo '    <li><a href="http://'.$curserver.'/cma/vistas/reportes/ventascelmaconsulta.php">Dr. Cintrón </a></li> ';
        echo '    <li><a href="http://'.$curserver.'/cma/vistas/reportes/ventascmexocmed.php">Ventas AMNISOMA por Médico </a></li> ';
        echo '    <li><a href="http://'.$curserver.'/cma/vistas/reportes/efectividad.php">Efectividad</a></li> ';  
        echo '    <li><a href="http://'.$curserver.'/cma/vistas/reportes/repprodmeddesc.php">Productos por medico orden descendente</a></li> ';  
        echo '    <li><a href="http://'.$curserver.'/cma/vistas/reportes/repcocientes.php">Cocientes</a></li> ';  
        echo '    <li><a href="http://'.$curserver.'/cma/vistas/reportes/ventasporitemscant.php">Cantidad de unidades vendidas por items </a></li> ';        
        echo '    <li><a href="http://'.$curserver.'/cma/vistas/reportes/ventasporcajerolaser.php">Laser Resumen de Ventas </a></li> ';
        echo '    <li><a href="http://'.$curserver.'/cma/vistas/reportes/ventasporcajeroexos.php">AMNISOMA Resumen de Ventas </a></li> ';          
        echo '    <li><a href="http://'.$curserver.'/cma/vistas/reportes/promayorsalida.php">Productos y Formulas orden Descendente por Medicos</a></li> ';
        echo '    <li><a href="http://'.$curserver.'/cma/vistas/notasentrega/repnotaentrega.php"> <!--<img src="http://'.$curserver.'/cma/img/new2.png" alt="new" >-->Notas de entrega Valorizadas</a></li> ';
        echo '    <li><a href="http://'.$curserver.'/cma/vistas/reportes/cmamovinv.php">Movimiento de inventario de Suero</a></li> ';
        echo '    <li><a href="http://'.$curserver.'/cma/vistas/reportes/movlaserintra.php">Movimiento de inventario de Laser Interavenoso</a></li> ';   
        echo '    <li><a href="http://'.$curserver.'/cma/vistas/reportes/gastosmedicos.php">Certificación de Gastos </a></li> ';
        echo '    <li><a href="http://'.$curserver.'/cma/vistas/reportes/returns.php">Devoluciones</a></li> ';  
        echo '    <li><a href="http://'.$curserver.'/cma/vistas/reportes/ventasnoivu.php"><img src="http://'.$curserver.'/cma/img/new2.png" alt="new" style="display:none;" >Ventas Sin IVU</a></li> ';  
        echo '    <li><a href="http://'.$curserver.'/cma/vistas/reportes/ventasgenerales.php"><img src="http://'.$curserver.'/cma/img/new2.png" alt="new" style="display:none;">Ventas Generales </a></li> ';  
        echo '    <li><a href="http://'.$curserver.'/cma/vistas/exos_mov/exos_move.php">Movimientos de AMNISOMA-CM</a></li>';  
        echo '    <li><a href="http://'.$curserver.'/cma/vistas/reportes/vitac.php">Rep Vit C</a></li>'; 
        echo '    <li><a href="http://'.$curserver.'/cma/vistas/reportes/cmqty.php">Rep  CM (cantidad)</a></li>';  
        echo ' </ul>';

        echo ' </li>';
       }?>

       <?php  if($_SESSION['codperfil']=='01' || $_SESSION['access']=='10' ){
            echo '<li class="dropdown">';
            echo '  <a href="#" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Admin<span class="caret"></span></a> ';
            echo '  <ul class="dropdown-menu">';
            echo '    <li><a href="http://'.$curserver.'/cma/vistas/admin/users.php"><img src="http://'.$curserver.'/cma/img/underconstruction.png" alt="under construction "   >Usuarios</a></li> ';    
            echo '    <li><a href="http://'.$curserver.'/cma/vistas/medicos/index.php"><img src="http://'.$curserver.'/cma/img/underconstruction.png" alt="under construction "   >Médicos</a></li> ';        
            echo ' </ul>';
            echo ' </li>';
       }?>


      </ul>
          <!-- fin -->
          
            <div class="col-sm-3">
                          <ul class="nav navbar-nav">    
                <li class="dropdown">
                   <a href="#" style="font-size: 150%;"  id="xtitulo"></a> 
                </li>
            </ul>
            </div>
             
            <ul class="nav navbar-top-links navbar-right">

                <li class="dropdown">
                    <a   data-toggle="dropdown" href="#">
                        <i class="fa fa-user fa-fw"></i> <?php echo $user ;?> <i class="fa fa-caret-down"></i>
                    </a>
                    <input  id="loggedusr" type="hidden"  name="loggedusr" value=<?php echo $user ; ?>>
                    <ul class="dropdown-menu dropdown-user">
          <!--               <li ><a href="#" id="userlogd"><i class="fa fa-user fa-fw"></i> <?php echo $user;?></a>    </li>
          print $user->get_username()              <li><a href="#"><i class="fa fa-gear fa-fw"></i> Settings</a>
                        </li> -->
                        <li class="divider"></li>
                        <li><a href="http://<?php echo $curserver;?>/cma/index.php?op=logout"><i class="fa fa-sign-out fa-fw"></i> Logout</a>
                        </li>
                        <?php 
                         echo '<li><a href="http://'.$curserver.'/cma/vistas/usuarios/"><img src="http://'.$curserver.'/cma/img/underconstruction.png" alt="under construction ">Configuración</a></li> ';   
                         ?>

                        


                    </ul>
                    <!-- /.dropdown-user -->
                </li>
                <!-- /.dropdown -->
            </ul>

        </nav>



    </div>