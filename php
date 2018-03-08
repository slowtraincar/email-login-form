<?php 
//save user info w/cookie
    session_start();
    
     $error="";

//log user out with cookie unset
    if(array_key_exists("logout", $_GET)){
        unset($_SESSION);
        setcookie("id","", time() - 60*60);
        $_COOKIE["id"]= "";
        
//if signed in user attempts to get to sign up page, redirect to logged in page 
/* Also verifies logout sticks to make user log in again if they log out and then attempt to go to logged in page*/        
    }else if ((array_key_exists("id", $_SESSION) AND $_SESSION['id']) OR (array_key_exists("id", $_COOKIE) AND $_COOKIE['id'])) {
        
        header("Location: loggedinpage.php");
    }

//check to make sure php is connecting to html input on site
    if (array_key_exists("submit", $_POST)){

//connect to mySQL database on server       
        $link = mysqli_connect("serverName", "databaseName", "password", "userName");
        
//connection test       
        if(mysqli_connect_error()){
        
            
//response for bad connection to server mySQL database           
            die ("Database Connection Error");
        }
        
        
//check that form is filled out completely        
       
//no email        
        if (!$_POST['email']){
            
            $error .= "An email address is required<br>";
        }
//no password        
        if (!$_POST['password']){
            
            $error .= "A password is required<br>";
        }
//heading to explain what is missing in form        
        if ($error != ""){
            
            $error = "<p> There were error(s) in your form:</p>".$error;
//form filled out, check that email is not already in mySQL database            
        } else {
            
            if ($_POST['signUp'] == '1'){
            
            
            
//            check from database sect. listed        stop bad script    use link connect    
            $query = "SELECT id FROM users WHERE email = '".mysqli_real_escape_string($link, $_POST['email'])."' LIMIT 1";
// check for email in linked database            
            $result = mysqli_query($link, $query);
            
            if (mysqli_num_rows($result) > 0){
// if email is already stored                
                $error = "That email address is taken.";
                
            } else {
// email not already stored. set up account with email and password submited                
                $query = "INSERT INTO users (email, password) VALUES ('".mysqli_real_escape_string($link, $_POST['email'])."',
                '".mysqli_real_escape_string($link, $_POST['password'])."')";
// if unable to add user                
                    if (!mysqli_query($link, $query)) {
                        
                        $error = "<p>Could not sign you up - please try again later.</p>";
                    
                    } else {

//encrypt new user's password                        
                        $query = "UPDATE users SET password = '".md5(md5(mysqli_insert_id($link)).$_POST['password'])."' WHERE id = ".mysqli_insert_id($link)." LIMIT 1"; 
                        
                        mysqli_query($link, $query);

//set session                        
                        $_SESSION['id'] = mysqli_insert_id($link);

//set cookie timeframe if user checks stay logged in                         
                        if ($_POST['stayLoggedIn'] == '1'){
                            
                            setcookie("id", mysqli_insert_id($link), time() + 60*60*24*365);
                            
                        }
// confirmation of account creation sends user to logged in page                        
                        header("Location: loggedinpage.php");
                        
                    } 
            } 
            
          } else {
 //log into account validation               
                $query = "SELECT * FROM users WHERE email = '".mysqli_real_escape_string($link, $_POST['email'])."'";
                
                $result = mysqli_query($link, $query);
                
                $row = mysqli_fetch_array($result);
                
                if (isset($row)){
                    
                    $hashedPassword = md5(md5($row['id']).$_POST['password']);
                    
                    if($hashedPassword == $row['password']){
                        
                        $_SESSION['id'] = $row['id'];
                        
                        if ($_POST['stayLoggedIn'] == '1'){
                            
                            setcookie("id", $row['id'], time() + 60*60*24*365);
                        }
//confirmed credentials, logged in page redirect                       
                        header("Location: loggedinpage.php");
                    } else {
//wrong email/password error                       
                        $error = "That email/password combination could not be found.";
                        
                    }
                } else {
//wrong email/password error
                    $error = "That email/password combination could not be found.";
                    
                }
            } 
        }
    }







?>
