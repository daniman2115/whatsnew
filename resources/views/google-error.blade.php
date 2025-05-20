<!-- google-error.blade.php -->
<!DOCTYPE html>
<html>
<head>
    <title>Google Auth Error</title>
</head>
<body>
    <script>
        if (window.opener) {
            window.opener.postMessage({
                type: 'google-auth-error',
                error: '{{ $error }}'
            }, '{{ config("app.frontend_url") }}');
        }
        window.close();
    </script>
</body>
</html>