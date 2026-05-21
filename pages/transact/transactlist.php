<div class="container-fluid">
  <div class="row">
    <div class="col-12">
      <div class="card mb-4">
       <div class="card-header d-flex justify-content-between align-items-center">
          <h3 class="card-title mb-0">Transaction List</h3>
          <button id="btnReport" class="btn btn-danger btn-sm ms-auto">
            <i class="fas fa-archive"></i> Report
          </button>
        </div>


        <div class="card-body">
        <div class="search-box mb-4">
            <input type="text" id="searchInput" placeholder="Search by POA Number or Status" onkeyup="searchUsers()">
          </div>
          <div class="table-responsive"  style="max-height: 250px; overflow-y: auto;">

          <table class="table table-bordered" id="tbl_transactionlist">
            <thead>
              <tr>
                <th>POA</th>
                <th>Storename</th>
                <th>Scan Qty</th>
                <th>Status</th>
                <th>User Scan</th>
                <th>Date</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody>
              <!-- Dynamic rows go here -->
            </tbody>
          </table>
        </div>
        </div>
        <!-- Pagination -->
        <div class="card-footer clearfix">
          <ul class="pagination pagination-sm m-0 float-end" id="pagination">
           <!-- JS will inject pagination here -->
          </ul>
        </div>

      </div>
    </div>
  </div>
</div>


<div class="modal fade" id="modalTransactList" tabindex="-1" aria-labelledby="modalTransactListLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header text-dark">
        <h5 class="modal-title" id="modalTransactListLabel">Transact POA List</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <table class="table table-bordered table-hover" id="tblTransactList">
          <thead class="table-light">
            <tr>
              <th>Material</th>
              <th>SKU</th>
              <th>Scan Qty</th>
              <th>Status</th>
              <th id="thAction">Action</th>
            </tr>
          </thead>
          <tbody>
            <!-- Filled dynamically via JS -->
          </tbody>
        </table>
      </div>
      <div class="modal-footer">
        <button class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>


<div class="modal fade" id="modalTransactListupdate" tabindex="-1" aria-labelledby="modalTransactListLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header text-dark">
        <h5 class="modal-title" id="modalTransactListLabel">Transact POA Update</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body">
        <form id="formUpdateTransaction">
          <input type="hidden" id="updateId">
          <input type="hidden" id="poanumber">
          <input type="hidden" id="remarksid">

          <div class="row g-3 align-items-end">
            <div class="col-md-4">
              <label for="updateMaterialCode" class="form-label">Material Code</label>
              <input type="text" class="form-control" id="updateMaterialCode" readonly>
            </div>

            <div class="col-md-4">
              <label for="updateSku" class="form-label">SKU</label>
              <input type="text" class="form-control" id="updateSku" readonly>
            </div>

            <div class="col-md-4">
              <label for="updateQty" class="form-label">Quantity</label>
              <input type="number" class="form-control" id="updateQty" placeholder="Enter new quantity" min="0" required>
            </div>
          </div>
        </form>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-primary" id="btn-updatemtcodeqty">Save Changes</button>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>




<!-- Report Modal -->
<div class="modal fade" id="reportModal" tabindex="-1" aria-labelledby="reportModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">

      <div class="modal-header">
        <h5 class="modal-title" id="reportModalLabel">Generate Report</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body">
        <form id="reportForm">
          <div class="row">
            <div class="col-md-6 mb-3">
              <label for="dateFrom" class="form-label">Date From</label>
              <input type="date" class="form-control" id="dateFrom" name="dateFrom" required>
            </div>
            <div class="col-md-6 mb-3">
              <label for="dateTo" class="form-label">Date To</label>
              <input type="date" class="form-control" id="dateTo" name="dateTo" required>
            </div>
          </div>
        </form>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" form="reportForm" id='btngeneratereport' class="btn btn-primary">Generate</button>
      </div>

    </div>
  </div>
</div>
