<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Birthday Greeting System</title>

    <!-- Google Font -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #007bff, #00c6ff);
            height: 100vh;
            overflow: hidden;
            animation: fadeInBody 1s ease-in-out;
        }

        @keyframes fadeInBody {
            from {opacity: 0;}
            to {opacity: 1;}
        }

        .login-wrapper {
            height: 100vh;
        }

        .login-card {
            width: 380px;
            background: #fff;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.15);
            animation: slideUp 0.8s ease-out;
        }

        @keyframes slideUp {
            from {transform: translateY(60px); opacity: 0;}
            to {transform: translateY(0); opacity: 1;}
        }

        .login-title {
            font-weight: 600;
            animation: fadeInText 1.2s ease-in-out;
        }

        @keyframes fadeInText {
            from {opacity: 0;}
            to {opacity: 1;}
        }

        .input-group-text {
            background-color: #f1f1f1;
            border-radius: 8px 0 0 8px;
        }

        .form-control {
            border-radius: 8px;
            transition: 0.3s;
        }
        .form-control:focus {
            border-color: #007bff;
            box-shadow: 0 0 8px rgba(0,123,255,0.3);
        }

        .btn-login {
            background: linear-gradient(to right, #ff7a18, #af002d);
            border: none;
            padding: 10px;
            border-radius: 8px;
            font-weight: 500;
            color: #fff;
            transition: 0.5s;
            background-size: 200%;
        }
        .btn-login:hover {
            background-position: right;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(175, 0, 45, 0.4);
        }

        a {
            text-decoration: none;
            transition: 0.3s;
        }
        a:hover {
            color: #ff7a18;
        }
    </style>
</head>
<body>

<div class="d-flex justify-content-center align-items-center login-wrapper">
    <div class="login-card">
        <h3 class="text-center login-title">Welcome Back</h3>
        <p class="text-center text-muted mb-4">Login to continue</p>

        <form onsubmit="return validateLogin()" method="POST" action="{{ route('login') }}">
            @csrf
            <div class="mb-3">
                <label>Email Address</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fa-solid fa-envelope"></i></span>
                    <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" placeholder="Enter your email" name="email" value="{{ old('email') }}" required autocomplete="email" autofocus>
                    @error('email')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>
            </div>

            <div class="mb-3">
                <label>Password</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fa-solid fa-lock"></i></span>
                    <input id="password" type="password"   class="form-control @error('password') is-invalid @enderror" placeholder="Enter your password" name="password" required autocomplete="current-password">
                    @error('password')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>
            </div>

            <div class="d-flex justify-content-between mb-4">
                <div>
                    <input type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}> <label for="remember">Remember Me</label>
                </div>
            </div>

            <button type="submit" class="btn btn-login w-100">Login</button>
        </form>

    </div>
</div>

<script>
function validateLogin() {
    let email = document.getElementById("email").value.trim();
    let password = document.getElementById("password").value.trim();

    if (email === "" || password === "") {
        alert("Please fill in both fields.");
        return false;
    }

    return true;
}
</script>

</body>
</html>
