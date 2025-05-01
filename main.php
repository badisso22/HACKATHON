<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <link rel="icon" type="image/png" href="Screenshot_2025-05-01_at_11.42.04_PM-removebg.png" />
  <title>Mura</title>
  
  <style>
    :root {
      --primary-color: #9d4edd;
      --primary-color-light: #b76ee8;
      --primary-color-dark: #7b2cbf;
      --background-color: #1f1235;
      --form-background: #3c1642;
      --input-background: #f3f0ff;
      --text-color: #ffffff;
      --text-dark: #333333;
      --text-muted: #cccccc;
      --button-hover: #7b2cbf;
    }

    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
    }

    body {
      width: 100%;
      min-height: 100vh;
      background-color: var(--background-color);
      font-family: "Inter", sans-serif;
      background-image: radial-gradient(circle at 50% 50%, #2a1745 0%, #1f1235 100%);
      color: var(--text-color);
    }

    header {
      width: 100%;
      padding: 20px 40px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      background-color: rgba(0, 0, 0, 0.2);;
    }

    .logo-title {
      display: flex;
      align-items: center;
      gap: 15px;
    }

    .logo {
      width: 60px;
      height: 60px;
      border-radius: 50%;
      display: flex;
      justify-content: center;
      align-items: center;
      font-size: 2rem;
      font-weight: bold;
      color: white;
    }
    img{
        height: 160px;
        margin-top: 40px;
    }

    h1 {
      font-size: 2.2rem;
      font-weight: 900;
      color: white;
      letter-spacing: 2px;
      background: linear-gradient(135deg,white , rgb(159, 106, 229));
      -webkit-background-clip: text;
      background-clip: text;
      -webkit-text-fill-color: transparent;
      text-shadow: 0 2px 10px rgba(157, 78, 221, 0.3);
    }

    .nav-buttons {
      display: flex;
      gap: 15px;
    }

    .btn {
      padding: 10px 20px;
      background: linear-gradient(135deg, var(--primary-color), var(--primary-color-light));
      border: none;
      border-radius: 10px;
      color: white;
      font-size: 1rem;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.3s ease;
      box-shadow: 0 4px 10px rgba(157, 78, 221, 0.3);
      text-decoration: none;
    }

    .btn:hover {
      background: linear-gradient(135deg, var(--button-hover), var(--primary-color));
      transform: translateY(-2px);
      box-shadow: 0 6px 15px rgba(157, 78, 221, 0.4);
    }

    .btn-outline {
      background: transparent;
      border: 2px solid var(--primary-color);
      color: var(--primary-color);
    }

    .btn-outline:hover {
      background: rgba(157, 78, 221, 0.1);
    }

    .page-section {
      height: 100vh;
      display: flex;
      justify-content: center;
      align-items: center;
      padding: 20px 10px;
      text-align: center;
    
    }#first {
    background-color: rgba(194, 155, 225, 0.1);
    }

    #first .description-block {
    background-color: rgba(145, 55, 218, 0.1);
    }
    #third{
        background-color: rgba(194, 155, 225, 0.1);
    }
    #third .description-block {
        background-color: rgba(145, 55, 218, 0.1);
    }

    .description-block {
      background-color: rgba(255, 255, 255, 0.05);
      border: 1px solid rgba(255, 255, 255, 0.1);
      padding: 40px 30px;
      max-width: 650px;
      border-radius: 20px;
      box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
      backdrop-filter: blur(6px);
      animation: fadeIn 1s ease forwards;
      display: flex;
      gap: 20px;
    }

    .description-text {
      font-size: 1.2rem;
      line-height: 1.4;
    }

    h4 {
      font-style: italic;
      margin-top: 20px;
      font-weight: normal;
      color: var(--text-muted);
    }

    .cta-button {
      text-align: center;
      margin-top: 40px;
    }

    .cta-button .btn {
      margin-top: 30px;
    }

    footer {
      width: 100%;
      padding: 30px 20px;
      background-color: rgba(0, 0, 0, 0.2);
    }

    .footer-content {
      max-width: 1200px;
      margin: 0 auto;
      display: flex;
      flex-direction: column;
      align-items: center;
    }

    .footer-links {
      display: flex;
      gap: 30px;
      margin-bottom: 20px;
      flex-wrap: wrap;
      justify-content: center;
    }

    .footer-links a {
      color: var(--text-muted);
      text-decoration: none;
      font-size: 0.9rem;
      transition: color 0.3s ease;
    }

    .footer-links a:hover {
      color: var(--primary-color-light);
    }

    .footer-copyright {
      color: var(--text-muted);
      font-size: 0.8rem;
    }

    @keyframes fadeIn {
      from {
        opacity: 0;
        transform: translateY(20px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    @media (max-width: 768px) {
      header {
        flex-direction: column;
        gap: 20px;
        text-align: center;
      }
      .logo-title {
        justify-content: center;
      }

      .description-block {
        flex-direction: column;
      }

      .description-block img {
        width: 100%;
        margin-top: 20px;
      }
    }

    @media (max-width: 480px) {
      .nav-buttons {
        flex-direction: column;
        width: 100%;
      }

      .btn {
        width: 100%;
        text-align: center;
      }

      .footer-links {
        flex-direction: column;
        align-items: center;
        gap: 15px;
      }
    }
  </style>
</head>
<body>
  <header>
    <div class="logo-title">
      <div class="logo"><img src="Screenshot_2025-05-01_at_11.42.04_PM-removebg.png" alt="Mura"></div>
      <h1>Mura — Where Language Meets Patience</h1>
    </div>
    <form class="nav-buttons">
      <a href="login.php" class="btn btn-outline" >Sign In</a>
      <a href="signup.php" class="btn" >Sign Up</a>
    </div>
  </header>

  <main>
    <section class="page-section" id="first" >
      <div class="description-block">
        <div class="description-text">
          <p>Learning a new language shouldn't feel like a race. At Mura, we believe progress comes with patience, not pressure.</p>
        </div>
        <img src="image1.jpg" alt="Image 1" width="200px" height="auto" />
      </div>
    </section>

    <section class="page-section" id="second">
      <div class="description-block">
        <img src="image2.jpg" alt="Image 2" width="200px" height="auto" />
        <div class="description-text">
          <p>Our daily 1v1 language challenges create a fun and competitive environment that rewards consistency over perfection.</p>
        </div>
      </div>
    </section>

    <section class="page-section" id="third">
      <div class="description-block">
        <div class="description-text">
          <p>With every challenge, you connect, learn, and improve — not for streaks or scores, but for real, lasting fluency.</p>
        </div>
        <img src="image3.jpg" alt="Image 3" width="200px" height="auto" />
      </div>
    </section>

    <section class="page-section" id="fourth">
      <div class="description-block">
        <div class="description-text">
          <h4>-- Step into the arena. Speak slowly, Learn deeply --</h4>
          <div class="cta-button">
            <a href="signup.php" class="btn">Get Started</a>
          </div>
        </div>
      </div>
    </section>
  </main>

  <footer>
    <div class="footer-content">
      <div class="footer-links">
        <a href="#">About Us</a>
        <a href="#">How It Works</a>
        <a href="#">Languages</a>
        <a href="#">Support</a>
        <a href="#">Privacy Policy</a>
        <a href="#">Terms of Service</a>
      </div>
      <div class="footer-copyright">
        © 2025 Mura Language Learning. All rights reserved.
      </div>
    </div>
  </footer>
</body>
</html>

