<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="stylesheet" href="{{ asset('css/login-page.css') }}">
    <link rel="icon" href="{{ asset('logo.jpg') }}" />
    <!--https://boxicons.com/ -->

    <title>Login & Register Page </title>
    <script defer src="{{ asset('js/login-page.js') }}"></script>
</head>


<body>

    <div class="container" id="container">
        <div class="form-container sign-up">
            <form>
                <h1>Create Account</h1>
                <!-- <div class="social-icons">
                    <a href="#" class="icons"><i class='bx bxl-google'></i></a>
                    <a href="#" class="icons"><i class='bx bxl-facebook'></i></a>
                    <a href="#" class="icons"><i class='bx bxl-github'></i></a>
                    <a href="#" class="icons"><i class='bx bxl-linkedin'></i></a>
                </div>
                 <span>Register with E-mail</span> -->
                <input type="text" placeholder="Name">
                <input type="email" placeholder="Enter E-mail">
                <input type="password" placeholder="Enter Password">
                <input type="confirm-password" placeholder="Confirm Password">

                <button>Sign Up</button>
            </form>
        </div>


        <div class="form-container sign-in">
            <form>
                <h1>Login</h1>
                <!-- <div class="social-icons">
                    <a href="#" class="icons"><i class='bx bxl-google'></i></a>
                    <a href="#" class="icons"><i class='bx bxl-facebook'></i></a>
                    <a href="#" class="icons"><i class='bx bxl-github'></i></a>
                    <a href="#" class="icons"><i class='bx bxl-linkedin'></i></a>
                </div> -->
                <!-- <span>Login With Email & Password</span> -->
                <input type="email" placeholder="Enter E-mail">
                <input type="password" placeholder="Enter Password">
                <a href="#">Forget Password?</a>
                <button> Login </button><a href="Home.html" target="_blank">...</a>
                <!-- <button>Login</button> -->
            </form>
        </div>


        <div class="toggle-container">
            <div class="toggle">
                <div class="toggle-panel toggle-left">
                    <h1>Welcome To <br>FitScan</h1>
                    <p>Login With ID & Passowrd</p>
                    <button class="hidden" id="login">Login</button>
                </div>
                <div class="toggle-panel toggle-right">
                    <h1>Hi Friend</h1>
                    <p>Join "FitScan" to Improve your healthy and achieve more</p>
                    <button class="hidden" id="register">Sign Up</button>
                </div>
            </div>
        </div>
    </div>

</body>

</html>
