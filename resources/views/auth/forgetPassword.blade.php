<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>forgot Password</title>
</head>
<body>
    <form method="POST" action="{{route('auth.submitforgetpassword')}}">
        @csrf
        <input type="email" name = "email" id="email" placeholder="Enter Email">
        <button type="submit">Send Reset password link</button>

    </form>
</body>
</html>