<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="Mark Otto, Jacob Thornton, and Bootstrap contributors">
    <meta name="generator" content="Jekyll v4.1.1">
    <title>Signin Template · Bootstrap</title>

    <link rel="canonical" href="https://getbootstrap.com/docs/4.5/examples/sign-in/">

    <!-- Bootstrap core CSS -->
    <link href="https://getbootstrap.com/docs/4.5/dist/css/bootstrap.min.css" rel="stylesheet" >

    <style>
        .bd-placeholder-img {
            font-size: 1.125rem;
            text-anchor: middle;
            -webkit-user-select: none;
            -moz-user-select: none;
            -ms-user-select: none;
            user-select: none;
        }

        @media (min-width: 768px) {
            .bd-placeholder-img-lg {
                font-size: 3.5rem;
            }
        }
    </style>
    <!-- Custom styles for this template -->
    <link href="https://getbootstrap.com/docs/4.5/examples/sign-in/signin.css" rel="stylesheet">
</head>
<body class="text-center">

<?php
require_once '../controller/Input.php';
require_once '../controller/Validate.php';
if(Input::exists()) {
   $validate = new Validate();
   $validation = $validate->check($_POST,array(
         'username'=>array(
                 'required' => true,
                 'min' => 2,
                 'max' => 20,
                 'uniqe'=>'users'
         ),
       'password' => array(
               'required'=> true,
               'min'=> 6,
       ),
       'passwordRepeat' => array(
               'required'=> true,
               'matches'=> 'password'
       )
   ));
   if($validation->passed()){
       //register user
   }else{
       //output errors
   }
}
?>

<form class="form-signin" method="post" action="">
    <h1 class="h3 mb-3 font-weight-normal">Please sign in</h1>
    <label for="inputEmail" class="sr-only">Email address</label>
    <input type="text" id="inputEmail" class="form-control" name="username"
           placeholder="Email address" required autofocus>
    <label for="inputPassword" class="sr-only">Password</label>
    <input type="password" id="inputPassword" class="form-control" name="password"
           placeholder="Password" required>
    <label for="inputPasswordRepeat" class="sr-only">Password Repeat</label>
    <input type="password" id="inputPasswordRepeat" class="form-control" name="passwordRepeat"
           placeholder="Password Repeat" required>
    <label>
        <select id="type" name="type" class="form-control">
            <option value="admin">Admin</option>
            <option value="guest">Guest</option>
        </select>
    </label>


    <div class="text-danger">

    </div>
    <button class="btn btn-lg btn-primary btn-block" type="submit">Register</button>
    <p class="mt-5 mb-3 text-muted">&copy; 2017-2020</p>
</form>
</body>
</html>