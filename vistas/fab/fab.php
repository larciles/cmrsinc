<?php
header("Content-Type: text/html;charset=utf-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
header("Access-Control-Allow-Headers: X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method");
set_time_limit(0);
error_reporting(E_ERROR | E_WARNING | E_PARSE);
session_start();
if(!isset($_SESSION['username'])){
    header("Location:../login/login.php");
    return;
}else{
    $user=$_SESSION['username'];
    $codperfil=$_SESSION['codperfil'];
}

?>

<script src="https://code.jquery.com/jquery-1.11.3.js"></script>
<!-- <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script> -->

<style type="text/css">
  .floatingmenu_label {
  width: 150px;
  text-align: right;
  padding-right: 10px;
  position: absolute;
  left: -160px;
  color: #454545;
  white-space: nowrap;
}

#btnExit.show {
  -webkit-transform: translateY(-125%);
  transform: translateY(-125%);
}

#btnUsers.show {
  -webkit-transform: translateY(-250%);
  transform: translateY(-250%);
}

#btnJobs.show {
  -webkit-transform: translateY(-375%);
  transform: translateY(-375%);
}

#btnReports.show {
  -webkit-transform: translateY(-500%);
  transform: translateY(-500%);
}

#btnFilters.show {
  -webkit-transform: translateY(-625%);
  transform: translateY(-625%);
}

#hamburger {
  z-index: 10;
  position: fixed;
  display: -webkit-box;
  display: -ms-flexbox;
  display: flex;
  -webkit-box-orient: vertical;
  -webkit-box-direction: normal;
  -ms-flex-direction: column;
  flex-direction: column;
  -webkit-box-pack: center;
  -ms-flex-pack: center;
  justify-content: center;
  -webkit-box-align: center;
  -ms-flex-align: center;
  align-items: center;
  bottom: 10%;
  right: 5%;
  background-color: #FF5722;
  width: 56px;
  height: 56px;
  border-radius: 50%;
  cursor: pointer;
  box-shadow: 2px 2px 10px rgba(10, 10, 10, 0.3);
  -webkit-transition: all 0.2s ease-in-out;
  transition: all 0.2s ease-in-out;


}

#hamburger .icon-bar {
  display: block;
  background-color: #FFFFFF;
  width: 22px;
  height: 2px;
  -webkit-transition: all 0.3s ease-in-out;
  transition: all 0.3s ease-in-out;
}

#hamburger .icon-bar+.icon-bar {
  margin-top: 4px;
}

.hamburger-nav {
  z-index: 9;
  position: fixed;
  bottom: 10.5%;
  right: 5.5%;
  width: 48px;
  height: 48px;
  border-radius: 50%;
  background-color: #f9f9f9;
  display: -webkit-box;
  display: -ms-flexbox;
  display: flex;
  -webkit-box-orient: vertical;
  -webkit-box-direction: normal;
  -ms-flex-direction: column;
  flex-direction: column;
  -webkit-box-pack: center;
  -ms-flex-pack: center;
  justify-content: center;
  -webkit-box-align: center;
  -ms-flex-align: center;
  align-items: center;
  visibilty: hidden;
  opacity: 0;
  box-shadow: 3px 3px 10px 0px rgba(0, 0, 0, 0.48);
  cursor: pointer;
  -webkit-transition: all 0.3s ease-in;
  transition: all 0.3s ease-in;
}

#hamburger.show {
  box-shadow: 7px 7px 10px 0px rgba(0, 0, 0, 0.48);
}

#hamburger.show #wrapper {
  -webkit-transition: -webkit-transform 0.4s ease-in-out;
  transition: -webkit-transform 0.4s ease-in-out;
  transition: transform 0.4s ease-in-out;
  transition: transform 0.4s ease-in-out, -webkit-transform 0.4s ease-in-out;
  -webkit-transform: rotateZ(90deg);
  transform: rotateZ(90deg);
}

#hamburger.show #one {
  -webkit-transform: translateY(6px) rotateZ(45deg) scaleX(0.9);
  transform: translateY(6px) rotateZ(45deg) scaleX(0.9);
}

#hamburger.show #two {
  opacity: 0;
}

#hamburger.show #thr {
  -webkit-transform: translateY(-6px) rotateZ(-45deg) scaleX(0.9);
  transform: translateY(-6px) rotateZ(-45deg) scaleX(0.9);
}

.hamburger-nav.show {
  visibility: visible;
  opacity: 1;
}

.fab-center{
     position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
}
.ham{
  top: 50%;
  left: 50%;
}
</style>

<div id="hamburger" class="waves-effect waves-light">
    <div id="wrapper">
    
      <span class="icon-bar ham" id="one"></span>
      <span class="icon-bar ham" id="two"></span>
      <span class="icon-bar ham" id="thr"></span>
    </div>
  </div>

  <div id="btnExit" class="hamburger-nav">
    <span class="floatingmenu_label">Exit</span>
    <img  class="fab-center" style="width: 24px; height: 24px;" src="https://www.iconfinder.com/data/icons/small-n-flat/24/pencil-128.png">
  </div>
  <div id="btnUsers" class="hamburger-nav">
    <span class="floatingmenu_label">Users</span>
     <img  class="fab-center" style="width: 24px; height: 24px;" src="https://www.iconfinder.com/data/icons/small-n-flat/24/pencil-128.png">
  </div>
  <div id="btnJobs" class="hamburger-nav">
    <div class="floatingmenu_label">Jobs</div>
     <img  class="fab-center" style="width: 24px; height: 24px;" src="https://www.iconfinder.com/data/icons/small-n-flat/24/pencil-128.png">
  </div>
  <div id="btnFilters" class="hamburger-nav">
    <span class="floatingmenu_label">Filters</span>
     <img  class="fab-center" style="width: 24px; height: 24px;" src="https://www.iconfinder.com/data/icons/small-n-flat/24/pencil-128.png">
  </div>
  <div id="btnReports" class="hamburger-nav">
    <span class="floatingmenu_label">Reports</span>
     <img  class="fab-center" style="width: 24px; height: 24px;" src="https://www.iconfinder.com/data/icons/small-n-flat/24/pencil-128.png">
  </div>


  <script type="text/javascript">
      $('#hamburger').click(function() {
      $('#hamburger').toggleClass('show');
      $('.hamburger-nav').toggleClass('show');
  });
  </script>
