<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Dashboard</title>
    <style>
        body{
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f2f2f2
        }
        .container{
            max-width: 960px;
            margin:  0 auto;
            padding: 20px;
        }
        h1{
            color:#333;
            text-align: center;
            margin-bottom: 20px;

        }
        .logout-link a{
            text-decoration: none;
            color: #007bff
        }

        .logout-link a:hover{
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>this is dashboard</h1>
        <div class = "logout-link">
            <a href="{{route('logout')}}">    

            <em class="icon ni ni-signout"></em><span>Sign out</span>
        </a>
        </div>

    </div>
    
</body>
</html>