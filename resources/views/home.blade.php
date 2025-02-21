<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="description"
        content="FitScan is an AI-driven workout plans and tailored meal suggestions based on your body metrics and goals." />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="icon" href="{{ asset('logo.jpg') }}" />
    <link rel="apple-touch-icon" href="img/apple-touch-icon.png" />
    <link rel="manifest" href="manifest.webmanifest" />
    <link rel="preconnect" href="https://fonts.gstatic.com" />
    <link href="https://fonts.googleapis.com/css2?family=Rubik:wghtسشسسسببلث@400;500;600;700&display=swap"
        rel="stylesheet" />
    <link rel="stylesheet" href="{{ asset('css/style.css') }}" />
    <link rel="stylesheet" href="{{ asset('css/general.css') }}" />
    <link rel="stylesheet" href="{{ asset('css/queries.css') }}" />
    <script type="module" src="https://unpkg.com/ionicons@5.4.0/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule="" src="https://unpkg.com/ionicons@5.4.0/dist/ionicons/ionicons.js"></script>
    <script defer src="{{ asset('js/script.js') }}"></script>
    <title>FitScan &mdash; Train smarter!</title>
</head>

<body>
    <header class="header">
        <a href="#">
            <img class="logo" src="{{ asset('logo.svg') }}" alt="Fit-Scan logo" />
        </a>
        <nav class="main-nav">
            <ul class="main-nav-list">
                <li><a class="main-nav-link" href="#how">How it works</a></li>
                <li><a class="main-nav-link" href="#plans">Plans</a></li>
                <li>
                    <a class="main-nav-link" href="#testimonials">Testimonials</a>
                </li>
                <li><a class="main-nav-link" href="#pricing">Pricing</a></li>
                <li><a class="main-nav-link nav-cta" href="{{ route('signup') }}">Sign Up</a></li>
            </ul>
        </nav>
        <button class="btn-mobile-nav">
            <ion-icon class="icon-mobile-nav" name="menu-outline"></ion-icon>
            <ion-icon class="icon-mobile-nav" name="close-outline"></ion-icon>
        </button>
    </header>
    <main>
        <section class="section-hero">
            <div class="hero">
                <div class="hero-text-box">
                    <h1 class="heading-primary">
                        Achieve Your Fitness Goals with AI-Powered Workouts & Meal Plans
                    </h1>
                    <p class="hero-description">
                        Your personalized fitness companion—offering AI-driven workout plans and tailored meal
                        suggestions based on your body metrics and goals. Stay on track, train smarter, and fuel your
                        progress effortlessly.
                    </p>
                    <a href="{{ route('signup') }}" class="btn btn--full margin-right-sm">Start training well</a>
                    <a href="#how" class="btn btn--outline">Learn more &darr;</a>

                </div>
                <div class="hero-img-box">
                    <picture>
                        <source srcset="{{ asset('hero.jpg') }}" type="image/jpg" />
                        <source srcset="{{ asset('hero.jpg') }}" type="image/jpg" />
                        <img src="{{ asset('hero.jpg') }}" class="hero-img" alt="Man enjoying training" />
                    </picture>
                </div>
            </div>
        </section>
        <section class="section-how" id="how">
            <div class="container">
                <span class="subheading">How It Works</span>
                <h2 class="heading-secondary">
                    Your daily dose of health in 3 simple steps
                </h2>
            </div>
            <div class="container grid grid--2-cols">
                <div class="step-text-box">
                    <p class="step-number">01</p>
                    <h3 class="heading-tertiary">
                        Tell us about your body metrics
                    </h3>
                    <p class="step-description">
                        Forget guessing your fitness needs! Just enter your weight, height, and age, and our AI will
                        analyze your body composition to create the perfect workout and meal plan tailored just for you.
                    </p>
                </div>
                <div class="step-img-box">
                    <img src="{{ asset('app-screen-1.svg') }}" class="step-img"
                        alt="iphone app preference selection screen" />
                </div>
                <div class="step-img-box">
                    <img src="{{ asset('app-screen-2.svg') }}" class="step-img"
                        alt="iphone app meal approving plan screen" />
                </div>
                <div class="step-text-box">
                    <p class="step-number">02</p>
                    <h3 class="heading-tertiary">Choose where you train</h3>
                    <p class="step-description">
                        Do you prefer working out at the gym or in the comfort of your home? Select your preferred
                        training environment, and we’ll generate a plan that fits your lifestyle—no excuses, just
                        results!
                    </p>
                </div>
                <div class="step-text-box">
                    <p class="step-number">03</p>
                    <h3 class="heading-tertiary">Set your fitness goal</h3>
                    <p class="step-description">
                        Want to build muscle or lose weight? No matter your goal, our AI-powered system will design the
                        best workout and meal plans to help you achieve it efficiently and sustainably.
                    </p>
                </div>
                <div class="step-img-box">
                    <img src="{{ asset('app-screen-3.svg') }}" class="step-img" alt="iphone app delivery screen" />
                </div>
            </div>
        </section>
        <section class="section-meals" id="plans">
            <div class="container center-text">
                <span class="subheading">Plans</span>
                <h2 class="heading-secondary">
                    FitScan AI chooses from many recipes & exercises
                </h2>
            </div>
            <div class="container grid grid--3-cols margin-bottom-md">
                <div class="meal">
                    <img class="meal-img" src="{{ asset('meals/meal-1.jpg') }}" alt="Japanese Gyozas" />
                    <div class="meal-content">
                        <div class="meal-tags">
                            <span class="tag tag--vegetarian">Vegetarian</span>
                        </div>
                        <p class="meal-title">Japanese Gyozas</p>
                        <ul class="meal-attributes">
                            <li class="meal-attribute">
                                <ion-icon class="meal-icon" name="flame-outline"></ion-icon>
                                <span><strong>650</strong> calories</span>
                            </li>
                            <li class="meal-attribute">
                                <ion-icon class="meal-icon" name="restaurant-outline"></ion-icon>
                                <span>NutriScore &copy; <strong>74</strong></span>
                            </li>
                            <li class="meal-attribute">
                                <ion-icon class="meal-icon" name="star-outline"></ion-icon>
                                <span><strong>4.9</strong> rating (537)</span>
                            </li>
                        </ul>
                    </div>
                </div>
                <div class="meal">
                    <img class="meal-img" src="{{ asset('meals/meal-2.jpg') }}" alt="Avocado Salad" />
                    <div class="meal-content">
                        <div class="meal-tags">
                            <span class="tag tag--vegan">Vegan</span>
                            <span class="tag tag--paleo">Paleo</span>
                        </div>
                        <p class="meal-title">Avocado Salad</p>
                        <ul class="meal-attributes">
                            <li class="meal-attribute">
                                <ion-icon class="meal-icon" name="flame-outline"></ion-icon>
                                <span><strong>400</strong> calories</span>
                            </li>
                            <li class="meal-attribute">
                                <ion-icon class="meal-icon" name="restaurant-outline"></ion-icon>
                                <span>NutriScore &copy; <strong>92</strong></span>
                            </li>
                            <li class="meal-attribute">
                                <ion-icon class="meal-icon" name="star-outline"></ion-icon>
                                <span><strong>4.9</strong> rating (441)</span>
                            </li>
                        </ul>
                    </div>
                </div>
                <div class="diets">
                    <h3 class="heading-tertiary">Works with any diet:</h3>
                    <ul class="list">
                        <li class="list-item">
                            <ion-icon class="list-icon" name="checkmark-outline"></ion-icon>
                            <span>Vegetarian</span>
                        </li>
                        <li class="list-item">
                            <ion-icon class="list-icon" name="checkmark-outline"></ion-icon>
                            <span>Vegan</span>
                        </li>
                        <li class="list-item">
                            <ion-icon class="list-icon" name="checkmark-outline"></ion-icon>
                            <span>Pescatarian</span>
                        </li>
                        <li class="list-item">
                            <ion-icon class="list-icon" name="checkmark-outline"></ion-icon>
                            <span>Gluten-free</span>
                        </li>
                        <li class="list-item">
                            <ion-icon class="list-icon" name="checkmark-outline"></ion-icon>
                            <span>Lactose-free</span>
                        </li>
                        <li class="list-item">
                            <ion-icon class="list-icon" name="checkmark-outline"></ion-icon>
                            <span>Keto</span>
                        </li>
                        <li class="list-item">
                            <ion-icon class="list-icon" name="checkmark-outline"></ion-icon>
                            <span>Paleo</span>
                        </li>
                        <li class="list-item">
                            <ion-icon class="list-icon" name="checkmark-outline"></ion-icon>
                            <span>Low FODMAP</span>
                        </li>
                        <li class="list-item">
                            <ion-icon class="list-icon" name="checkmark-outline"></ion-icon>
                            <span>Kid-friendly</span>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="container all-recipes">
                <a href="#" class="link">See all recipes &rarr;</a>
            </div>
        </section>
        <section class="section-testimonials" id="testimonials">
            <div class="testimonials-container">
                <span class="subheading">Testimonials</span>
                <h2 class="heading-secondary">Once you try it, you can't go back</h2>
                <div class="testimonials">
                    <figure class="testimonial">
                        <img class="testimonial-img" src="{{ asset('customers/customer.jpg') }}"
                            alt="Photo of customer Ahmed Hesham" />
                        <blockquote class="testimonial-text">
                            Accurate, insightful, and super easy to use! Fit Scan gives me personalized meal plans
                            instantly, it's like having a nutritionist in my pocket
                        </blockquote>
                        <p class="testimonial-name">&mdash; Ahmed Hesham</p>
                    </figure>
                    <figure class="testimonial">
                        <img class="testimonial-img" src="{{ asset('customers/customer.jpg') }}"
                            alt="Photo of customer Kareem Saif" />
                        <blockquote class="testimonial-text">
                            No more guesswork, just results! I love how Fit Scan analyzes my body data and suggests meals that actually match my goals.
                        </blockquote>
                        <p class="testimonial-name">&mdash; Kareem Saif</p>
                    </figure>
                    <figure class="testimonial">
                        <img class="testimonial-img" src="{{ asset('customers/customer.jpg') }}"
                            alt="Photo of customer Ahmed Saed" />
                        <blockquote class="testimonial-text">
                            Smart, simple, and incredibly effective! With Fit Scan, I get customized meal plans without any effort, it feels like magic!
                        </blockquote>
                        <p class="testimonial-name">&mdash; Ahmed Saed</p>
                    </figure>
                    <figure class="testimonial">
                        <img class="testimonial-img" src="{{ asset('customers/customer.jpg') }}"
                            alt="Photo of customer Mohamed Tamer" />
                        <blockquote class="testimonial-text">
                            Finally, a fitness app that truly understands me! Fit Scan takes my body scans and turns them into real, actionable nutrition plans.
                        </blockquote>
                        <p class="testimonial-name">&mdash; Mohamed Tamer</p>
                    </figure>
                </div>
            </div>
            <div class="gallery">
                <figure class="gallery-item">
                    <img src="{{ asset('gallery/gallery-1.jpg') }}" alt="Photo of beautifully arranged food" />
                </figure>
                <figure class="gallery-item">
                    <img src="{{ asset('gallery/gallery-2.jpg') }}" alt="Photo of beautifully arranged food" />
                </figure>
                <figure class="gallery-item">
                    <img src="{{ asset('gallery/gallery-3.jpg') }}" alt="Photo of beautifully arranged food" />
                </figure>
                <figure class="gallery-item">
                    <img src="{{ asset('gallery/gallery-4.jpg') }}" alt="Photo of beautifully arranged food" />
                </figure>
                <figure class="gallery-item">
                    <img src="{{ asset('gallery/gallery-5.jpg') }}" alt="Photo of beautifully arranged food" />
                </figure>
                <figure class="gallery-item">
                    <img src="{{ asset('gallery/gallery-6.jpg') }}" alt="Photo of beautifully arranged food" />
                </figure>
                <figure class="gallery-item">
                    <img src="{{ asset('gallery/gallery-7.jpg') }}" alt="Photo of beautifully arranged food" />
                </figure>
                <figure class="gallery-item">
                    <img src="{{ asset('gallery/gallery-8.jpg') }}" alt="Photo of beautifully arranged food" />
                </figure>
                <figure class="gallery-item">
                    <img src="{{ asset('gallery/gallery-9.jpg') }}" alt="Photo of beautifully arranged food" />
                </figure>
                <figure class="gallery-item">
                    <img src="{{ asset('gallery/gallery-10.jpg') }}" alt="Photo of beautifully arranged food" />
                </figure>
                <figure class="gallery-item">
                    <img src="{{ asset('gallery/gallery-11.jpg') }}" alt="Photo of beautifully arranged food" />
                </figure>
                <figure class="gallery-item">
                    <img src="{{ asset('gallery/gallery-12.jpg') }}" alt="Photo of beautifully arranged food" />
                </figure>
            </div>
        </section>
        <section class="section-pricing" id="pricing">
            <div class="container">
                <span class="subheading">Pricing</span>
                <h2 class="heading-secondary">
                    Training well without breaking the bank
                </h2>
            </div>
            <div class="container grid grid--2-cols margin-bottom-md">
                <div class="pricing-plan pricing-plan--starter">
                    <header class="plan-header">
                        <p class="plan-name">Starter</p>
                        <p class="plan-price"><span>$</span>00</p>
                        <p class="plan-text">per month.</p>
                    </header>
                    <ul class="list">
                        <li class="list-item">
                            <ion-icon class="list-icon" name="checkmark-outline"></ion-icon>
                            <span>1 hour per day</span>
                        </li>
                        <li class="list-item">
                            <ion-icon class="list-icon" name="checkmark-outline"></ion-icon>
                            <span></span>
                        </li>
                        <li class="list-item">
                            <ion-icon class="list-icon" name="checkmark-outline"></ion-icon>
                            <span>It is free</span>
                        </li>
                        <li class="list-item">
                            <ion-icon class="list-icon" name="checkmark-outline"></ion-icon>
                        </li>
                    </ul>
                    <div class="plan-sign-up">
                        <a href="#" class="btn btn--full">Start eating well</a>
                    </div>
                </div>
                <div class="pricing-plan pricing-plan--complete">
                    <header class="plan-header">
                        <p class="plan-name">Complete</p>
                        <p class="plan-price"><span>$</span>00</p>
                        <p class="plan-text">per month.</p>
                    </header>
                    <ul class="list">
                        <li class="list-item">
                            <ion-icon class="list-icon" name="checkmark-outline"></ion-icon>
                            <span><strong>2 hours</strong> per day</span>
                        </li>
                        <li class="list-item">
                            <ion-icon class="list-icon" name="checkmark-outline"></ion-icon><span></span>
                        </li>
                        <li class="list-item">
                            <ion-icon class="list-icon" name="checkmark-outline"></ion-icon>
                            <span></span>
                        </li>
                        <li class="list-item">
                            <ion-icon class="list-icon" name="checkmark-outline"></ion-icon><span>Get access to
                                anything in the app</span>
                        </li>
                    </ul>
                    <div class="plan-sign-up">
                        <a href="#" class="btn btn--full">Start eating well</a>
                    </div>
                </div>
            </div>
            <div class="container grid">
                <aside class="plan-details">
                    Prices include all applicable taxes. You can cancel at any time.
                    Both plans include the following:
                </aside>
            </div>
            <div class="container grid grid--4-cols">
                <div class="feature">
                    <ion-icon class="feature-icon" name="infinite-outline"></ion-icon>
                    <p class="feature-title">Personalized for You</p>
                    <p class="feature-text">
                        Enter your weight, height, and age, and let our AI craft the perfect fitness plan tailored to
                        your needs.
                    </p>
                </div>
                <div class="feature">
                    <ion-icon class="feature-icon" name="nutrition-outline"></ion-icon>
                    <p class="feature-title">Train Where You Want</p>
                    <p class="feature-text">
                        Whether you prefer working out at home or hitting the gym, we adapt to your lifestyle and
                        preferences.
                    </p>
                </div>
                <div class="feature">
                    <ion-icon class="feature-icon" name="leaf-outline"></ion-icon>
                    <p class="feature-title">Your Goal, Your Plan</p>
                    <p class="feature-text">
                        Looking to gain muscle or lose weight? We create a strategy that keeps you on track for success.
                    </p>
                </div>
                <div class="feature">
                    <ion-icon class="feature-icon" name="pause-outline"></ion-icon>
                    <p class="feature-title">Total flexibility</p>
                    <p class="feature-text">
                        Need a break? Pause your plan anytime—no worries, no commitments!
                    </p>
                </div>
            </div>
        </section>
        <section class="section-cta" id="cta">
            <div class="container">
                <div class="cta">
                    <div class="cta-text-box">
                        <h2 class="heading-secondary">Start your fitness journey today – for free!</h2>
                        <p class="cta-text">
                            Achieve your dream body with personalized workouts and meal plans designed just for you.
                            Whether you want to build muscle or lose weight, we’ve got you covered. No commitment, no
                            hassle – and your first plan is on us!
                        </p>
                        <form class="cta-form" name="sign-up" netlify>
                            <div>
                                <label for="full-name">Full Name</label>
                                <input id="full-name" type="text" placeholder="Ahmed Hesham" name="full-name"
                                    required />
                            </div>
                            <div>
                                <label for="email">Email address</label>
                                <input id="email" type="email" placeholder="me@example.com" name="email"
                                    required />
                            </div>
                            <div>
                                <label for="select-where">Where did you hear from us?</label>
                                <select id="select-where" name="select-where" required>
                                    <option value="">Please choose one option:</option>
                                    <option value="friends">Friends and Family</option>
                                    <option value="youtube">YouTube Video</option>
                                    <option value="podcast">Podcast</option>
                                    <option value="ad">Facebook ad</option>
                                    <option value="others">Others</option>
                                </select>
                            </div>
                            <button type="button" class="btn btn--form">Sign up now</button>
                        </form>
                    </div>
                    <div class="cta-img-box" role="img" aria-label="Man enjoying food"></div>
                </div>
            </div>
        </section>
        <footer class="footer">
            <div class="container grid grid--footer">
                <div class="logo-col">
                    <a href="#" class="footer-logo">
                        <img class="logo" src="{{ asset('logo.jpg') }}" alt="FitScan logo" />
                    </a>
                    <ul class="social-links">
                        <li>
                            <a class="footer-link" href="#"><ion-icon class="social-icon"
                                    name="logo-instagram"></ion-icon></a>
                        </li>
                        <li>
                            <a class="footer-link" href="#"><ion-icon class="social-icon"
                                    name="logo-facebook"></ion-icon></a>
                        </li>
                        <li>
                            <a class="footer-link" href="#"><ion-icon class="social-icon"
                                    name="logo-twitter"></ion-icon></a>
                        </li>
                    </ul>
                    <p class="copyright">
                        Copyright &copy; <span class="year"></span> by FitScan, Inc. All
                        rights reserved.
                    </p>
                </div>
                <div class="address-col">
                    <p class="footer-heading">Contact us</p>
                    <address class="contacts">
                        <p class="address">
                            22 Talaat Harb St., 2nd Floor, Down Twon, CAIRO 94107
                        </p>
                        <p>
                            <a class="footer-link" href="tel:415-201-6370">415-201-6370</a>
                            <br />
                            <a class="footer-link" href="mailto:hello@FitScan.com">hello@Fit-Scan.com</a>
                        </p>
                    </address>
                </div>
                <nav class="nav-col">
                    <p class="footer-heading">Account</p>
                    <ul class="footer-nav">
                        <li><a class="footer-link" href="#">Create account</a></li>
                        <li><a class="footer-link" href="#">Sign in</a></li>
                        <li><a class="footer-link" href="#">iOS app</a></li>
                        <li><a class="footer-link" href="#">Android app</a></li>
                    </ul>
                </nav>
                <nav class="nav-col">
                    <p class="footer-heading">Company</p>
                    <ul class="footer-nav">
                        <li><a class="footer-link" href="#">About FitScan</a></li>
                        <li><a class="footer-link" href="#">For Business</a></li>
                        <li><a class="footer-link" href="#">Training partners</a></li>
                        <li><a class="footer-link" href="#">Careers</a></li>
                    </ul>
                </nav>
                <nav class="nav-col">
                    <p class="footer-heading">Resources</p>
                    <ul class="footer-nav">
                        <li><a class="footer-link" href="#">Help center</a></li>
                        <li><a class="footer-link" href="#">Privacy & terms</a></li>
                    </ul>
                </nav>
            </div>
        </footer>
    </main>
</body>

</html>
