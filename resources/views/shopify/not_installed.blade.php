<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>App Not Installed</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
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
            animation: slideIn 0.6s ease-out;
        }
        
        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .warning-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 30px;
            font-size: 45px;
        }
        
        h1 {
            color: #2d3748;
            font-size: 28px;
            margin-bottom: 15px;
            font-weight: 700;
        }
        
        .shop-name {
            color: #f5576c;
            font-weight: 600;
        }
        
        p {
            color: #718096;
            font-size: 16px;
            line-height: 1.6;
            margin-bottom: 15px;
        }
        
        .install-btn {
            display: inline-block;
            margin-top: 30px;
            padding: 14px 35px;
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            color: white;
            text-decoration: none;
            border-radius: 10px;
            font-weight: 600;
            transition: transform 0.2s, box-shadow 0.2s;
            box-shadow: 0 4px 15px rgba(245, 87, 108, 0.4);
        }
        
        .install-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(245, 87, 108, 0.6);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="warning-icon">⚠️</div>
        
        <h1>App Not Installed</h1>
        <p>The ShowQuote app is not installed for</p>
        <p class="shop-name">{{ $shop }}</p>
        <p>Please install the app to start collecting quotes from customers.</p>
        
        <a href="{{ config('shopify.url') }}/shopify/install?shop={{ $shop }}" class="install-btn">
            Install App Now
        </a>
    </div>
</body>
</html>