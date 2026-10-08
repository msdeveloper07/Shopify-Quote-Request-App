<!DOCTYPE html>
<html>
<head>
    <title>Install App</title>
</head>
<body>
    <h1>Install Link Generator</h1>
    <form action="{{ route('shopify.install') }}" method="GET">
        <label>Enter Store Name:</label>
        <input type="text" name="shop" placeholder="store-name.myshopify.com" required>
        <button type="submit">Generate Install Link</button>
    </form>
</body>
</html>