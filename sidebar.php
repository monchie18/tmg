<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <!-- Font Awesome CDN for Professional Icons -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A==" crossorigin="anonymous" referrerpolicy="no-referrer" />
  <!-- Google Fonts for Typography -->
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
  <style>
    /* General Styles */
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      font-family: 'Poppins', sans-serif;
    }

    body {
      background-color: #f3f4f6;
      color: #333;
      overflow-x: hidden;
    }

    /* Sidebar Styles */
    .sidebar {
      width: 260px;
      background: linear-gradient(180deg, #1e3a8a 0%, #2b5dc9 70%, #3b82f6 100%);
      color: #fff;
      height: 100vh;
      position: fixed;
      top: 0;
      left: 0;
      padding: 25px 20px;
      box-shadow: 4px 0 20px rgba(0, 0, 0, 0.2);
      transition: all 0.3s ease-in-out;
      z-index: 1000;
      overflow-y: auto;
    }

    .sidebar.collapsed {
      width: 80px;
    }

    .sidebar::-webkit-scrollbar {
      width: 6px;
    }

    .sidebar::-webkit-scrollbar-thumb {
      background-color: rgba(255, 255, 255, 0.3);
      border-radius: 3px;
    }

    .sidebar-header {
      text-align: center;
      margin-bottom: 35px;
      padding-bottom: 20px;
      border-bottom: 2px solid rgba(255, 255, 255, 0.3);
      position: relative;
    }

    .sidebar-header h4 {
      font-size: 1.4rem;
      font-weight: 700;
      color: #facc15;
      text-transform: uppercase;
      letter-spacing: 1px;
      transition: opacity 0.3s ease, transform 0.3s ease;
    }

    .sidebar.collapsed .sidebar-header h4 {
      opacity: 0;
      transform: translateX(-20px);
    }

    .sidebar-toggle {
      position: absolute;
      top: 20px;
      right: -45px;
      background: linear-gradient(135deg, #2563eb, #3b82f6);
      color: white;
      border: none;
      border-radius: 0 10px 10px 0;
      padding: 10px 12px;
      cursor: pointer;
      box-shadow: 3px 3px 8px rgba(0, 0, 0, 0.25);
      transition: transform 0.3s ease;
      display: none;
    }

    .sidebar-toggle:hover {
      transform: scale(1.1);
    }

    .sidebar-nav {
      list-style: none;
      padding: 0;
      margin: 0;
    }

    .sidebar-nav li {
      margin-bottom: 10px;
    }

    .sidebar-nav a {
      display: flex;
      align-items: center;
      color: #fff;
      padding: 14px 18px;
      border-radius: 10px;
      text-decoration: none;
      font-size: 1rem;
      font-weight: 500;
      transition: all 0.3s ease-in-out;
      position: relative;
      overflow: hidden;
    }

    .sidebar-nav a::before {
      content: '';
      position: absolute;
      top: 0;
      left: -100%;
      width: 100%;
      height: 100%;
      background: rgba(255, 255, 255, 0.1);
      transition: all 0.4s ease;
      z-index: 0;
    }

    .sidebar-nav a:hover::before {
      left: 0;
    }

    .sidebar-nav a i {
      margin-right: 15px;
      width: 22px;
      text-align: center;
      font-size: 1.2rem; /* Slightly larger for better visibility */
      transition: transform 0.3s ease;
    }

    .sidebar-nav a:hover i {
      transform: translateX(5px);
    }

    .sidebar-nav a:hover {
      background: rgba(255, 255, 255, 0.2);
      transform: translateX(5px);
    }

    .sidebar-nav a.active {
      background: #2563eb;
      font-weight: 600;
      box-shadow: inset 0 0 10px rgba(0, 0, 0, 0.2);
    }

    .sidebar-nav a.active::before {
      display: none;
    }

    .sidebar.collapsed .sidebar-nav a span {
      display: none;
    }

    .sidebar.collapsed .sidebar-nav a {
      justify-content: center;
      padding: 14px;
    }

    .sidebar.collapsed .sidebar-nav a i {
      margin-right: 0;
      transform: scale(1.2);
    }

    .logout-link {
      position: absolute;
      bottom: 25px;
      width: calc(100% - 40px);
    }

    .logout-link a {
      display: flex;
      align-items: center;
      color: #ff4444;
      padding: 14px 18px;
      border-radius: 10px;
      text-decoration: none;
      font-size: 1rem;
      font-weight: 500;
      transition: all 0.3s ease-in-out;
      background: rgba(255, 68, 68, 0.1);
    }

    .logout-link a:hover {
      background: rgba(255, 68, 68, 0.2);
      transform: translateX(5px);
    }

    .sidebar.collapsed .logout-link a {
      justify-content: center;
      padding: 14px;
    }

    .sidebar.collapsed .logout-link a span {
      display: none;
    }

    @media (max-width: 768px) {
      .sidebar {
        width: 80px;
        transform: translateX(-100%);
        transition: transform 0.3s ease-in-out;
      }

      .sidebar.open {
        transform: translateX(0);
      }

      .sidebar-header h4 {
        opacity: 0;
        transform: translateX(-20px);
      }

      .sidebar-nav a span {
        display: none;
      }

      .sidebar-nav a {
        justify-content: center;
        padding: 14px;
      }

      .sidebar-nav a i {
        margin-right: 0;
        transform: scale(1.2);
      }

      .logout-link a span {
        display: none;
      }

      .sidebar-toggle {
        display: block;
      }

      .sidebar-toggle.active {
        transform: rotate(180deg);
      }

      .sidebar-toggle i {
        font-size: 1.3rem;
      }
    }
  </style>
</head>
<body>
  <!-- Sidebar -->
  <div class="sidebar" id="sidebar">
    <div class="sidebar-header">
      <h4>Traffic System</h4>
      <button class="sidebar-toggle" id="sidebarToggle">
        <i class="fas fa-bars"></i>
      </button>
    </div>
    <ul class="sidebar-nav">
      <li>
        <a href="dashboard.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>">
          <i class="fas fa-tachometer-alt"></i>
          <span>Dashboard</span>
        </a>
      </li>
      <li>
        <a href="records.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'records.php' ? 'active' : ''; ?>">
          <i class="fas fa-file-alt"></i>
          <span>Traffic Citations</span>
        </a>
      </li>
      <li>
        <a href="driver_records.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'driver_records.php' ? 'active' : ''; ?>">
          <i class="fas fa-users"></i>
          <span>Driver Records</span>
        </a>
      </li>
      <li>
        <a href="index.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>">
          <i class="fas fa-plus-circle"></i>
          <span>Add Citation</span>
        </a>
      </li>
    </ul>
    <div class="logout-link">
      <a href="logout.php">
        <i class="fas fa-sign-out-alt"></i>
        <span>Logout</span>
      </a>
    </div>
  </div>

  <!-- Initialize Sidebar Toggle -->
  <script>
    const sidebar = document.getElementById('sidebar');
    const sidebarToggle = document.getElementById('sidebarToggle');

    sidebarToggle.addEventListener('click', () => {
      sidebar.classList.toggle('collapsed');
      sidebarToggle.classList.toggle('active');
      if (window.innerWidth <= 768) {
        sidebar.classList.toggle('open');
      }
    });

    // Close sidebar if clicked outside on mobile
    document.addEventListener('click', (e) => {
      if (window.innerWidth <= 768 && !sidebar.contains(e.target) && !sidebarToggle.contains(e.target) && sidebar.classList.contains('open')) {
        sidebar.classList.remove('open');
        sidebarToggle.classList.remove('active');
      }
    });
  </script>
</body>
</html>