<header class="admin-topbar">
  <div style="display:flex;align-items:center;gap:12px">
    <button class="sidebar-toggle" id="sidebarToggle" onclick="toggleSidebar()">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
    </button>
    <div class="topbar-title">
      <h1><?php echo $pageTitle ?? 'Tổng quan'; ?></h1>
      <p><?php echo $pageSubtitle ?? ''; ?></p>
    </div>
  </div>
  <div class="topbar-actions">
    <div class="topbar-search">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
      <input type="text" placeholder="Tìm kiếm...">
    </div>
    <button class="notif-btn" title="Thông báo">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8A6 6 0 006 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 01-3.46 0"/></svg>
      <span class="notif-badge"></span>
    </button>
    <button class="avatar-btn" title="Admin">A</button>
  </div>
</header>
<script>
function toggleSidebar() {
  const sidebar = document.getElementById('adminSidebar');
  const overlay = document.getElementById('sidebarOverlay');
  if (sidebar) sidebar.classList.toggle('open');
  if (overlay) overlay.classList.toggle('show');
}
</script>
