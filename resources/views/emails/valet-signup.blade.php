<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{env('APP_NAME')}}</title>
</head>
<body>
    <p>
        Hi, {{$name}},
        <br>
        These are you login details :-
        <br>
        <strong>Email : </strong>{{$email}} <br>
        <strong>Password : </strong>{{$password}}
    </p>
</body>
</html>
