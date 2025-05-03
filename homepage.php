<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Mura - Language Learning at Your Pace</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;900&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  
  <style>
    :root {
      --primary: #9d4edd;
      --primary-light: #b76ee8;
      --primary-dark: #7b2cbf;
      --bg: #1f1235;
      --bg-light: #3c1642;
      --text: #ffffff;
      --text-dark: #333333;
      --text-muted: #cccccc;
    }

    * {box-sizing: border-box; margin: 0; padding: 0; scroll-behavior: smooth;}
    
    body {
      width: 100%; min-height: 100vh; font-family: "Inter", sans-serif;
      background: radial-gradient(circle at 50% 50%, #2a1745 0%, #1f1235 100%);
      color: var(--text); overflow-x: hidden;
    }

    /* Particles */
    .particles-container {
      position: fixed; top: 0; left: 0; width: 100%; height: 100%;
      overflow: hidden; z-index: -1; pointer-events: none;
    }
    .particle {
      position: absolute; border-radius: 50%;
      background: rgba(157, 78, 221, 0.2); pointer-events: none;
    }

    /* Header */
    header {
      width: 100%; padding: 20px 40px; display: flex; justify-content: space-between;
      align-items: center; background-color: rgba(31, 18, 53, 0.8);
      backdrop-filter: blur(10px); position: fixed; top: 0; left: 0; z-index: 100;
      transition: all 0.3s ease;
    }
    header.scrolled {padding: 15px 40px; box-shadow: 0 5px 20px rgba(0, 0, 0, 0.3);}
    
    .logo-title {
      display: flex; align-items: center; gap: 15px; position: relative;
    }
    
    .logo {
      width: 50px; height: 50px; border-radius: 50%; display: flex;
      justify-content: center; align-items: center;
      
      box-shadow: 0 4px 10px rgba(157, 78, 221, 0.3); transition: all 0.3s ease;
    }
    
    .logo-right {
      position: absolute; right: -60px; top: 50%; transform: translateY(-50%);
      width: 40px; height: 40px;
    }
    
    header.scrolled .logo {width: 40px; height: 40px;}
    .logo img { height: 30px; object-fit:fill;}
    
    h1 {
      font-size: 1.8rem; font-weight: 900; letter-spacing: 1px;
      background: linear-gradient(135deg, #ffffff, var(--primary-light));
      -webkit-background-clip: text; background-clip: text;
      -webkit-text-fill-color: transparent; transition: all 0.3s ease;
    }
    header.scrolled h1 {font-size: 1.5rem;}
    
    .nav-links {display: flex; gap: 30px; list-style: none;}
    .nav-links li a {
      color: var(--text); text-decoration: none; font-weight: 500;
      transition: all 0.3s ease; position: relative;
    }
    .nav-links li a::after {
      content: ''; position: absolute; bottom: -5px; left: 0;
      width: 0; height: 2px; background: var(--primary-light);
      transition: width 0.3s ease;
    }
    .nav-links li a:hover::after {width: 100%;}
    
    .nav-buttons {display: flex; gap: 15px;}
    
    .btn {
      padding: 10px 20px; background: linear-gradient(135deg, var(--primary), var(--primary-light));
      border: none; border-radius: 10px; color: white; font-size: 1rem;
      font-weight: 600; cursor: pointer; transition: all 0.3s ease;
      box-shadow: 0 4px 10px rgba(157, 78, 221, 0.3); text-decoration: none;
      display: inline-block;
    }
    .btn:hover {
      background: linear-gradient(135deg, var(--primary-dark), var(--primary));
      transform: translateY(-2px); box-shadow: 0 6px 15px rgba(157, 78, 221, 0.4);
    }
    .btn-outline {
      background: transparent; border: 2px solid var(--primary);
      color: var(--primary);
    }
    .btn-outline:hover {background: rgba(157, 78, 221, 0.1);}
    
    .mobile-menu-toggle {
      display: none; background: none; border: none; color: var(--text);
      font-size: 1.5rem; cursor: pointer;
    }

    /* Hero section */
    .hero {
      height: 100vh; display: flex; align-items: center; justify-content: center;
      text-align: center; padding: 0 20px; position: relative; overflow: hidden;
    }
    .hero-content {max-width: 800px; z-index: 1;}
    
    .hero-title {
      font-size: 4rem; font-weight: 900; margin-bottom: 20px;
      background: linear-gradient(135deg, #ffffff, var(--primary-light));
      -webkit-background-clip: text; background-clip: text;
      -webkit-text-fill-color: transparent; opacity: 0;
      transform: translateY(30px); animation: fadeInUp 1s ease forwards 0.3s;
    }
    .hero-subtitle {
      font-size: 1.5rem; color: var(--text-muted); margin-bottom: 40px;
      max-width: 600px; margin-left: auto; margin-right: auto; opacity: 0;
      transform: translateY(30px); animation: fadeInUp 1s ease forwards 0.6s;
    }
    .hero-buttons {
      display: flex; gap: 20px; justify-content: center; opacity: 0;
      transform: translateY(30px); animation: fadeInUp 1s ease forwards 0.9s;
    }
    .hero-buttons .btn {padding: 15px 30px; font-size: 1.1rem;}
    
    .hero-image {
      position: absolute; bottom: 0; width: 100%; height: 100px;
      background: url('wave.svg') repeat-x; background-size: 1000px 100px;
      animation: wave 20s linear infinite; opacity: 0.6;
    }
    .hero-image:nth-child(2) {
      bottom: 10px; animation: wave 15s linear reverse infinite; opacity: 0.4;
    }
    .hero-image:nth-child(3) {
      bottom: 20px; animation: wave 30s linear infinite; opacity: 0.2;
    }
    
    @keyframes wave {
      0% {background-position-x: 0;}
      100% {background-position-x: 1000px;}
    }

    /* Sections */
    .section {padding: 100px 20px;}
    .section-title {
      text-align: center; font-size: 2.5rem; margin-bottom: 60px;
      background: linear-gradient(135deg, #ffffff, var(--primary-light));
      -webkit-background-clip: text; background-clip: text;
      -webkit-text-fill-color: transparent;
    }
    
    /* Features */
    .features-container {
      display: flex; flex-wrap: wrap; justify-content: center;
      gap: 40px; max-width: 1200px; margin: 0 auto;
    }
    .feature-card {
      background: rgba(60, 22, 66, 0.5); border-radius: 20px; padding: 30px;
      width: 350px; box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
      border: 1px solid rgba(157, 78, 221, 0.2); backdrop-filter: blur(10px);
      transition: all 0.3s ease; opacity: 0; transform: translateY(30px);
    }
    .feature-card.visible {opacity: 1; transform: translateY(0);}
    .feature-card:hover {
      transform: translateY(-10px);
      box-shadow: 0 15px 40px rgba(157, 78, 221, 0.3);
    }
    .feature-icon {
      width: 70px; height: 70px;
      background: linear-gradient(135deg, var(--primary), var(--primary-light));
      border-radius: 50%; display: flex; align-items: center; justify-content: center;
      margin-bottom: 20px; font-size: 1.8rem; color: white;
    }
    .feature-title {font-size: 1.5rem; margin-bottom: 15px;}
    .feature-description {color: var(--text-muted); line-height: 1.6;}

    /* How it works */
    .how-it-works {background: rgba(31, 18, 53, 0.5);}
    .steps-container {
      max-width: 900px; margin: 0 auto; position: relative;
    }
    .step {
      display: flex; margin-bottom: 80px; position: relative;
      opacity: 0; transform: translateX(-30px);
    }
    .step.visible {opacity: 1; transform: translateX(0); transition: all 0.6s ease;}
    .step:nth-child(even) {flex-direction: row-reverse; transform: translateX(30px);}
    .step:nth-child(even).visible {transform: translateX(0);}
    .step:last-child {margin-bottom: 0;}
    
    .step-number {
      width: 60px; height: 60px;
      background: linear-gradient(135deg, var(--primary), var(--primary-light));
      border-radius: 50%; display: flex; align-items: center; justify-content: center;
      font-size: 1.5rem; font-weight: bold; color: white; flex-shrink: 0;
      margin-right: 30px; box-shadow: 0 5px 15px rgba(157, 78, 221, 0.3);
    }
    .step:nth-child(even) .step-number {margin-right: 0; margin-left: 30px;}
    
    .step-content {
      background: rgba(60, 22, 66, 0.5); border-radius: 20px; padding: 30px;
      flex-grow: 1; box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
      border: 1px solid rgba(157, 78, 221, 0.2); backdrop-filter: blur(10px);
    }
    .step-title {font-size: 1.5rem; margin-bottom: 15px;}
    .step-description {color: var(--text-muted); line-height: 1.6;}

    /* CTA section */
    .cta {background: rgba(31, 18, 53, 0.5); text-align: center;}
    .cta-content {
      max-width: 800px; margin: 0 auto; background: rgba(60, 22, 66, 0.7);
      border-radius: 20px; padding: 60px 40px; box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
      border: 1px solid rgba(157, 78, 221, 0.3); backdrop-filter: blur(10px);
    }
    .cta-title {
      font-size: 2.5rem; margin-bottom: 20px;
      background: linear-gradient(135deg, #ffffff, var(--primary-light));
      -webkit-background-clip: text; background-clip: text;
      -webkit-text-fill-color: transparent;
    }
    .cta-subtitle {
      font-size: 1.2rem; color: var(--text-muted); margin-bottom: 40px;
      max-width: 600px; margin-left: auto; margin-right: auto;
    }
    .cta-buttons {display: flex; gap: 20px; justify-content: center;}
    .cta-buttons .btn {padding: 15px 30px; font-size: 1.1rem;}

    /* Footer */
    footer {background-color: rgba(31, 18, 53, 0.8); padding: 60px 20px 30px;}
    .footer-content {
      max-width: 1200px; margin: 0 auto; display: grid;
      grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 40px;
    }
    .footer-column h3 {
      font-size: 1.2rem; margin-bottom: 20px; position: relative;
      padding-bottom: 10px;
    }
    .footer-column h3::after {
      content: ''; position: absolute; bottom: 0; left: 0;
      width: 40px; height: 2px; background: var(--primary);
    }
    .footer-links {list-style: none;}
    .footer-links li {margin-bottom: 10px;}
    .footer-links a {
      color: var(--text-muted); text-decoration: none;
      transition: color 0.3s ease;
    }
    .footer-links a:hover {color: var(--primary-light);}
    
    .footer-bottom {
      max-width: 1200px; margin: 40px auto 0; padding-top: 20px;
      border-top: 1px solid rgba(255, 255, 255, 0.1); display: flex;
      justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 20px;
    }
    .footer-copyright {color: var(--text-muted); font-size: 0.9rem;}
    .footer-bottom-links {display: flex; gap: 20px;}
    .footer-bottom-links a {
      color: var(--text-muted); text-decoration: none;
      font-size: 0.9rem; transition: color 0.3s ease;
    }
    .footer-bottom-links a:hover {color: var(--primary-light);}

    /* Screen Reader */
    .screen-reader {
      position: fixed; z-index: 9999; bottom: 20px; right: 20px;
      transition: all 0.3s ease;
    }
    .screen-reader-toggle {
      width: 60px; height: 60px; border-radius: 50%;
      background: linear-gradient(135deg, var(--primary), var(--primary-light));
      color: white; display: flex; align-items: center; justify-content: center;
      cursor: pointer; box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3);
      border: none; transition: all 0.3s ease;
    }
    .screen-reader-toggle:hover {
      transform: scale(1.05); box-shadow: 0 6px 15px rgba(0, 0, 0, 0.4);
    }
    .screen-reader-toggle i {font-size: 24px;}
    
    .screen-reader-panel {
      position: absolute; bottom: 70px; right: 0; width: 300px;
      background: var(--bg-light); border-radius: 15px; padding: 20px;
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
      border: 1px solid rgba(157, 78, 221, 0.2); display: none;
      transform: translateY(20px); opacity: 0; transition: all 0.3s ease;
    }
    .screen-reader-panel.active {display: block; transform: translateY(0); opacity: 1;}
    
    .screen-reader-header {
      display: flex; justify-content: space-between; align-items: center;
      margin-bottom: 15px; padding-bottom: 10px;
      border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    }
    .screen-reader-title {font-weight: 600; font-size: 1.1rem; color: var(--text);}
    .screen-reader-close {
      background: none; border: none; color: var(--text-muted);
      cursor: pointer; font-size: 1.2rem; transition: color 0.3s ease;
    }
    .screen-reader-close:hover {color: var(--text);}
    
    .screen-reader-controls {display: flex; gap: 10px; margin-bottom: 15px;}
    .control-btn {
      flex: 1; padding: 8px; border-radius: 8px; background: rgba(157, 78, 221, 0.2);
      color: var(--text); border: none; cursor: pointer; transition: all 0.2s ease;
      display: flex; align-items: center; justify-content: center;
    }
    .control-btn:hover:not(:disabled) {background: rgba(157, 78, 221, 0.3);}
    .control-btn:disabled {opacity: 0.5; cursor: not-allowed;}
    .control-btn i {font-size: 16px;}
    
    .highlight-text {
      background-color: rgba(157, 78, 221, 0.3); border-radius: 3px;
      padding: 2px 0; transition: background-color 0.3s ease;
    }

    /* Animations */
    @keyframes fadeInUp {
      from {opacity: 0; transform: translateY(30px);}
      to {opacity: 1; transform: translateY(0);}
    }
    
    /* Responsive */
    @media (max-width: 992px) {
      .hero-title {font-size: 3rem;}
      .step {
        flex-direction: column !important; align-items: flex-start;
        margin-left: 30px;
      }
      .step-number {margin-bottom: 20px;}
      .step:nth-child(even) .step-number {margin-left: 0;}
    }
    
    @media (max-width: 768px) {
      header {padding: 15px 20px;}
      .logo-right {display: none;}
      .nav-links {
        display: none; position: absolute; top: 100%; left: 0; width: 100%;
        background: rgba(31, 18, 53, 0.95); padding: 20px; flex-direction: column;
        align-items: center; box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
      }
      .nav-links.active {display: flex;}
      .mobile-menu-toggle {display: block;}
      .hero-title {font-size: 2.5rem;}
      .hero-subtitle {font-size: 1.2rem;}
      .hero-buttons, .cta-buttons {flex-direction: column; align-items: center;}
      .hero-buttons .btn, .cta-buttons .btn {width: 100%;}
      .feature-card {width: 100%;}
      .footer-bottom {flex-direction: column; text-align: center;}
      .footer-bottom-links {justify-content: center;}
    }
    
    @media (max-width: 480px) {
      h1 {font-size: 1.3rem;}
      .hero-title {font-size: 2rem;}
      .section-title {font-size: 2rem;}
      .screen-reader {bottom: 10px; right: 10px;}
      .screen-reader-toggle {width: 50px; height: 50px;}
      .screen-reader-panel {width: 280px;}
    }
  </style>
</head>
<body>
  <div class="particles-container" id="particles"></div>
  
  <header id="header">
    <div class="logo-title">
      <div class="logo">
        <img src="image/mura.png" alt="Mura Logo">
      </div>
      <h1>Mura — Language Learning at Your Pace</h1>
    </div>
    
    <nav>
      <ul class="nav-links" id="navLinks">
        <li><a href="#features">Features</a></li>
        <li><a href="#how-it-works">How It Works</a></li>
        <li><a href="#contact">Contact</a></li>
      </ul>
    </nav>
    
    <div class="nav-buttons">
      <a href="Login/signin.php" class="btn btn-outline">Sign In</a>
      <a href="Login/signup.php" class="btn">Sign Up</a>
    </div>
    
    <button class="mobile-menu-toggle" id="mobileMenuToggle">
      <i class="fas fa-bars"></i>
    </button>
  </header>

  <main>
    <section class="hero" id="hero">
      <div class="hero-content">
        <h2 class="hero-title">Learn Languages Without Pressure</h2>
        <p class="hero-subtitle">
          Join thousands of learners discovering new languages with confidence and at their own pace. 
          Our approach focuses on natural progress, not deadlines.
        </p>
        <div class="hero-buttons">
          <a href="Login/signup.php" class="btn">Get Started Free</a>
          <a href="#features" class="btn btn-outline">Explore Features</a>
        </div>
      </div>
      <div class="hero-image"></div>
      <div class="hero-image"></div>
      <div class="hero-image"></div>
    </section>

    <section class="section features" id="features">
      <h2 class="section-title">Why Choose Mura</h2>
      <div class="features-container">
        <div class="feature-card">
          <div class="feature-icon"><i class="fas fa-clock"></i></div>
          <h3 class="feature-title">Learn at Your Pace</h3>
          <p class="feature-description">
            No deadlines or pressure. Our platform adapts to your schedule and learning style, 
            allowing you to progress naturally and confidently.
          </p>
        </div>
        
        <div class="feature-card">
          <div class="feature-icon"><i class="fas fa-comments"></i></div>
          <h3 class="feature-title">Practice Speaking</h3>
          <p class="feature-description">
            Improve your conversation skills with our AI language partners that adapt to your 
            proficiency level and provide instant feedback.
          </p>
        </div>
        
        <div class="feature-card">
          <div class="feature-icon"><i class="fas fa-brain"></i></div>
          <h3 class="feature-title">Personalized Learning</h3>
          <p class="feature-description">
            Our adaptive algorithm creates a customized learning path based on your interests, 
            goals, and progress to maximize retention.
          </p>
        </div>
      </div>
    </section>

    <section class="section how-it-works" id="how-it-works">
      <h2 class="section-title">How Mura Works</h2>
      <div class="steps-container">
        <div class="step">
          <div class="step-number">1</div>
          <div class="step-content">
            <h3 class="step-title">Choose Your Language</h3>
            <p class="step-description">
              Select from over 30 languages and set your learning goals. Whether you're a beginner 
              or looking to improve existing skills, we have the right path for you.
            </p>
          </div>
        </div>
        
        <div class="step">
          <div class="step-number">2</div>
          <div class="step-content">
            <h3 class="step-title">Take the Assessment</h3>
            <p class="step-description">
              Complete a quick assessment so we can understand your current level and create a 
              personalized learning plan tailored to your needs.
            </p>
          </div>
        </div>
        
        <div class="step">
          <div class="step-number">3</div>
          <div class="step-content">
            <h3 class="step-title">Daily Practice</h3>
            <p class="step-description">
              Engage in short, focused daily sessions that fit your schedule. Our bite-sized lessons 
              are designed for maximum retention without overwhelming you.
            </p>
          </div>
        </div>
      </div>
    </section>

    <section class="section cta" id="cta">
      <div class="cta-content">
        <h2 class="cta-title">Ready to Start Your Language Journey?</h2>
        <p class="cta-subtitle">
          Join Mura today and experience a new way of learning languages — one where progress comes naturally, 
          without pressure or deadlines.
        </p>
        <div class="cta-buttons">
          <a href="signup.html" class="btn">Get Started Free</a>
          <a href="#features" class="btn btn-outline">Learn More</a>
        </div>
      </div>
    </section>
  </main>

  <footer id="contact">
    <div class="footer-content">
      <div class="footer-column">
        <h3>Mura</h3>
        <p>Language learning at your own pace, without pressure.</p>
      </div>
      
      <div class="footer-column">
        <h3>Languages</h3>
        <ul class="footer-links">
          <li><a href="#">Japanese</a></li>
          <li><a href="#">Spanish</a></li>
          <li><a href="#">French</a></li>
          <li><a href="#">German</a></li>
          <li><a href="#">Italien</a></li>
          <li><a href="#">Japanese</a></li>
        </ul>
      </div>
      
      <div class="footer-column">
        <h3>Company</h3>
        <ul class="footer-links">
          <li><a href="#">About Us</a></li>
          <li><a href="#">Careers</a></li>
          <li><a href="#">Blog</a></li>
        </ul>
      </div>
      
      <div class="footer-column">
        <h3>Support</h3>
        <ul class="footer-links">
          <li><a href="#">Help Center</a></li>
          <li><a href="#">Contact Us</a></li>
          <li><a href="#">Community</a></li>
        </ul>
      </div>
    </div>
    
    <div class="footer-bottom">
      <div class="footer-copyright">
        © 2025 Mura Language Learning. All rights reserved.
      </div>
      <div class="footer-bottom-links">
        <a href="#">Privacy Policy</a>
        <a href="#">Terms of Service</a>
      </div>
    </div>
  </footer>

  <!-- Screen Reader Component -->
  <div class="screen-reader" id="screenReader">
    <button class="screen-reader-toggle" id="screenReaderToggle" aria-label="Activate screen reader">
      <i class="fas fa-volume-high"></i>
    </button>
    
    <div class="screen-reader-panel" id="screenReaderPanel">
      <div class="screen-reader-header">
        <div class="screen-reader-title">Screen Reader</div>
        <button class="screen-reader-close" id="screenReaderClose" aria-label="Close screen reader panel">
          <i class="fas fa-xmark"></i>
        </button>
      </div>
      
      <div class="screen-reader-controls">
        <button class="control-btn" id="playBtn" aria-label="Start reading"><i class="fas fa-play"></i></button>
        <button class="control-btn" id="pauseBtn" aria-label="Pause reading" disabled><i class="fas fa-pause"></i></button>
        <button class="control-btn" id="stopBtn" aria-label="Stop reading" disabled><i class="fas fa-stop"></i></button>
        <button class="control-btn" id="prevBtn" aria-label="Previous sentence" disabled><i class="fas fa-backward"></i></button>
        <button class="control-btn" id="nextBtn" aria-label="Next sentence" disabled><i class="fas fa-forward"></i></button>
      </div>
    </div>
  </div>

  <script>
    document.addEventListener('DOMContentLoaded', function() {
      // Create floating particles
      const particlesContainer = document.getElementById('particles');
      for (let i = 0; i < 30; i++) {
        const particle = document.createElement('div');
        particle.className = 'particle';
        const size = Math.random() * 5 + 3;
        particle.style.width = `${size}px`;
        particle.style.height = `${size}px`;
        particle.style.left = `${Math.random() * 100}%`;
        particle.style.top = `${Math.random() * 100}%`;
        particle.style.opacity = Math.random() * 0.5 + 0.1;
        const duration = Math.random() * 20 + 10;
        const delay = Math.random() * 5;
        particle.style.animation = `float ${duration}s ease-in-out infinite ${delay}s`;
        particlesContainer.appendChild(particle);
      }

      // Header scroll effect
      const header = document.getElementById('header');
      window.addEventListener('scroll', function() {
        if (window.scrollY > 50) {
          header.classList.add('scrolled');
        } else {
          header.classList.remove('scrolled');
        }
      });

      // Mobile menu toggle
      const mobileMenuToggle = document.getElementById('mobileMenuToggle');
      const navLinks = document.getElementById('navLinks');
      
      if (mobileMenuToggle) {
        mobileMenuToggle.addEventListener('click', function() {
          navLinks.classList.toggle('active');
          const icon = this.querySelector('i');
          icon.className = navLinks.classList.contains('active') ? 'fas fa-times' : 'fas fa-bars';
        });
      }

      // Smooth scroll for navigation links
      document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
          e.preventDefault();
          const target = document.querySelector(this.getAttribute('href'));
          if (target) {
            window.scrollTo({
              top: target.offsetTop - 80,
              behavior: 'smooth'
            });
            
            // Close mobile menu if open
            if (navLinks.classList.contains('active')) {
              navLinks.classList.remove('active');
              mobileMenuToggle.querySelector('i').className = 'fas fa-bars';
            }
          }
        });
      });

      // Animate elements on scroll
      const featureCards = document.querySelectorAll('.feature-card');
      const steps = document.querySelectorAll('.step');
      
      const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
          if (entry.isIntersecting) {
            entry.target.classList.add('visible');
          }
        });
      }, { threshold: 0.1 });
      
      featureCards.forEach(card => {
        observer.observe(card);
      });
      
      steps.forEach(step => {
        observer.observe(step);
      });

      // Screen Reader Functionality
      const screenReader = {
        isOpen: false,
        isReading: false,
        isPaused: false,
        utterance: null,
        sentences: [],
        currentSentence: 0,
        voices: [],
        highlightedElements: [],
        
        elements: {
          toggle: document.getElementById('screenReaderToggle'),
          panel: document.getElementById('screenReaderPanel'),
          close: document.getElementById('screenReaderClose'),
          playBtn: document.getElementById('playBtn'),
          pauseBtn: document.getElementById('pauseBtn'),
          stopBtn: document.getElementById('stopBtn'),
          prevBtn: document.getElementById('prevBtn'),
          nextBtn: document.getElementById('nextBtn'),
          status: document.getElementById('readerStatus')
        },
        
        init: function() {
          if ('speechSynthesis' in window) {
            this.loadVoices();
            if (window.speechSynthesis.onvoiceschanged !== undefined) {
              window.speechSynthesis.onvoiceschanged = this.loadVoices.bind(this);
            }
            this.setupEventListeners();
          } else {
            this.elements.toggle.style.display = 'none';
          }
        },
        
        loadVoices: function() {
          this.voices = window.speechSynthesis.getVoices();
        },
        
        setupEventListeners: function() {
          this.elements.toggle.addEventListener('click', this.togglePanel.bind(this));
          this.elements.close.addEventListener('click', this.closePanel.bind(this));
          this.elements.playBtn.addEventListener('click', this.startReading.bind(this));
          this.elements.pauseBtn.addEventListener('click', this.pauseReading.bind(this));
          this.elements.stopBtn.addEventListener('click', this.stopReading.bind(this));
          this.elements.prevBtn.addEventListener('click', this.previousSentence.bind(this));
          this.elements.nextBtn.addEventListener('click', this.nextSentence.bind(this));
        },
        
        togglePanel: function() {
          this.isOpen = !this.isOpen;
          this.elements.panel.classList.toggle('active', this.isOpen);
          
          if (this.isOpen && this.sentences.length === 0) {
            this.extractPageContent();
          }
        },
        
        closePanel: function() {
          this.isOpen = false;
          this.elements.panel.classList.remove('active');
        },
        
        extractPageContent: function() {
          const mainContent = document.querySelector('main') || document.body;
          const textNodes = [];
          const walk = document.createTreeWalker(
            mainContent,
            NodeFilter.SHOW_TEXT,
            { acceptNode: function(node) {
                if (node.parentElement.offsetHeight === 0 || node.textContent.trim() === '') {
                  return NodeFilter.FILTER_REJECT;
                }
                return NodeFilter.FILTER_ACCEPT;
              }
            },
            false
          );
          
          let node;
          while (node = walk.nextNode()) {
            if (node.parentElement.tagName === 'SCRIPT' || node.parentElement.tagName === 'STYLE') {
              continue;
            }
            const text = node.textContent.trim();
            if (text) {
              textNodes.push({
                node: node,
                text: text
              });
            }
          }
          
          this.sentences = [];
          let currentSentence = '';
          let currentNodes = [];
          
          textNodes.forEach(item => {
            const sentenceFragments = item.text.split(/([.!?]+\s)/g);
            for (let i = 0; i < sentenceFragments.length; i++) {
              const fragment = sentenceFragments[i];
              if (!fragment) continue;
              currentSentence += fragment;
              currentNodes.push(item.node);
              if (fragment.match(/[.!?]+\s$/)) {
                if (currentSentence.trim()) {
                  this.sentences.push({
                    text: currentSentence.trim(),
                    nodes: [...currentNodes]
                  });
                }
                currentSentence = '';
                currentNodes = [];
              }
            }
          });
          
          if (currentSentence.trim()) {
            this.sentences.push({
              text: currentSentence.trim(),
              nodes: [...currentNodes]
            });
          }
        },
        
        startReading: function() {
          if (this.sentences.length === 0) {
            this.extractPageContent();
            if (this.sentences.length === 0) return;
          }
          
          window.speechSynthesis.cancel();
          this.removeHighlighting();
          
          this.utterance = new SpeechSynthesisUtterance(this.sentences[this.currentSentence].text);
          
          if (this.voices.length > 0) {
            this.utterance.voice = this.voices[0];
          }
          
          this.utterance.rate = 1;
          this.utterance.pitch = 1;
          this.utterance.volume = 1;
          
          this.utterance.onend = () => {
            if (this.currentSentence < this.sentences.length - 1) {
              this.currentSentence++;
              this.startReading();
            } else {
              this.isReading = false;
              this.removeHighlighting();
              this.currentSentence = 0;
              this.updateControlButtons();
            }
          };
          
          this.highlightCurrentSentence();
          window.speechSynthesis.speak(this.utterance);
          this.isReading = true;
          this.isPaused = false;
          this.updateControlButtons();
        },
        
        pauseReading: function() {
          if (this.isReading && !this.isPaused) {
            window.speechSynthesis.pause();
            this.isPaused = true;
            this.updateControlButtons();
          }
        },
        
        resumeReading: function() {
          if (this.isReading && this.isPaused) {
            window.speechSynthesis.resume();
            this.isPaused = false;
            this.updateControlButtons();
          }
        },
        
        stopReading: function() {
          window.speechSynthesis.cancel();
          this.isReading = false;
          this.isPaused = false;
          this.removeHighlighting();
          this.currentSentence = 0;
          this.updateControlButtons();
        },
        
        nextSentence: function() {
          if (this.currentSentence < this.sentences.length - 1) {
            window.speechSynthesis.cancel();
            this.removeHighlighting();
            this.currentSentence++;
            if (this.isReading) {
              this.startReading();
            }
          }
        },
        
        previousSentence: function() {
          if (this.currentSentence > 0) {
            window.speechSynthesis.cancel();
            this.removeHighlighting();
            this.currentSentence--;
            if (this.isReading) {
              this.startReading();
            }
          }
        },
        
        highlightCurrentSentence: function() {
          this.removeHighlighting();
          const currentSentenceData = this.sentences[this.currentSentence];
          if (!currentSentenceData || !currentSentenceData.nodes) return;
          
          currentSentenceData.nodes.forEach(node => {
            const parent = node.parentNode;
            if (!parent) return;
            
            const highlightSpan = document.createElement('span');
            highlightSpan.className = 'highlight-text';
            highlightSpan.textContent = node.textContent;
            
            parent.replaceChild(highlightSpan, node);
            
            this.highlightedElements.push({
              span: highlightSpan,
              originalNode: node,
              parent: parent
            });
          });
          
          if (this.highlightedElements.length > 0) {
            this.highlightedElements[0].span.scrollIntoView({
              behavior: 'smooth',
              block: 'center'
            });
          }
        },
        
        removeHighlighting: function() {
          this.highlightedElements.forEach(item => {
            if (item.span.parentNode) {
              item.parent.replaceChild(item.originalNode, item.span);
            }
          });
          this.highlightedElements = [];
        },
        
        updateControlButtons: function() {
          this.elements.playBtn.disabled = this.isReading && !this.isPaused;
          this.elements.pauseBtn.disabled = !this.isReading || this.isPaused;
          this.elements.stopBtn.disabled = !this.isReading;
          this.elements.prevBtn.disabled = this.currentSentence === 0;
          this.elements.nextBtn.disabled = this.currentSentence === this.sentences.length - 1;
        }
      };
      
      // Initialize screen reader
      screenReader.init();
    });
  </script>
</body>
</html>
