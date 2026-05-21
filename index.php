<div class="container-fluid">
  <div class="row">
    <div class="col-12">
      <div class="card mb-4">
        <!-- Header -->
        <div class="card-header d-flex justify-content-between align-items-center">
          <h5 class="card-title mb-0 mode-poa">Transaction POA</h5>
          <div class="ms-auto">
            <button id="btnViewParked" class="btn btn-warning btn-sm">
              <i class="fas fa-archive"></i> View Parked List
            </button>
            <button id="btnFloatItems" class="btn btn-info btn-sm ms-2">
              <i class="fas fa-archive"></i> Float Items List
            </button>
          </div>
        </div>

        <!-- Body -->
        <div class="card-body">
          <!-- Input fields -->
          <div class="row g-2 mb-3">
            <div class="col-md-6">
              <div id="poaStoreWrapper">
                <input type="text" class="form-control txtenterpoa" placeholder="Enter POA" id="txtenterpoa">
              </div>
            </div>
            <div class="col-md-6">
              <input type="text" class="form-control txtscanupc" placeholder="Scan Here" id="txtscanupc">
            </div>
            <div class="col-md-12">
              <input type="text" class="form-control txtstore" placeholder="Shipto name" id="txtstore" readonly>
            </div>
          </div>

          <!-- Table -->
          <div class="table-responsive">
            <table class="table table-bordered table-hover mb-0 tblTransaction" id="tblTransaction">
              <thead class="table-light">
                <tr>
                  <th style="width: 5%;">#</th>
                  <th style="width: 20%;">Material Code</th>
                  <th style="width: 35%;">SKU</th>
                  <th style="width: 20%;">Progress</th>
                  <th style="width: 20%;">Remarks</th>
                </tr>
              </thead>
              <tbody>
                <!-- Dynamic rows go here -->
              </tbody>
            </table>
          </div>
        </div>

        <!-- Combined Footer -->
        <div class="card-footer d-flex flex-wrap justify-content-between align-items-center gap-2">
          <div class="d-flex gap-2">
            <button class="btn btn-success btn-sm" type="button" id="btnSavetransact">Save</button>
            <button class="btn btn-primary btn-sm" type="button" id="btnParktransact">Park</button>
            <button class="btn btn-secondary btn-sm" type="button" id="btnCanceltransact">Cancel</button>
          </div>
          <ul class="pagination pagination-sm m-0">
            <!-- Page numbers here -->
          </ul>
        </div>

      </div>
    </div>
  </div>
</div>




<div class="modal fade" id="modalParkedList" tabindex="-1" aria-labelledby="modalParkedListLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header text-dark">
        <h5 class="modal-title" id="modalParkedListLabel">Parked POA List</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <table class="table table-bordered table-hover" id="tblParkedList">
          <thead class="table-light">
            <tr>
              <th>POA</th>
              <th>Storename</th>
              <th>Date Parked</th>
              <th>Status</th>
              <th>Action</th>
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

<div class="modal fade" id="modalAfloatList" tabindex="-1" aria-labelledby="modalAfloatListLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header text-dark">
        <h5 class="modal-title" id="modalAfloatListLabel">Float Item List</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <table class="table table-bordered table-hover" id="tblAfloatList">
          <thead class="table-light">
            <tr>
              <th>Branch</th>
              <th>Material Code</th>
              <th>SKU</th>
              <th>Scan Qty</th>
              <th>Date</th>
              <th>Action</th>
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


<div class="modal fade" id="modalPOA" tabindex="-1" aria-labelledby="modalPOALabel" aria-hidden="true">
  <div class="modal-dialog modal-sm"> <!-- Changed to modal-sm for smaller size -->
    <div class="modal-content">
      <div class="modal-header text-dark">
        <h5 class="modal-title" id="modalPOALabel">POA Search</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="modalPOAForm">
          <!-- Hidden ID field -->
          <input type="hidden" id="poa-id">

          <!-- These fields are now hidden with style="display: none;" -->
          <div class="row mb-3" style="display: none;">
            <div class="col-md-6">
              <label for="poamtcode" class="form-label">Material Code</label>
              <input type="text" class="form-control" id="poa-mtcode" placeholder="Material Code" readonly>
            </div>
            <div class="col-md-6">
              <label for="poa-sku" class="form-label">SKU</label>
              <input type="text" class="form-control" id="poa-sku" placeholder="SKU" readonly>
            </div>
            <div class="col-md-6">
              <label for="poa-storename" class="form-label">Store Name</label>
              <input type="text" class="form-control" id="poa-storename" placeholder="Store Name" readonly>
            </div>
          </div>

          <!-- POA Number centered -->
          <div class="row mb-3 justify-content-center">
            <div class="col-12 text-center">
              <label for="poa-number" class="form-label">POA Number</label>
              <input type="text" class="form-control" id="poa-number" placeholder="Search POA Number">
            </div>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        <button type="button" class="btn btn-primary" id="update-poa-btn">Update</button>
      </div>
    </div>
  </div>
</div>
