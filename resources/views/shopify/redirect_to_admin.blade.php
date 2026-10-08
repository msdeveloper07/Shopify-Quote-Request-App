<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Redirecting to Shopify Admin...</title>
</head>
<body>
    <script>
        // Redirect to the app inside Shopify Admin
        const shop = "{{ $shop }}";
        const apiKey = "{{ config('shopify.api_key') }}";
        const redirectUrl = `https://${shop}/admin/apps/${apiKey}`;
        
        window.top.location.href = redirectUrl;
    </script>
    <p>Redirecting to Shopify Admin...</p>
</body>
</html>