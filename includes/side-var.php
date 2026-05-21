<!-- Begin Sidebar -->
<aside class="app-sidebar bg-dark d-flex flex-column" data-bs-theme="dark">
  <div class="sidebar-brand d-flex align-items-center justify-content-center py-3">
    <a href="#" class="brand-link text-decoration-none">
      <span class="brand-text fw-bold ms-2">PRS Application</span>
    </a>
  </div>

  <div class="sidebar-wrapper flex-grow-1">
    <nav class="mt-2">
      <ul class="nav nav-pills nav-sidebar flex-column mb-3">
        <!-- Transaction -->
        <li class="nav-item">
          <a href="structure.php?page=Transaction" class="nav-link">
            <i class="bi bi-file-earmark me-2"></i>
            <span>Transact</span>
          </a>
        </li>

        <!-- Transaction List -->
        <li class="nav-item ">
          <a href="structure.php?page=Transactionlist" class="nav-link">
            <i class="bi bi-list-columns-reverse me-2"></i>
            <span>Transact list</span>
          </a>
        </li>

        <!-- User List -->
        <li class="nav-item li-userlist">
          <a href="structure.php?page=Userlist" class="nav-link">
            <i class="bi bi-people-fill me-2"></i>
            <span>User List</span>
          </a>
        </li>

        <!-- Uploaded DBF -->
        <li class="nav-item li-uploading">
          <a href="structure.php?page=Uploading" class="nav-link">
            <i class="bi bi-upload me-2"></i>
            <span>Uploaded DBF</span>
          </a>
        </li>
      </ul>
    </nav>
  </div>

  <!-- Bottom Links -->
  <div class="mt-auto mb-3">
    <ul class="nav nav-pills nav-sidebar flex-column">
      <li class="nav-item">
        <a href="structure.php?page=Aboutus" class="nav-link">
          <i class="bi bi-info-circle me-2"></i>
          <span>About Us</span>
        </a>
      </li>
      <li class="nav-item">
        <a href="#" id="btnLogout" class="nav-link">
          <i class="bi bi-box-arrow-right me-2"></i>
          <span>Logout</span>
        </a>
      </li>
    </ul>
  </div>
</aside>
<!-- End Sidebar -->
