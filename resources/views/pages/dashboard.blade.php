<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FitScan Dashboard</title>
    <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body>
    <button class="menu-toggle" id="menuToggle">
        <i class="fas fa-bars"></i>
    </button>

    <div class="container">
        <!-- Sidebar -->
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <h2><i class="fas fa-dumbbell"></i> Fitness App</h2>
                <p>Welcome back, Almed!</p>
            </div>

            <nav>
                <ul class="nav-menu">
                    <li class="nav-item active" data-content="dashboard">
                        <i class="fas fa-home"></i>
                        Dashboard
                    </li>
                    <li class="nav-item" data-content="workouts">
                        <i class="fas fa-running"></i>
                        Workouts
                    </li>
                    <li class="nav-item" data-content="nutrition">
                        <i class="fas fa-utensils"></i>
                        Nutrition
                    </li>
                    <li class="nav-item" data-content="progress">
                        <i class="fas fa-chart-line"></i>
                        Progress
                    </li>
                    <li class="nav-item" data-content="settings">
                        <i class="fas fa-cog"></i>
                        Settings
                    </li>
                </ul>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <div class="header">
                <h1>Dashboard</h1>
                <div class="notification-badge">
                    <i class="fas fa-bell"></i>
                    <span>3 New</span>
                </div>
            </div>

            <!-- Dynamic Content Sections -->
            <section class="content-section" id="dashboard">
                <div class="cards-container">
                    <div class="card">
                        <h3><i class="fas fa-walking"></i> Today's Activity</h3>
                        <p>Steps: 8,532</p>
                        <p>Calories Burned: 420</p>
                    </div>
                    <div class="card">
                        <h3><i class="fas fa-calendar-alt"></i> Current Program</h3>
                        <p>Full Body Workout</p>
                        <p>Day 12/30</p>
                    </div>
                </div>
            </section>
        </main>
    </div>

    <script>
        // Toggle sidebar
        const sidebar = document.getElementById('sidebar');
        const menuToggle = document.getElementById('menuToggle');

        menuToggle.addEventListener('click', () => {
            sidebar.classList.toggle('active');
        });

        // Close sidebar when clicking outside on mobile
        document.addEventListener('click', (e) => {
            if (window.innerWidth <= 768 &&
                !sidebar.contains(e.target) &&
                !menuToggle.contains(e.target)) {
                sidebar.classList.remove('active');
            }
        });

        // Handle window resize
        window.addEventListener('resize', () => {
            if (window.innerWidth > 768) {
                sidebar.classList.remove('active');
            }
        });

        // Switch content sections
        document.querySelectorAll('.nav-item').forEach(item => {
            item.addEventListener('click', () => {
                // Remove active class
                document.querySelectorAll('.nav-item').forEach(nav =>
                    nav.classList.remove('active'));

                // Add active class
                item.classList.add('active');

                // Hide all content
                document.querySelectorAll('.content-section').forEach(section =>
                    section.style.display = 'none');

                // Show selected content
                const contentId = item.dataset.content;
                document.getElementById(contentId).style.display = 'block';

                // Close sidebar on mobile
                if (window.innerWidth <= 768) {
                    sidebar.classList.remove('active');
                }
            });
        });
    </script>
</body>

</html>
