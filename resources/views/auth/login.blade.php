<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Login</title>
</head>
<body>
    <form method="POST" action="{{route('login.store')}}">
        @csrf
        <input type="email" name = "email" id="email" placeholder="Enter Email">
        <input type="password" name = "password" id="password" placeholder="Enter password">
        <button type="submit">Login</button>
        <div class = 'forgot-password'>
            <a href="{{route('auth.forgetpassword')}}">Forgot Password</a>
        </div>
    </form>
</body>
</html>
