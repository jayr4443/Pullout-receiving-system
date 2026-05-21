<div class="content-wrapper">
        <div class="content pt-3 px-3">
          <div class="container-fluid">
            <div class="row">
              <div class="col-12">
              <?php
                  // First check if page parameter exists
                  if (isset($_GET['page'])) {
                      $page = $_GET['page'];
                      // echo $page;

                      switch ($page) {
                          case 'Transaction':
                              require_once 'index.php';
                              break;
                          case 'Transactionlist':
                              require_once './pages/transact/transactlist.php';
                              break;
                          case 'Userlist':
                              require_once './pages/maintenance/user.php';
                              break;
                          case 'Uploading':
                              require_once './pages/maintenance/uploadingdbf.php';
                              break;
                          case 'Report':
                              require_once './pages/report/report.php';
                              break;
                          case 'Aboutus':
                              require_once './pages/aboutus/aboutus.php';
                              break;
                          default:
                              require_once 'pages/page404.php';
                      }
                  } else {
                      require_once './pages/transact/transactlist.php';
                  }
                  ?>
              </div>
            </div>
          </div>
        </div>
      </div>
