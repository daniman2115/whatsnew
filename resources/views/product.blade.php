<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
</head>
<body>
    <form method="POST" action="{{route('uploadproduct')}}" enctype="multipart/form-data">
        @csrf
        <input type="text" name ="name"placeholder="Video Name">
        <input type="text" name ="title"placeholder="Video title"></input>
        <input type="text" name = "description" placeholder="Video description">
        <input type="file" name="file"></input>
        <input type="submit"></input>
    </form>
</body>
</html>