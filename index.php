<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login | Saha's Solvex</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <style>
    :root {
      --primary: #e74c3c;       /* Brand red */
      --secondary: #f39c12;    /* Golden yellow */
      --dark: #333;           /* Dark text */
      --light: #f9f9f9;       /* Light background */
      --white: #ffffff;
      --gray: #e0e0e0;
      --shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
      --transition: all 0.3s ease;
    }

    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: 'Poppins', sans-serif;
      line-height: 1.6;
      color: var(--dark);
      background-color: var(--light);
      display: flex;
      justify-content: center;
      align-items: center;
      min-height: 100vh;
      padding: 20px;
    }

    .login-container {
      width: 100%;
      max-width: 450px;
      background: var(--white);
      border-radius: 15px;
      box-shadow: var(--shadow);
      overflow: hidden;
      padding: 40px;
      position: relative;
    }

    .login-header {
      text-align: center;
      margin-bottom: 40px;
    }

    .login-header h2 {
      font-size: 2rem;
      color: var(--primary);
      margin-bottom: 10px;
    }

    .login-header p {
      color: #666;
    }

    .login-form .form-group {
      margin-bottom: 25px;
      position: relative;
    }

    .login-form .form-group i {
      position: absolute;
      top: 15px;
      left: 15px;
      color: #999;
    }

    .login-form .form-control {
      width: 100%;
      padding: 15px 15px 15px 45px;
      border: 1px solid var(--gray);
      border-radius: 8px;
      font-family: inherit;
      font-size: 1rem;
      transition: var(--transition);
    }

    .login-form .form-control:focus {
      outline: none;
      border-color: var(--primary);
      box-shadow: 0 0 0 3px rgba(231, 76, 60, 0.2);
    }

    .options {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 25px;
      font-size: 0.9rem;
    }

    .remember-me {
      display: flex;
      align-items: center;
    }

    .remember-me input {
      margin-right: 8px;
    }

    .forgot-password a {
      color: var(--primary);
      text-decoration: none;
      transition: var(--transition);
    }

    .forgot-password a:hover {
      text-decoration: underline;
    }

    .btn {
      display: block;
      width: 100%;
      padding: 15px;
      background: var(--primary);
      color: var(--white);
      border: none;
      border-radius: 8px;
      font-size: 1rem;
      font-weight: 500;
      cursor: pointer;
      transition: var(--transition);
      margin-bottom: 25px;
    }

    .btn:hover {
      background: #c0392b;
      transform: translateY(-3px);
      box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
    }

    .social-login {
      text-align: center;
      margin-bottom: 25px;
    }

    .social-login p {
      color: #666;
      margin-bottom: 15px;
      position: relative;
    }

    .social-login p::before,
    .social-login p::after {
      content: "";
      position: absolute;
      top: 50%;
      width: 30%;
      height: 1px;
      background: var(--gray);
    }

    .social-login p::before {
      left: 0;
    }

    .social-login p::after {
      right: 0;
    }

    .social-icons {
      display: flex;
      justify-content: center;
      gap: 15px;
    }

    .social-icon {
      width: 45px;
      height: 45px;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      color: var(--white);
      font-size: 1.2rem;
      transition: var(--transition);
    }

    .social-icon.facebook {
      background: #3b5998;
    }

    .social-icon.google {
      background: #db4437;
    }

    .social-icon.linkedin {
      background: #0077b5;
    }

    .social-icon:hover {
      transform: translateY(-3px);
    }

    .signup-link {
      text-align: center;
      color: #666;
    }

    .signup-link a {
      color: var(--primary);
      text-decoration: none;
      transition: var(--transition);
    }

    .signup-link a:hover {
      text-decoration: underline;
    }

    /* Responsive Design */
    @media (max-width: 480px) {
      .login-container {
        padding: 30px 20px;
      }
      
      .options {
        flex-direction: column;
        align-items: flex-start;
        gap: 15px;
      }
    }
  </style>
</head>
<body>
  <div class="login-container">
    <div class="login-header">
      <h2>Welcome Back</h2>
      <p>Please login to your account</p>
    </div>

    <form action="php/login.php" method="POST" class="login-form">
      <div class="form-group">
        <i class="fas fa-user"></i>
        <input type="text" name="username" class="form-control" placeholder="Enter Username" required>
      </div>
      
      <div class="form-group">
        <i class="fas fa-lock"></i>
        <input type="password" name="password" class="form-control" placeholder="Enter Password" required>
      </div>

      <div class="options">
        <div class="remember-me">
          <input type="checkbox" id="remember">
          <label for="remember">Remember me</label>
        </div>
        <div class="forgot-password">
          <a href="forgot-password.html">Forgot password?</a>
        </div>
      </div>

      <button type="submit" class="btn">Login</button>

      <div class="social-login">
        <p>Or login with</p>
        <div class="social-icons">
          <a href="#" class="social-icon facebook"><i class="fab fa-facebook-f"></i></a>
          <a href="#" class="social-icon google"><i class="fab fa-google"></i></a>
          <a href="#" class="social-icon linkedin"><i class="fab fa-linkedin-in"></i></a>
        </div>
      </div>

      <div class="signup-link">
        Don't have an account? <a href="signup.html">Sign up here</a>
      </div>
    </form>
  </div>
</body>
</html>