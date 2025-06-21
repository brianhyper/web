<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>404 Not Found</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            background: linear-gradient(135deg, #0a0f2c 0%, #1a1f4c 100%);
            color: #fff;
            font-family: 'Orbitron', 'Segoe UI', Arial, sans-serif;
            margin: 0;
            height: 100vh;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .container {
            text-align: center;
            background: rgba(10, 15, 44, 0.85);
            border: 2px solid #00f0ff;
            border-radius: 20px;
            box-shadow: 0 0 40px 10px #00f0ff44, 0 0 80px 10px #ff003c22;
            padding: 60px 40px;
            position: relative;
        }
        .glow {
            color: #00f0ff;
            text-shadow: 0 0 10px #00f0ff, 0 0 20px #ff003c;
            font-size: 7rem;
            font-weight: bold;
            letter-spacing: 0.2em;
            margin-bottom: 10px;
        }
        .subtitle {
            color: #ff003c;
            font-size: 2rem;
            margin-bottom: 30px;
            text-shadow: 0 0 10px #ff003c88;
        }
        .desc {
            color: #b3eaff;
            font-size: 1.2rem;
            margin-bottom: 40px;
        }
        .btn {
            background: linear-gradient(90deg, #00f0ff 0%, #ff003c 100%);
            color: #fff;
            border: none;
            border-radius: 30px;
            padding: 15px 40px;
            font-size: 1.1rem;
            font-family: inherit;
            cursor: pointer;
            box-shadow: 0 0 20px #00f0ff88;
            transition: background 0.3s, box-shadow 0.3s;
            text-decoration: none;
        }
        .btn:hover {
            background: linear-gradient(90deg, #ff003c 0%, #00f0ff 100%);
            box-shadow: 0 0 40px #ff003c88;
        }
        @import url('https://fonts.googleapis.com/css2?family=Orbitron:wght@700&display=swap');
    </style>
</head>
<body>
    <div class="container">
        <div class="glow">404</div>
        <div class="subtitle">Page Not Found</div>
        <div class="desc">
            The page you are looking for does not exist.<br>
            Maybe you took a wrong turn in the matrix.
        </div>
        <a href="/" class="btn">Return Home</a>
    </div>
</body>
</html>