<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - CPSU Campus Navigator</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #1a2c3e 0%, #0f1a24 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .login-container {
            width: 100%;
            max-width: 420px;
            padding: 20px;
        }
        
        .login-card {
            background: white;
            border-radius: 24px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            overflow: hidden;
        }
        
        .login-header {
            background: linear-gradient(135deg, #0057a3 0%, #003d73 100%);
            color: white;
            padding: 35px 30px;
            text-align: center;
        }
        
        .login-header h1 {
            font-size: 1.8rem;
            margin-bottom: 8px;
            font-weight: 700;
        }
        
        .login-header p {
            opacity: 0.85;
            font-size: 0.85rem;
        }
        
        .login-body {
            padding: 35px 30px;
        }
        
        .form-group {
            margin-bottom: 24px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #1a2c3e;
            font-size: 0.85rem;
        }
        
        .form-group input {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            font-size: 0.95rem;
            transition: all 0.3s;
        }
        
        .form-group input:focus {
            outline: none;
            border-color: #0057a3;
            box-shadow: 0 0 0 3px rgba(0, 87, 163, 0.1);
        }
        
        .checkbox-group {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 25px;
        }
        
        .checkbox-group label {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 0.85rem;
            color: #5c6f87;
            cursor: pointer;
        }
        
        .btn-login {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #0057a3 0%, #003d73 100%);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s;
        }
        
        .btn-login:hover {
            transform: translateY(-2px);
        }
        
        .alert {
            padding: 12px 16px;
            border-radius: 12px;
            margin-bottom: 20px;
            font-size: 0.85rem;
        }
        
        .alert-error {
            background: #fee2e2;
            color: #dc2626;
            border-left: 4px solid #dc2626;
        }
        
        .alert-success {
            background: #dcfce7;
            color: #16a34a;
            border-left: 4px solid #16a34a;
        }
        
        .campus-icon {
            font-size: 3rem;
            margin-bottom: 10px;
        }
        
        .footer-note {
            text-align: center;
            margin-top: 20px;
            color: #94a3b8;
            font-size: 0.75rem;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <div class="campus-icon">🏫</div>
                <h1>CPSU Admin</h1>
                <p>Campus Navigation System</p>
            </div>
            <div class="login-body">
                @if(session('error'))
                    <div class="alert alert-error">{{ session('error') }}</div>
                @endif
                
                @if(session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif
                
                @if($errors->any())
                    <div class="alert alert-error">{{ $errors->first() }}</div>
                @endif
                
                <form method="POST" action="{{ route('post-login') }}">
                    @csrf
                    
                    <div class="form-group">
                        <label>Username or Email</label>
                        <input type="text" name="username" value="{{ old('username') }}" placeholder="Enter your username or email" required autofocus>
                    </div>
                    
                    <div class="form-group">
                        <label>Password</label>
                        <input type="password" name="password" placeholder="Enter your password" required>
                    </div>
                    
                    <div class="checkbox-group">
                        <label>
                            <input type="checkbox" name="remember"> Remember me
                        </label>
                    </div>
                    
                    <button type="submit" class="btn-login">Login to Admin Panel</button>
                </form>
            </div>
        </div>
        <div class="footer-note">
            Central Philippines State University - Kabankalan City
        </div>
    </div>
</body>
</html>