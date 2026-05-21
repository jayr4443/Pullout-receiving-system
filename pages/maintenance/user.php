<div class="container-fluid">
  <div class="row">
    <div class="col-12">
      <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
          <h3 class="card-title m-0">User List</h3>
          <div class="ms-auto">
            <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addUserModal">
              <i class="fas fa-user-plus"></i> Add New User
            </button>
          </div>
        </div>
        <div class="card-body">
          <div class="search-box mb-4">
            <input type="text" id="searchInput" placeholder="Search by name or username" onkeyup="searchUsers()">
          </div>
          <div class="table-responsive"  style="max-height: 250px; overflow-y: auto;">
          <table class="table table-bordered" id="tbl_userlist">
            <thead>
              <tr>
                <th>Name</th>
                <th>Username</th>
                <th>Userlevel</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody>
              <!-- Dynamic rows will be populated here -->
            </tbody>
          </table>
        </div>
        </div>
        <div class="card-footer clearfix">
          <ul class="pagination pagination-sm m-0 float-end" id="pagination">
           <!-- JS will inject pagination here -->
          </ul>
        </div>
      </div>
    </div>
  </div>
</div>


<div class="modal fade" id="addUserModal" tabindex="-1" aria-labelledby="addUserModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="addUserModalLabel">Add New User</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="addUserForm">
          <div class="mb-3">
            <label for="name" class="form-label">Full Name</label>
            <input type="text" class="form-control" id="txtname" required>
          </div>
          <div class="mb-3">
            <label for="username" class="form-label">Username</label>
            <input type="text" class="form-control" id="txtusername" required>
          </div>
          <div class="mb-3">
            <label for="password" class="form-label">Password</label>
            <input type="password" class="form-control" id="txtpassword" required>
          </div>
          <div class="mb-3">
            <label for="userlevel" class="form-label">User Level</label>
            <select class="form-select" id="txtuserlevel" required>
              <option value="Admin">Admin</option>
              <option value="User">User</option>
            </select>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" class="btn btn-primary btn-sm" id="saveUserBtn">Save User</button>
      </div>
    </div>
  </div>
</div>
