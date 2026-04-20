<div class="sidebar-overlay" id="sidebar-overlay"></div>

<div class="mobile-topbar">
    <button class="sidebar-toggle" id="sidebar-toggle" aria-label="Toggle menu">
        <span></span>
        <span></span>
        <span></span>
    </button>
    <a href="../index.html" class="mobile-topbar-logo">
        <img src="../images/Smiths_Grips_Logo.svg" alt="Smith's Grips Logo">
    </a>
</div>

<nav class="admin-sidebar" id="admin-sidebar">
    <div class="sidebar-brand">
        <h2>Smith's Golf Grips</h2>
        <span>Admin Panel</span>
    </div>
    <ul>
        <li><a href="orders.php">Orders</a></li>
        <li><a href="grips.php">Grips</a></li>
        <li><a href="pricing.php">Pricing</a></li>
        <li><a href="admins.php">Admins</a></li>
        <li><a href="../index.html">← Back to Site</a></li>
        <li class="logout"><a href="logout.php">Logout</a></li>
    </ul>
</nav>

<script>
(function () {
    const sidebar = document.getElementById('admin-sidebar');
    const overlay = document.getElementById('sidebar-overlay');
    const toggle  = document.getElementById('sidebar-toggle');

    function toggleSidebar() {
        const isOpen = sidebar.classList.toggle('open');
        overlay.classList.toggle('active', isOpen);
        toggle.classList.toggle('open', isOpen);
    }

    toggle.addEventListener('click', toggleSidebar);
    overlay.addEventListener('click', toggleSidebar);
})();
</script>
