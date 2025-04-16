<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Reset Password password</title>
</head>
<body>
    <form method="POST" action="{{route('auth.submitresetpassword')}}">
        @csrf
        <input type="hidden" name = "token" value ="{{$token}}">
        <input type="email" name = "email" id="email" placeholder="Enter Email">
        <input type="password" name = "password" id="password" placeholder="Enter Password">
        <input type="password" name = "password_confirmation" id="confirmpassword" placeholder="confirm Password">
        <button type="submit">Reset password</button>

    </form>
</body>
</html>