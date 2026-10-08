<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>App Installed Successfully</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
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
        
        .success-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 30px;
            animation: scaleIn 0.5s ease-out 0.2s both;
        }
        
        @keyframes scaleIn {
            from {
                transform: scale(0);
            }
            to {
                transform: scale(1);
            }
        }
        
        .success-icon svg {
            width: 45px;
            height: 45px;
            stroke: white;
            stroke-width: 3;
            stroke-linecap: round;
            stroke-linejoin: round;
            fill: none;
            animation: draw 0.5s ease-out 0.5s both;
        }
        
        @keyframes draw {
            from {
                stroke-dasharray: 100;
                stroke-dashoffset: 100;
            }
            to {
                stroke-dasharray: 100;
                stroke-dashoffset: 0;
            }
        }
        
        h1 {
            color: #2d3748;
            font-size: 28px;
            margin-bottom: 15px;
            font-weight: 700;
        }
        
        .shop-name {
            color: #667eea;
            font-weight: 600;
        }
        
        p {
            color: #718096;
            font-size: 16px;
            line-height: 1.6;
            margin-bottom: 15px;
        }
        
        .info-box {
            background: #f7fafc;
            border-left: 4px solid #667eea;
            padding: 15px;
            border-radius: 8px;
            margin-top: 30px;
            text-align: left;
        }
        
        .info-box h3 {
            color: #2d3748;
            font-size: 14px;
            margin-bottom: 10px;
            font-weight: 600;
        }
        
        .info-box ul {
            list-style: none;
            padding: 0;
        }
        
        .info-box li {
            color: #4a5568;
            font-size: 14px;
            padding: 5px 0;
            padding-left: 20px;
            position: relative;
        }
        
        .info-box li:before {
            content: "✓";
            position: absolute;
            left: 0;
            color: #667eea;
            font-weight: bold;
        }
        
        .dashboard-link {
            display: inline-block;
            margin-top: 30px;
            padding: 14px 35px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            text-decoration: none;
            border-radius: 10px;
            font-weight: 600;
            transition: transform 0.2s, box-shadow 0.2s;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
        }
        
        .dashboard-link:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.6);
        }

        .redirecting {
            margin-top: 20px;
            color: #667eea;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="success-icon">
            <svg viewBox="0 0 52 52">
                <polyline points="14 27 22 35 38 19"/>
            </svg>
        </div>
        
        <h1>Installation Successful!</h1>
        <p>Your app has been successfully installed for</p>
        <p class="shop-name">{{ $shop }}</p>
        
        <div class="info-box">
            <h3>What's Next?</h3>
            <ul>
                <li>Script tags are being injected automatically</li>
                <li>Quote form will appear on product pages</li>
                <li>Dashboard is ready to track quotes</li>
            </ul>
        </div>
        
        <a href="{{ $redirectUrl }}" class="dashboard-link" id="openApp">
            Open App in Shopify Admin
        </a>

        <p class="redirecting">Redirecting automatically in <span id="countdown">3</span> seconds...</p>
    </div>

    <script>
        const redirectUrl = "{{ $redirectUrl }}";
        let countdown = 3;
        
        const countdownElement = document.getElementById('countdown');
        const interval = setInterval(() => {
            countdown--;
            countdownElement.textContent = countdown;
            
            if (countdown <= 0) {
                clearInterval(interval);
                // Use top.location to break out of any iframe
                if (window.top) {
                    window.top.location.href = redirectUrl;
                } else {
                    window.location.href = redirectUrl;
                }
            }
        }, 1000);

        // Also allow manual click
        document.getElementById('openApp').addEventListener('click', function(e) {
            e.preventDefault();
            clearInterval(interval);
            if (window.top) {
                window.top.location.href = redirectUrl;
            } else {
                window.location.href = redirectUrl;
            }
        });
    </script>
</body>
</html>