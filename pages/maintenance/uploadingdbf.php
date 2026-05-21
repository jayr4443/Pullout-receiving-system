<div class="container-fluid">
  <div class="row">
    <div class="col-12">
      <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
          <h3 class="card-title m-0">Uploaded DBF</h3>
          <div class="ms-auto">
            <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#uploadDbfModal">
              <i class="fas fa-upload"></i> Upload DBF
            </button>
          </div>
        </div>
        <div class="card-body">
          <div class="search-box mb-4">
            <input type="text" id="searchInput" placeholder="Search by Brand or Model" onkeyup="searchUsers()">
          </div>
          <div class="table-responsive"  style="max-height: 250px; overflow-y: auto;">
          <table class="table table-bordered" id="tbl_uploadeddbf">
            <thead>
              <tr>
                <th>Vendo</th>
                <th>UPC</th>
                <th>SKU</th>
                <th>Brand</th>
                <th>Model</th>
                <th>Desc</th>
                <th>SRP</th>
                <th>Promo</th>
                <th>Remarks</th>
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


<!-- Upload Excel Modal -->
<div class="modal fade" id="uploadDbfModal" tabindex="-1" aria-labelledby="uploadDbfModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="uploadDbfModalLabel">Upload Excel File</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form id="uploadDbfForm" enctype="multipart/form-data">
        <div class="modal-body">
          <div class="mb-3">
            <label for="dbfFile" class="form-label">Select Excel File (.xls, .xlsx, .csv)</label>
            <input type="file" class="form-control" id="dbfFile" name="dbfFile" accept=".xls,.xlsx,.csv" required>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary btn-sm" id="btn_uploaddbf">Upload</button>
        </div>
      </form>
    </div>
  </div>
</div>

