<?php

//include("../admin/includes/config.php");
session_start();

if($_SESSION['email']!="" && $_SESSION['heslo']!=""){
    $request = $mysqli->query( "SELECT * FROM demands WHERE email = '" . $_SESSION['email'] . "' AND password = '" . $_SESSION['heslo'] . "' AND active = '1'" ) or die( $mysqli->error );
    if ( mysqli_num_rows( $request ) == 1 ) {
        $_SESSION['loggedin'] = "1";
    }else{$_SESSION['loggedin'] = "0"; echo 'Bad Session';}

}elseif($_COOKIE["cookie_user"]!="null" && $_COOKIE["cookie_pass"]!="null"){
    $request = $mysqli->query( "SELECT * FROM demands WHERE email = '" . $_COOKIE['cookie_user'] . "' AND password = '" . $_COOKIE['cookie_pass'] . "' AND active = '1'" ) or die( $mysqli->error );
    if ( mysqli_num_rows( $request ) == 1 ) {
        setcookie("cookie_email", $_COOKIE['cookie_email'], time() + 60*60*24*30, "/");
        setcookie("cookie_pass", $_COOKIE['cookie_pass'], time() + 60*60*24*30, "/");
        setcookie("cookie_prava", $_COOKIE['cookie_prava'], time() + 60*60*24*30, "/");
        $_SESSION['loggedin'] = "1";
        $_SESSION['email'] = $_COOKIE['cookie_email'];
        $_SESSION['heslo'] = $_COOKIE['cookie_pass'];
        $_SESSION['prava'] = $_COOKIE['cookie_prava'];
        if ($_SESSION['prava'] == "admin") {
            $_SESSION['admin_kffs54fsda54a6f46afsa4554a'] = "1";
        }elseif ($_SESSION['prava'] == "redaktor") {
            $_SESSION['redaktor_hkjds45sadf46va23fadsfa'] = "1";
        }
    }else{$_SESSION['loggedin'] = "0";}
}

$clientquery = $mysqli->query('SELECT * FROM demands WHERE email="' . $_COOKIE['cookie_email'] . '"')or die($mysqli->error);
$client = mysqli_fetch_assoc($clientquery);
?>

<body class="page-body login-page is-lockscreen login-form-fall" data-url="http://neon.dev">


<!-- This is needed when you send requests via Ajax --><script type="text/javascript">
    var baseurl = '';
</script>

<div class="login-container">

    <div class="login-header login-caret">

        <div class="login-content">

            <a href="/" class="logo">
                <img src="../admin/assets/images/logo@2x.png" width="320" alt="" />
            </a>

            <p class="description">vířivky a sauny zaměřené na detail</p>

            <!-- progress bar indicator -->
            <div class="login-progressbar-indicator">
                <h3>43%</h3>
                <span>probíhá přihlašování...</span>
            </div>
        </div>

    </div>

    <div class="login-progressbar">
        <div></div>
    </div>

    <div class="login-form">

        <div class="login-content">


            <form method="post" role="form" id="form_lockscreen">

                <div class="form-group lockscreen-input">

                    <div class="lockscreen-thumb">
                        <img src="<?php if($client['role'] == 'admin'){ echo '../admin/assets/avatars/'.$client['id'].'.jpg';}else{ echo '../admin/data/images/customer/'.$client['product'].'.png';}?>" width="150" class="img-circle" />

                        <div class="lockscreen-progress-indicator">0%</div>
                    </div>

                    <div class="lockscreen-details">
                        <h4><?= $client['name'].' '.$client['surname'] ?></h4>
                        <span data-login-text="logging in...">uzamknuto</span>
                    </div>

                </div>

                <div class="form-login-error">
                    <h3>Neplatné přihlášení</h3>
                    <p>Zadané heslo není správné.  <strong style="color: #fff">Zkuste to znovu</strong>.</p>

                </div>
                <div class="form-group">

                    <div class="input-group">
                        <div class="input-group-addon">
                            <i class="entypo-key"></i>
                        </div>

                        <input type="password" class="form-control" name="password" id="password" placeholder="Heslo" autocomplete="off" />
                    </div>

                </div>

                <div class="form-group">
                    <button type="submit" class="btn btn-primary btn-block btn-login">
                        <i class="entypo-login"></i>
                        Přihlásit se
                    </button>
                </div>

            </form>


            <div class="login-bottom-links">

                <a href="flush.php" class="link">Přihlásit se pod jiným účtem <i class="entypo-right-open"></i></a>


            </div>


        </div>

    </div>

</div>

<div class="footer-logos">


    <a href="http://www.spahouse.cz"><img src="../admin/assets/images/spahouse-logo-footer.png"></a>
    <a href="https://www.saunahouse.cz"><img src="../admin/assets/images/saunahouse-footlogo.png"  style="margin-bottom: -6px;"></a>
    <a href="https://www.virivka.cz"><img src="../admin/assets/images/virivka-footlogo.png"></a>
    <a href="http://www.iquespa.cz" style="margin-right: 0px;"><img src="../admin/assets/images/ique-footlogo.png"></a>

</div>
<!-- Bottom Scripts -->
<script src="../admin/assets/js/gsap/main-gsap.js"></script>
<script src="../admin/assets/js/jquery-ui/js/jquery-ui-1.10.3.minimal.min.js"></script>
<script src="../admin/assets/js/bootstrap.js"></script>
<script src="../admin/assets/js/joinable.js"></script>
<script src="../admin/assets/js/resizeable.js"></script>
<script src="../admin/assets/js/neon-api.js"></script>
<script src="../admin/assets/js/jquery.validate.min.js"></script>
<script src="../admin/assets/js/neon-login.js"></script>
<script src="../admin/assets/js/neon-custom.js"></script>
<script src="../admin/assets/js/neon-demo.js"></script>




</body>
</html>
