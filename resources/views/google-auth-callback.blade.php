<!DOCTYPE html>
<html>
<head>
    <title>Google Auth Callback</title>
    <script>
        window.onload = function() {
            const authData = {
                token: @json($token),
                user: @json($user),
                message: @json($message)
            };
            
            // Store the data in localStorage so the opener can access it
            localStorage.setItem('googleAuth', JSON.stringify(authData));
            
            // Trigger a storage event so the opener can listen for it
            window.opener.postMessage('googleAuthSuccess', window.location.origin);
            
            // Close the popup
            window.close();
        };
    </script>
</head>
<body>
    <p>Please wait while we log you in...</p>
</body>
</html>