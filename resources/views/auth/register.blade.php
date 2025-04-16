<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Register</title>
</head>
<body>
    <form method="POST" action="{{route('register.store')}}">
        @csrf
        <input type="text" name = "name" id="name" placeholder="Enter name">
        <input type="email" name = "email" id="email" placeholder="Enter Email">
        <input type="password" name = "password" id="password" placeholder="Enter password">
        <input type="password" name="password_confirmation" placeholder="Confirm Password" required>
        <input type="text" name="username" id="username" placeholder="Enter username">
        <select name="user_type" id="user_type" required>
        <option value="news_enthusiast">News Enthusiast</option>
        <option value="content_creator">Content Creator</option>
        <option value="admin">Admin</option>
        </select>
        <button type="submit">Register</button>
    </form>
</body>
</html>
