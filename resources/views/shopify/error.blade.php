<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Error</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #ffeaa7 0%, #fdcb6e 100%);
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
        }
        
        .container {
            background: white;
            padding: 50px 40px;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            max-width: 500px;
            width: 100%;
            text-align: center;
        }
        
        .error-icon {
            font-size: 60px;
            margin-bottom: 20px;
        }
        
        h1 {
            color: #2d3748;
            font-size: 24px;
            margin-bottom: 15px;
        }
        
        p {
            color: #718096;
            font-size: 16px;
            line-height: 1.6;
        }
        
        .back-btn {
            display: inline-block;
            margin-top: 30px;
            padding: 12px 30px;
            background: #fdcb6e;
            color: #2d3748;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="error-icon">❌</div>
        <h1>Oops! Something went wrong</h1>
        <p>{{ $message ?? 'An unexpected error occurred. Please try again later.' }}</p>
        <a href="javascript:history.back()" class="back-btn">Go Back</a>
    </div>
</body>
</html>