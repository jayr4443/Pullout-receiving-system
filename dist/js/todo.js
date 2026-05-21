$(document).ready(function () {
  let page_url = window.location.pathname.split("/")[window.location.pathname.split("/").length - 1];


  if (page_url !== "login.php" && localStorage.userUsername == undefined) {
      location.replace('./login.php');
      return false;
  }
  if (localStorage.userLevel !== 'Admin') {
    $('#btnViewTransact').hide();
    $('#btnReport').hide();

  }
  const urlParams = new URLSearchParams(window.location.search);
  const pages = urlParams.get('page');
  var page;
  let currentPage = 1;
  const itemsPerPage = 5;
  var editUserId;
  let editMode = false;



  // Remove existing active classes first
    $('.nav-link').removeClass('active');
    if (page) {
      // Add the 'active' class to the corresponding nav-link
      $('.li-' + page.toLowerCase()).addClass('active');
    }
    if (localStorage.userLevel === 'User') {
      $('.li-userlist').hide();
      $('.li-uploading').hide();
    }
    //Transactions
    function validatePoa(poa, search) {
      if (poa.length < 3) return; // Don't validate too early
      $.ajax({
        type: 'POST',
        url: './includes/query.php',
        data: {
          action: 'checkpoaexist',
          term: poa,
          search: search
        },
        success: function (res) {
          const json = JSON.parse(res);

          if (json.status_code === "409") {
            if (search === 1) {
              Swal.fire({
                icon: 'error',
                title: 'Invalid POA',
                text: 'This POA number is not available!'
              });
              $('#txtenterpoa').val('').focus();
            } else {
              Swal.fire({
                icon: 'error',
                title: 'Invalid POA',
                text: 'This POA number is not available!'
              });
              $('#poa-number').val('').focus();
            }
          } else if (json.status_code === "404") {
            if (search === 1) {
              Swal.fire({
                icon: 'warning',
                title: 'Duplicate POA',
                text: 'This POA number is already transacted!'
              });
              $('#txtenterpoa').val('').focus();
            } else {
              Swal.fire({
                icon: 'warning',
                title: 'No new POA upload',
                text: 'This POA number is completed transaction!'
              });
              $('#poa-number').val('').focus();
            }

          } else if (json.status_code === "200"){
            if (search === 0) {
              $('#modalPOA').modal('hide')
              $('#btnParktransact').prop('disabled', true);
            }
              $('#txtenterpoa').val(poa).prop('readonly', true);
              transactpoa(poa,'new');

          }
        }
      });
    }

    // 🔁 Validate on ENTER key
    $('#txtenterpoa').on('keypress', function (e) {
      if (e.which === 13) {
        const poa = $(this).val().trim();
        if (poa.length >= 12) {
          Swal.fire({
            icon: 'error',
            title: 'Invalid Format',
            text: 'POA number must be exactly 12 characters!'
          });
          return;
        }
        validatePoa(poa, 1);
      }
    });

    // 🔁 Validate on mouse leave
    $('#txtenterpoa').on('mouseleave', function () {
      const poa = $(this).val().trim();
      if (poa.length >= 12) {
        validatePoa(poa, 1);
      }
    });

    // 🔁 Autocomplete setup
    $('#txtenterpoa').autocomplete({
      minLength: 2,
      source: function (request, response) {
        $.ajax({
          type: 'POST',
          url: './includes/query.php',
          data: {
            action: 'searchpoa',
            term: request.term,
            search: 'POA',
            poastore: 'NOTHING',
            poaSku: 'NOTHING',
            poamtcode: 'NOTHING'
          },
          success: function (res) {
            const json = JSON.parse(res);
            if (json.status_code === "200") {
              response(json.data); // Populate dropdown
            } else {
              response([]); // No match found
            }
          }
        });
      },
      select: function (event, ui) {
        const poa = ui.item.label;
        $('#txtenterpoa').val(poa).prop('readonly', true);
        $('#txtscanupc').focus();

        // Disable the switch and the view parked button
        $('#btnViewParked').attr('disabled', true)
        $('#btnFloatItems').attr('disabled', true)
        if (poa.length >= 12) {
          Swal.fire({
            icon: 'error',
            title: 'Invalid Format',
            text: 'Invalid POA number'
          });
          $('#txtenterpoa').val('');
          return false;
        }
        checkpoaiftransact(poa)

        return false; // Prevent default behavior
      }
    });

    function checkpoaiftransact(poa){
      $.ajax({
        type: 'POST',
        url: './includes/query.php',
        data: {
          action: 'checkpoaexist',
          term: poa
        },
        success: function (res) {
          const json = JSON.parse(res);

          if (json.status_code === "409") {
            Swal.fire({
              icon: 'warning',
              title: 'Duplicate POA',
              text: 'This POA number is already transacted!'
            });
            $('#txtenterpoa').val('');
          } else if (json.status_code === "404") {
            Swal.fire({
              icon: 'error',
              title: 'Not Available',
              text: 'This POA number does not exist.'
            });
            $('#txtenterpoa').val('');
          } else if (json.status_code === "200"){
            $('#txtenterpoa').val(poa).prop('readonly', true);
            transactpoa(poa,'new')
          }
        },
        error: function () {
          Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Something went wrong with the request.'
          });
        }
      });
    }

    function transactpoa(poa, transact) {

      $.ajax({
        type: 'POST',
        url: './includes/query.php',
        data: {
          action: 'transactpoa',
          poa: poa,
          transact: transact
        },
        success: function (res) {
          const json = JSON.parse(res);
          if (json.status_code === "200") {
            populateTransactionTable(json.data, poa, transact);
          } else {
            Swal.fire({
              icon: 'info',
              title: 'No Records',
              text: 'No items found under this POA.'
            });
          }
        }
      });
    }
    function populateTransactionTable(data, poa) {
      $('#btnViewParked').attr('disabled', true);
      $('#txtenterpoa').val(poa).prop('readonly', true);
      $('#txtscanupc').focus();

      const tbody = $('#tblTransaction tbody');
      tbody.empty();

      if (data.length === 0) {
        Swal.fire({
          icon: 'info',
          title: 'Done!',
          text: 'No data found. All POA items may have been scanned.'
        });
        return;
      }

      // ✅ Set shiptoname only once
      if (data[0].shiptoname) {
        $('#txtstore').val(data[0].shiptoname);
      }

      data.forEach((item, index) => {
        const scanned = parseInt(item.scanned_qty || 0, 10);
        const required = parseInt(item.required_qty || 0, 10);
        const stat = item.stat || '';
        const disableRemarks = scanned >= required && stat !== 'Float';

        const progressWidth = required > 0 ? (scanned / required) * 100 : 100;
        const progressPercent = Math.min(progressWidth, 100);

        // ✅ Determine progress bar color
        let barColor = 'bg-warning'; // default yellow
        if (stat === 'Float') barColor = 'bg-info'; // blue
        else if (scanned >= required) barColor = 'bg-success'; // green

        const tr = `
          <tr data-code="${item.material_code}" data-sku="${item.sku}" data-required="${required}" data-scanned="${scanned}">
            <td>${index + 1}</td>
            <td class="materialcode">${item.material_code}</td>
            <td class="SKU">${item.sku}</td>
            <td>
              <div class="progress" style="height: 20px;">
                <div class="progress-bar ${barColor}"
                  role="progressbar"
                  style="width: ${progressPercent}%;"
                  aria-valuenow="${scanned}"
                  aria-valuemin="0"
                  aria-valuemax="${required}">
                  ${scanned}
                </div>
              </div>
            </td>
            <td>
              <select class="form-select remarks-select" ${disableRemarks ? 'disabled' : ''}>
                <option value="">Select</option>
                <option value="Sold" ${stat === 'Sold' ? 'selected' : ''}>Sold</option>
                <option value="Lacking" ${stat === 'Lacking' ? 'selected' : ''}>Lacking</option>
                <option value="Wrong Barcode" ${stat === 'Wrong Barcode' ? 'selected' : ''}>Wrong Barcode</option>
                <option value="Others" ${stat === 'Others' ? 'selected' : ''}>Others</option>
                <option value="Float" ${stat === 'Float' ? 'selected' : ''}>Float</option>
              </select>
              <input type="text"
                class="form-control remarks-other-input mt-2"
                placeholder="Please specify..."
                style="display:${stat === 'Others' ? 'block' : 'none'};"
                value="${stat === 'Others' ? (item.trmk || '') : ''}"
                ${disableRemarks ? 'disabled' : ''} />
            </td>
          </tr>
        `;
        tbody.append(tr);
      });

      $('#tblTransaction').on('change', '.remarks-select', function () {
        const $select = $(this);
        if ($select.val() === 'Others') {
          $select.siblings('.remarks-other-input').show();
        } else {
          $select.siblings('.remarks-other-input').hide();
        }
      });
    }



    $('#txtscanupc').on('keypress', function (e) {
      if (e.which === 13) {
        const upc = $(this).val().trim();
        const poa = $('#txtenterpoa').val().trim();
        const storename = $('#txtstore').val().trim().toLowerCase();

        if (!upc) return;

        const isAceStore = storename.includes('ace');

        let trimmedUpc = upc;
        if (isAceStore && upc.length === 13) {
          trimmedUpc = upc.slice(0, -1);
        }

        $.ajax({
          type: 'POST',
          url: './includes/query.php',
          data: {
            action: 'scanupc',
            upc: trimmedUpc,
            poa: poa
          },
          success: function (res) {
            const json = JSON.parse(res);

            if (json.status_code === '200' && json.data.length > 0) {
              const icode = json.data[0].icode;

              let found = false;
              $('#tblTransaction tbody tr').each(function () {
                const row = $(this);
                const code = row.data('code');
                const required = parseInt(row.data('required'), 10);
                let scanned = parseInt(row.data('scanned'), 10);

                if (code === icode) {
                  found = true;

                  row.prependTo('#tblTransaction tbody');

                  if (scanned >= required) {
                    const audio = new Audio('dist/sound/alert.m4a');
                    audio.play();

                    Swal.fire({
                      icon: 'warning',
                      title: 'Over Scanned!',
                      text: `Material Code "${icode}" has already reached the required quantity.`,
                      timer: 3000,
                      showCancelButton: true,
                      confirmButtonText: 'Add as Float',
                      cancelButtonText: 'Cancel'
                    }).then((result) => {
                      if (result.isConfirmed) {
                        const model = json.data[0].icode || '';
                        const sku = json.data[0].sku || '';
                        let floatFound = false;

                        $('#tblTransaction tbody tr').each(function () {
                          const row = $(this);
                          const rowModel = row.find('td:eq(1)').text().trim();
                          const rowSku = row.find('td:eq(2)').text().trim();
                          const remarks = row.find('td:eq(4)').text().trim().toLowerCase();

                          if (rowModel === model && rowSku === sku && remarks === 'float') {
                            floatFound = true;

                            let scanned = parseInt(row.find('.progress-bar').data('scanned'), 10) || 0;
                            scanned++;

                            // ✅ Update both progress bar and row data attributes
                            row.data('scanned', scanned);
                            row.find('.progress-bar')
                              .data('scanned', scanned)
                              .css('width', '100%')
                              .text(scanned)
                              .removeClass('bg-success bg-warning')
                              .addClass('bg-info');

                            row.prependTo('#tblTransaction tbody');
                          }
                        });

                        if (!floatFound) {
                          // ✅ Improved float row creation with proper data attributes
                          const newRow = `
                            <tr data-code="${model}" data-sku="${sku}" data-scanned="1" data-required="0">
                              <td></td>
                              <td class="mtcode">${model}</td>
                              <td class="SKU">${sku}</td>
                              <td>
                                <div class="progress" style="height: 20px;">
                                  <div class="progress-bar bg-info" role="progressbar"
                                    style="width: 100%;" data-scanned="1">
                                    1
                                  </div>
                                </div>
                              </td>
                              <td>
                                <select class="form-control remarks-select" disabled>
                                  <option value="Float" selected>Float</option>
                                </select>
                                <input type="text" class="form-control remarks-other-input mt-2" placeholder="Please specify..." style="display:none;" value="">
                              </td>
                            </tr>
                          `;
                          $('#tblTransaction tbody').prepend(newRow);
                        }

                        $('#tblTransaction tbody tr').each(function (index) {
                          $(this).find('td:first').text(index + 1);
                        });

                        // ✅ Clear after adding float
                        $('#txtscanupc').val('');
                      }
                    });
                  } else {
                    // Not over-scanned
                    scanned++;
                    row.data('scanned', scanned);

                    const percent = (scanned / required) * 100;
                    const progressBar = row.find('.progress-bar');

                    progressBar
                      .data('scanned', scanned)
                      .css('width', `${percent}%`)
                      .text(scanned)
                      .removeClass('bg-success bg-warning')
                      .addClass(scanned === required ? 'bg-success' : 'bg-warning');

                    // ✅ Clear after successful scan
                    $('#txtscanupc').val('');
                  }

                  return false;
                }
              });

              if (!found) {
                Swal.fire({
                  icon: 'error',
                  title: 'Not Found',
                  text: `Scanned UPC does not match any item in the current POA list.`
                });
              }
            } else {
              Swal.fire({
                icon: 'error',
                title: 'Not Found',
                text: 'UPC not found in POA.'
              });
            }
          },
          error: function () {
            Swal.fire({
              icon: 'error',
              title: 'Server Error',
              text: 'Failed to process the scan. Please try again.'
            });
          }
        });
      }
    });



    $('#btnSavetransact').on('click', function () {
      const poa = $('#txtenterpoa').val().trim();
      if (poa === null) {
        Swal.fire({
          icon: 'warning',
          title: `POA Required`,
          text: `Please enter a poa before saving.`
        });
        return;
      }
      Swal.fire({
        title: `POA`,
        text: `Do you want to save this '${poa}' transaction?`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Yes, Save it',
        cancelButtonText: 'Cancel'
      }).then((result) => {
        if (result.isConfirmed) {
            saveTransactionToDatabase(poa, 'complete');
        }
      });
    });
    function saveTransactionToDatabase(poa, transact) {
      const shiptoname = $('#txtstore').val().trim();
      const rows = $('#tblTransaction tbody tr');

      if (rows.length === 0) {
        Swal.fire({ icon: 'info', title: 'No Items', text: 'There are no items to save.' });
        return;
      }

      // 🔍 Check for missing remarks on "less" quantity items
      let missingRemarks = false;
      rows.each(function () {
        const row = $(this);
        const scannedQty = parseInt(row.data('scanned'), 10) || 0;
        const requiredQty = parseInt(row.data('required'), 10) || 0;
        const remarkValue = row.find('.remarks-select').val();

        if (scannedQty < requiredQty && (!remarkValue || remarkValue === '')) {
          row.addClass('table-danger'); // Highlight error row
          missingRemarks = true;
          return false; // Stop loop
        } else {
          row.removeClass('table-danger'); // Clear highlight if fixed
        }
      });

      if (missingRemarks && transact === 'complete') {
        Swal.fire({
          icon: 'warning',
          title: 'Missing Remarks',
          text: 'Remarks are required for items with less quantity.'
        });
        return;
      }

      let completed = 0;
      const totalRows = rows.length;

      rows.each(function () {
        const row = $(this);
        const materialCode = row.find('.mtcode').text().trim() || row.data('code') || '';
        const sku = row.find('.SKU').text().trim() || row.data('sku') || '';
        const scannedQty = parseInt(row.data('scanned'), 10) || 0;
        const requiredQty = parseInt(row.data('required'), 10) || 0;
        const remarkValue = row.find('.remarks-select').val();
        const otherInput = row.find('.remarks-other-input').val()?.trim() || '';
        const trmk = (remarkValue === 'Others') ? otherInput : '';

        let stat = 'over';
        if (scannedQty < requiredQty) stat = 'less';
        else if (scannedQty === requiredQty) stat = 'complete';
        if (remarkValue === 'Float') stat = 'float';
        $.ajax({
          url: './includes/query.php',
          method: 'POST',
          data: {
            action: 'saveTransaction',
            poa: poa,
            sku: sku,
            materialcode: materialCode,
            scanqty: scannedQty,
            totalqty: requiredQty,
            stat: stat,
            trmk: trmk,
            nm: localStorage.userFname,
            shiptoname: shiptoname,
            transact: transact
          },
          success: function (response) {
            completed++;
            if (completed === totalRows) {
              $('#btnFloatItems').prop('disabled', false);
              $('#tblTransaction tbody').empty();
              $('#txtenterpoa').val('').prop('readonly', false).focus();
              $('#btnViewParked').prop('disabled', false);
              $('#txtstore').val('');
              const data = JSON.parse(response);
              showSwalToast(data.status_msg || 'Transaction successfully saved!', 'success');
            }
          },
          error: function () {
            alert('Error saving transaction for material: ' + materialCode);
          }
        });
      });
    }


    $('#btnCanceltransact').on('click', function () {
      const poa = $('#txtenterpoa').val();

      Swal.fire({
        title: 'Cancel Transaction?',
        text: `Do you want to cancel the transaction of this ${poa})?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, cancel it!',
        cancelButtonText: 'No, go back'
      }).then((result) => {
        if (result.isConfirmed) {
          // Clear table
          $('#tblTransaction tbody').empty();

          // Enable buttons back
          $('#btnViewParked').prop('disabled', false);
          $('#btnFloatItems').prop('disabled', false);

          // Clear inputs
          $('#txtenterpoa').val('').prop('readonly', false);
          $('#txtstore').val("")
          $('#txtenterpoa').focus();
        }
      });
    });

    $('#btnViewTransact').on('click', function () {

      searchfloatpoa('add','NOTHING', 'NOTHING');
      // Show the modal
      $('#modalPOA').modal('show')
    });
    $('#btnParktransact').on('click', function () {
      const poa = $('#txtenterpoa').val().trim();
      const rows = $('#tblTransaction tbody tr');
      if (!poa) {
        Swal.fire({
          icon: 'warning',
          title: 'POA Required',
          text: 'Please enter or select a POA number to park.'
        });
        return;
      }

      let hasIncomplete = false;
      let incompleteList = '';

      rows.each(function (index) {
        const row = $(this);
        const materialCode = row.find('td').eq(1).text().trim();
        const scannedQty = parseInt(row.data('scanned')) || 0;
        const requiredQty = parseInt(row.data('required')) || 0;

        if (scannedQty < requiredQty) {
          hasIncomplete = true;
          incompleteList += `• ${materialCode}: ${scannedQty} scanned\n`;
        }
      });

      let promptText = `Do you want to park the transaction for POA (${poa})?`;

      if (hasIncomplete) {
        promptText += `\n\n⚠️ The following items are incomplete:\n${incompleteList}`;
      }

      Swal.fire({
        title: 'Park Transaction?',
        text: promptText,
        icon: hasIncomplete ? 'warning' : 'question',
        showCancelButton: true,
        confirmButtonText: 'Yes, Park it',
        cancelButtonText: 'Cancel'
      }).then((result) => {
        if (result.isConfirmed) {
          // Optional: save to database as parked here
          // parkTransactionToDatabase(poa);
          saveTransactionToDatabase(poa, 'park');
          $('#tblTransaction tbody').empty();
          $('#txtenterpoa').val('').prop('readonly', false).focus();
          $('#btnViewParked').attr('disabled', false);
          $('#txtstore').val("")
          // Swal.fire({
          //   icon: 'success',
          //   title: 'Transaction Parked',
          //   text: 'This POA has been parked successfully.'
          // });
        }
      });
    });
    //PARK
    $('#btnViewParked').on('click', function () {
      $('#modalParkedList').modal('show');
      loadParkedPOAs();
    });
    function loadParkedPOAs() {
      $.ajax({
        type: "POST",
        url: "./includes/query.php",
        data: {
          action: "loadParkedPOAs"
        }
      }).done(function (response) {
        let data = JSON.parse(response);

        if (data.status_code === '404') {
          $('#tblParkedList tbody').html('<tr><td colspan="4" class="text-center text-danger">No records found.</td></tr>');
        } else if (data.status_code === '200') {
          let rows = '';
          data.data.forEach(item => {
            const remarksText = item.remarks === 'park' ? 'Received' : item.remarks;

            rows += `
              <tr>
                <td>${item.poa}</td>
                <td>${item.shiptoname}</td>
                <td>${item.dtcparked}</td>
                <td>${remarksText}</td>
                <td><button class="btn btn-info start-btn" data-poa="${item.poa}"data-str="${item.shiptoname}">Start Scan</button></td>
              </tr>
            `;
          });

          $('#tblParkedList tbody').html(rows);
        }
      }).fail(function () {
        showSwalToast('Something went wrong. Please try again.', 'error');
      });
    }
    $('#tblParkedList').on('click', '.start-btn', function () {
      const poa = $(this).data('poa');
      const str = $(this).data('str');
      transactpoa(poa,'parked')
    });

    //FLOAT
    $('#btnFloatItems').on('click', function () {
      $('#modalAfloatList').modal('show');
      loadfloatItems();
    });
    function loadfloatItems() {
      $.ajax({
        type: "POST",
        url: "./includes/query.php",
        data: {
          action: "loadfloatItems"
        }
      }).done(function (response) {
        let data = JSON.parse(response);

        if (data.status_code === '404') {
          $('#tblAfloatList tbody').html('<tr><td colspan="4" class="text-center text-danger">No records found.</td></tr>');
        } else if (data.status_code === '200') {
          let rows = '';
          data.data.forEach(item => {
            rows += `
              <tr>
                <td>${item.shiptoname}</td>
                <td>${item.materialcode}</td>
                <td>${item.sku}</td>
                <td>${item.scanqty}</td>
                <td>${item.dtc}</td>
               <td><button class="btn btn-info find-btn" data-id="${item.id}"data-storename="${item.shiptoname}"data-materialcode="${item.materialcode}"data-sku="${item.sku}"">Find POA</button></td>
              </tr>
            `;
          });
          $('#tblAfloatList tbody').html(rows);
        }
      }).fail(function () {
        showSwalToast('Something went wrong. Please try again.', 'error');
      });
    }

    $('#tblAfloatList').on('click', '.find-btn', function () {
      const poaId = $(this).data('id');
      const poastore = $(this).data('storename');
      const poamtcode = $(this).data('materialcode');
      const poaSku = $(this).data('sku');

      $('#poa-id').val(poaId);
      $('#poa-storename').val(poastore);
      $('#poa-mtcode').val(poamtcode);
      $('#poa-sku').val(poaSku);

      // ✅ FIXED parameter order
      searchfloatpoa('float', poastore, poaSku, poamtcode);

      $('#modalPOA').modal('show');
      $('#poa-number').focus();
    });
    function searchfloatpoa(search, poastore, poaSku, poamtcode) {
      $('#poa-number').autocomplete({
        minLength: 2,
        appendTo: "#modalPOA",
        source: function(request, response) {
          $.ajax({
            type: 'POST',
            url: './includes/query.php',
            data: {
              action: 'searchpoa',
              term: request.term,
              search: search,
              poastore: poastore,
              poaSku: poaSku,
              poamtcode: poamtcode
            },
            success: function(res) {
              const json = JSON.parse(res);
              console.log(res)
              if (json.status_code === "200") {
                response(json.data);
              } else if (json.status_code === "404") {
                Swal.fire({
                  icon: 'error',
                  title: 'Error',
                  text: 'Please input correct POA number'
                });
                $('#poa-number').val('');
                response([]);
              } else {
                response([]);
              }
            },
            error: function() {
              response([]);
            }
          });
        },
        select: function(event, ui) {
          $('#poa-number').val(ui.item.label);
          return false;
        }
      }).autocomplete("instance")._renderItem = function(ul, item) {
        return $("<li>").append("<div>" + item.label + "</div>").appendTo(ul);
      };
    }

$('#update-poa-btn').click(function () {
  const id = $('#poa-id').val();
  const store = $('#poa-storename').val();
  const sku = $('#poa-sku').val();
  const poa = $('#poa-number').val();

  if (!poa) {
    showSwalToast('Please input POA Number.', 'warning');
    return;
  }
  if (id === '') {
    Swal.fire({
      title: `Add line item on POA: ${poa}?`,
      text: `Do you want to Add Material on ${poa}?`,
      icon: 'warning',
      showCancelButton: true,
      confirmButtonText: 'Yes, Update!',
      cancelButtonText: 'Cancel'
    }).then((result) => {
      if (result.isConfirmed) {
        validatePoa(poa, 0)
      }
    });
  } else {
  Swal.fire({
    title: `Update ${sku}?`,
    text: `Do you want to update ${store} now to the POA: ${poa}?`,
    icon: 'warning',
    showCancelButton: true,
    confirmButtonText: 'Yes, Update!',
    cancelButtonText: 'Cancel'
  }).then((result) => {
    if (result.isConfirmed) {
      updatefloat(id, store, sku, poa);
    }
  });
}

});

function updatefloat(id, store, sku, poa) {
  $.ajax({
    type: "POST",
    url: "./includes/query.php",
    data: {
      action: "updatefloat",
      id: id,
      store: store,
      sku: sku,
      poa: poa
    },
    success: function(response) {
      try {
        let data = JSON.parse(response);

        if (data.status === "success") {
          Swal.fire({
            icon: 'success',
            title: 'Success',
            text: data.message
          });
          $('#modalPOAForm')[0].reset();
          $('#modalPOA').modal('hide');
          loadfloatItems();
        } else {
          Swal.fire({
            icon: 'error',
            title: 'Error',
            text: data.message
          });
        }
      } catch (e) {
        Swal.fire({
          icon: 'error',
          title: 'Error',
          text: 'An unexpected error occurred'
        });
      }
    },
    error: function(xhr, status, error) {
      Swal.fire({
        icon: 'error',
        title: 'Error',
        text: 'Failed to connect to the server'
      });
    }
  });
}
if (localStorage.userLevel !== 'Admin') {
  document.getElementById('thAction').style.display = 'none';
}
    $('#tbl_transactionlist').on('click', '.view-btn', function () {
      const poa = $(this).data('poa');
      const rmk = $(this).data('rmk');
      $('#modalTransactList').modal('show');
      transactpoaview(poa, rmk);
    });
    function transactpoaview(poa, rmk) {
      $.ajax({
        type: "POST",
        url: "./includes/query.php",
        data: {
          action: "transactpoaview",
          poa: poa,
          rmk: rmk
        }
      }).done(function (response) {
        let data = JSON.parse(response);
        if (data.status_code === '404') {
          $('#tblTransactList tbody').html('<tr><td colspan="5" class="text-center text-danger">No records found.</td></tr>');
        } else if (data.status_code === '200') {
          let rows = '';

          data.data.forEach(item => {
            const scanned = parseInt(item.scanqty);
            const total = parseInt(item.totalqty);
            const stat = item.stat?.toLowerCase() || '';
            const remarks = item.remarks?.toLowerCase() || '';

            let qtyDisplay = '';
            let status = '';

            if (remarks === 'float') {
              qtyDisplay = `${scanned}`;
              status = 'Float Items';
            } else if (stat) {
              qtyDisplay = `${scanned} / ${total}`;
              status = item.stat;
            } else {
              const isComplete = scanned >= total;
              qtyDisplay = isComplete ? `${scanned} / ${total}` : `${scanned} / -`;
              status = isComplete ? 'Complete' : 'Pending';
            }

            let updateBtnHtml = '';
            if (localStorage.userLevel === 'Admin') {
              updateBtnHtml = `
                <button
                  class="btn btn-sm btn-primary btn-update-transaction"
                  data-id="${item.id}"
                  data-poa="${item.poa}"
                  data-sku="${item.sku}"
                  data-materialcode="${item.materialcode}"
                  data-remarks="${item.remarks}"
                >
                  Update
                </button>
              `;
            }

            rows += `
              <tr>
                <td>${item.materialcode}</td>
                <td>${item.sku}</td>
                <td>${qtyDisplay}</td>
                <td>${status}</td>
                ${localStorage.userLevel === 'Admin' ? `<td>${updateBtnHtml}</td>` : ''}
              </tr>
            `;
          });

          $('#tblTransactList tbody').html(rows);
        }
      }).fail(function () {
        showSwalToast('Something went wrong. Please try again.', 'error');
      });
    }

    $('#tblTransactList').on('click', '.btn-update-transaction', function () {
      const id = $(this).data('id');
      const poa = $(this).data('poa');
      const sku = $(this).data('sku');
      const materialcode = $(this).data('materialcode');
      const remarks = $(this).data('remarks');

      $('#updateId').val(id);
      $('#poanumber').val(poa);
      $('#remarksid').val(remarks);
      $('#updateMaterialCode').val(materialcode);
      $('#updateSku').val(sku);

      $('#modalTransactListupdate').modal('show');
    });

    $('#btn-updatemtcodeqty').click(function () {
      const id = $('#updateId').val();
      const mtcode = $('#updateMaterialCode').val();
      const qty = $('#updateQty').val();
      const poa = $('#poanumber').val();
      const rmk = $('#remarksid').val();

      if (!qty || qty < 0) {
        showSwalToast("Please enter a valid quantity", "warning");
        return;
      }

      Swal.fire({
        title: `Update quantity?`,
        text: `Do you want to update Material Code (${mtcode}) to quantity (${qty}) now?`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Yes, update it!',
        cancelButtonText: 'Cancel'
      }).then((result) => {
        if (result.isConfirmed) {
          $.ajax({
            type: "POST",
            url: "./includes/query.php",
            data: {
              action: "updateTransactions",
              id: id,
              mtcode: mtcode,
              qty: qty,
              usr: localStorage.userUsername
            },
            success: function (res) {
              $('#modalTransactListupdate').modal('hide');
              showSwalToast("Updated successfully", "success");
              transactpoaview(poa, rmk);
               // Clear fields
                $('#updateId').val('');
                $('#updateMaterialCode').val('');
                $('#updateQty').val('');
                $('#poanumber').val('');
                $('#remarksid').val('');
            },
            error: function () {
              showSwalToast("Failed to update", "error");
            }
          });
        }
      });
    });

    //User
    $('#saveUserBtn').click(function () {
      const name = $('#txtname').val();
      const username = $('#txtusername').val();
      const password = $('#txtpassword').val();
      const userlevel = $('#txtuserlevel').val();

      if (!name || !username || !password || !userlevel) {
        showSwalToast('Please complete all fields.', 'warning');
        return;
      }

      const isUpdate = editMode && editUserId != null;

      Swal.fire({
        title: isUpdate ? `Update User?` : `Add New User?`,
        text: isUpdate
          ? `Do you want to update this user (${name}) now?`
          : `Do you want to add this user (${name}) now?`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: isUpdate ? 'Yes, update user!' : 'Yes, add user!',
        cancelButtonText: 'Cancel'
      }).then((result) => {
        if (result.isConfirmed) {
          if (isUpdate) {
            updateuser(editUserId, name, username, password, userlevel);
          } else {
            adduser(name, username, password, userlevel);
          }

          // Reset modal and state
          $('#addUserForm')[0].reset();
          editMode = false;
          editUserId = null;
          $('#addUserModal').modal('hide');
        }
      });
    });

    $('#tbl_userlist').on('click', '.delete-btn', function () {
      const userId = $(this).data('id');
      const userName = $(this).data('name');

      Swal.fire({
        title: `Delete ${userName}?`,
        text: `Do you want to delete ${userName} now?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, delete user!',
        cancelButtonText: 'Cancel'
      }).then((result) => {
        if (result.isConfirmed) {
          $.ajax({
            type: 'POST',
            url: './includes/query.php',
            data: { action: 'deleteuser', id: userId },
            success: function (response) {
              loadUserTable(currentPage);
              Swal.fire('Deleted!', `${userName} has been removed.`, 'success');
            }
          });
        }
      });
    });
    $('#tbl_userlist').on('click', '.edit-btn', function () {
      const userId = $(this).data('id');
      const name = $(this).data('name');
      const username = $(this).data('uname');
      const userlevel = $(this).data('ulvl');

      // Change modal title to "Update User - [Name]"
      $('#addUserModalLabel').text(`Update User - ${name}`);

      // Populate fields
      $('#txtname').val(name);
      $('#txtusername').val(username);
      $('#txtuserlevel').val(userlevel);
      $('#txtpassword').val(''); // Optional: clear password for security
      // Show modal
      $('#addUserModal').modal('show');
      editUserId = userId;
      editMode = true;
    });


    //Function User
    function adduser(name, username, password, userlevel) {
      $.ajax({
        type: "POST",
        url: "./includes/query.php",
        data: {
          action: "adduser",
          name: name,
          username: username,
          password: password,
          userlevel: userlevel
        }
      }).done(function (response) {
        let data = JSON.parse(response);

        if (data.status_code === '404') {
          showSwalToast(data.status_msg || 'Something went wrong!', 'error');
        } else if (data.status_code === '200') {
          showSwalToast(data.status_msg || 'User added successfully!', 'success');
              // Hide the modal
              $('#addUserModal').modal('hide');

              // Clear the inputs
              $('#txtname').val('');
              $('#txtusername').val('');
              $('#txtpassword').val('');
              $('#txtuserlevel').val('Admin');
              loadUserTable(currentPage);
        }
      }).fail(function () {
        showSwalToast('Something went wrong. Please try again.', 'error');
      });
    }
    function updateuser(userid, name, username, password, userlevel) {
      $.ajax({
        type: "POST",
        url: "./includes/query.php",
        data: {
          action: "updateuser",
          userid: userid,
          name: name,
          username: username,
          password: password,
          userlevel: userlevel
        }
      }).done(function (response) {
        let data = JSON.parse(response);

        if (data.status_code === '404') {
          showSwalToast(data.status_msg || 'Something went wrong!', 'error');
        } else if (data.status_code === '200') {
          showSwalToast(data.status_msg || 'User upated successfully!', 'success');
              // Hide the modal
              $('#addUserModal').modal('hide');

              // Clear the inputs
              $('#txtname').val('');
              $('#txtusername').val('');
              $('#txtpassword').val('');
              $('#txtuserlevel').val('Admin');
              editUserId = null;
              editMode = false;
              loadUserTable('gettbluser',currentPage);
        }
      }).fail(function () {
        showSwalToast('Something went wrong. Please try again.', 'error');
      });
    }
    //Function Datatable
  // Function Datatable
if (pages === 'Userlist') {
  loadUserTable('gettbluser', currentPage);
} else if (pages === 'Uploading') {
  loadUserTable('gettbldbf', currentPage);
}else if (pages === 'Transactionlist') {
  loadUserTable('getTransactPOAs',currentPage);
}

// Search Handler
$('#searchInput').on('keyup', function () {
  let search = $(this).val();
  if (pages === 'Userlist') {
    loadUserTable('gettbluser', 1, search);
  } else if (pages === 'Uploading') {
    loadUserTable('gettbldbf', 1, search);
  }else if (pages === 'Transactionlist') {
    loadUserTable('getTransactPOAs', 1, search);
  }
});

// Main Table Loader
function loadUserTable(action, page, search = '') {
  $.ajax({
    type: 'POST',
    url: './includes/query.php',
    data: {
      action: action,
      page: page,
      search: search,
      itemsPerPage: itemsPerPage
    },
    success: function (response) {
      let res = JSON.parse(response);
      let users = res.data;
      let pagination = res.pagination;
      currentPage = pagination.currentPage;


      let tbody;

      // Choose template depending on page
      if (action === 'gettbluser') {
        tbody = $('#tbl_userlist tbody');
      } else if (action === 'gettbldbf') {
        tbody = $('#tbl_uploadeddbf tbody');
      }else if (pages === 'Transactionlist') {
        tbody = $('#tbl_transactionlist tbody');
      }

      // Clear existing rows
      tbody.empty();

      // Render new rows
      if (action === 'gettbluser') {
        users.forEach((user) => {
          let row = `
            <tr>
              <td>${user.fname}</td>
              <td>${user.username}</td>
              <td>${user.userlevel}</td>
              <td>
                <button class="btn btn-sm btn-primary edit-btn"
                  data-id="${user.id}"
                  data-name="${user.fname}"
                  data-uname="${user.username}"
                  data-ulvl="${user.userlevel}">Edit</button>
                <button class="btn btn-sm btn-danger delete-btn"
                  data-id="${user.id}"
                  data-name="${user.fname}">Delete</button>
              </td>
            </tr>`;
          tbody.append(row);
        });
      } else if (action === 'gettbldbf') {
        users.forEach((item) => {
          let row = `
            <tr>
              <td>${item.vendor}</td>
              <td>${item.upc}</td>
              <td>${item.sku}</td>
              <td>${item.brand}</td>
              <td>${item.model}</td>
              <td>${item.desc}</td>
              <td>${item.srp}</td>
              <td>${item.promo}</td>
              <td>${item.remarks}</td>
            </tr>`;
          tbody.append(row);
        });
      } else if (action === 'getTransactPOAs') {
        users.forEach((item) => {
          // Determine remarks and color
          let remarksText = '';
          let remarksColor = '';
          let scannedDisplay = `${item.total_scanned}/${item.total_required}`;

          if (item.remarks.toLowerCase() === 'park') {
            remarksText = 'Parked';
            remarksColor = 'text-danger'; // red
            scannedDisplay = `${item.total_scanned}/-`;
          }else if (item.stat.toLowerCase() === 'float' && item.remarks.toLowerCase() === 'float') {
            remarksText = 'Float Items';
            remarksColor = 'text-info'; // red
            scannedDisplay = `${item.total_scanned}`;
          }else if (item.stat.toLowerCase() === 'complete' && item.remarks.toLowerCase() === 'float') {
            if (parseInt(item.total_scanned) > parseInt(item.total_required)) {
              remarksText = 'Complete - OVER';
              remarksColor = 'text-success'; // green
            } else if (parseInt(item.total_scanned) < parseInt(item.total_required)){
              remarksText = 'Complete - LESS';
              remarksColor = 'text-success'; // green
            } else {
              remarksText = 'Complete';
              remarksColor = 'text-success'; // green
            }
          } else {
            if (parseInt(item.total_scanned) > parseInt(item.total_required)) {
              remarksText = 'Complete - OVER';
              remarksColor = 'text-success'; // green
            } else {
              remarksText = 'Complete';
              remarksColor = 'text-success'; // green
            }
          }

          let row = `
            <tr>
              <td>${item.poa ? item.poa : ''}</td>
              <td>${item.shiptoname}</td>
              <td>${scannedDisplay}</td>
              <td class="${remarksColor}">${remarksText}</td>
              <td>${item.userscan}</td>
              <td>${item.dtc}</td>
              <td> <button class="btn btn-info view-btn" data-poa="${item.poa}" data-rmk="${item.remarks}">View</button></td>
            </tr>`;
          tbody.append(row);
        });
      }

      // Render pagination
      renderPagination(action, pagination.totalPages);
    },
    error: function (xhr, status, error) {
    }
  });
}

// Pagination Renderer
function renderPagination(action, totalPages) {
  let pagination = $('#pagination');
  pagination.empty();

  if (totalPages <= 1) return; // No pagination needed

  let buttons = '';

  // « Previous
  if (currentPage > 1) {
    buttons += `<button class="page-btn btn btn-light" data-page="${currentPage - 1}">«</button>`;
  }

  // Always show first page
  if (currentPage > 2) {
    buttons += `<button class="page-btn btn btn-light" data-page="1">1</button>`;
    if (currentPage > 3) {
      buttons += `<button class="btn btn-light disabled">...</button>`;
    }
  }

  // Middle pages
  let startPage = Math.max(2, currentPage - 1);
  let endPage = Math.min(totalPages - 1, currentPage + 1);

  for (let i = startPage; i <= endPage; i++) {
    buttons += `<button class="page-btn btn ${i === currentPage ? 'btn-primary' : 'btn-light'}" data-page="${i}">${i}</button>`;
  }

  // Always show last page
  if (currentPage < totalPages - 1) {
    if (currentPage < totalPages - 2) {
      buttons += `<button class="btn btn-light disabled">...</button>`;
    }
    buttons += `<button class="page-btn btn btn-light" data-page="${totalPages}">${totalPages}</button>`;
  }

  // » Next
  if (currentPage < totalPages) {
    buttons += `<button class="page-btn btn btn-light" data-page="${currentPage + 1}">»</button>`;
  }

  pagination.html(buttons);

  // Event binding
  $('.page-btn').on('click', function () {
    const page = $(this).data('page');
    loadUserTable(action, page);
  });
}


  // Upload Excel (mislabelled as DBF)
$("#btn_uploaddbf").click(function (e) {
  e.preventDefault(); // Prevent default form submission
  const action = "dbf";
  uploadExcelFile(action);
});

// Main upload function
function uploadExcelFile(action) {
  const input = document.getElementById("dbfFile");
  const validExtensions = ['.xls', '.xlsx', '.csv'];

  if (!input.files || input.files.length === 0) {
    showSwalToast('Please select a file.', 'warning');
    return;
  }

  const file = input.files[0];
  const fileName = file.name.toLowerCase();
  const isValidExcel = validExtensions.some(ext => fileName.endsWith(ext));

  if (!isValidExcel) {
    Swal.fire({
      icon: 'error',
      title: 'Invalid File',
      text: 'Please upload a valid Excel file (.xls, .xlsx, .csv).'
    });
    return;
  }

  const formData = new FormData();
  formData.append("excelFile", file);
  formData.append("action", action);

  Swal.fire({
    title: 'Uploading...',
    allowOutsideClick: false,
    didOpen: () => {
      Swal.showLoading();
    }
  });

  $.ajax({
    url: "./excelupload/upload.php",
    type: "POST",
    data: formData,
    processData: false,
    contentType: false,
    success: (response) => {
      Swal.close(); // Close the loading modal
      const responseData = JSON.parse(response);

      if (responseData.status_code === '200') {
        showSwalToast(responseData.status_msg || 'File uploaded successfully!', 'success');
        $('#uploadDbfModal').modal('hide'); // Close modal on success
        // Reload table or take any other action
        loadUploadedDBF();
      } else {
        showSwalToast(responseData.status_msg || 'Upload failed. Please check your file.', 'error');
      }
    },
    error: () => {
      Swal.close();
      showSwalToast('An error occurred while uploading the file.', 'error');
    }
  });
}

// Trigger Report Modal and set default dates
$('#btnReport').on('click', function () {
  const today = new Date();
  const dateTo = today.toISOString().split('T')[0];

  const sevenDaysAgo = new Date();
  sevenDaysAgo.setDate(today.getDate() - 7);
  const dateFrom = sevenDaysAgo.toISOString().split('T')[0];

  // Set the values for date inputs
  $('#dateFrom').val(dateFrom);
  $('#dateTo').val(dateTo);

  // Show the modal for selecting the report date range
  $('#reportModal').modal('show');
});

$('#btngeneratereport').on('click', function () {
  const dateFrom = $('#dateFrom').val();
  const dateTo = $('#dateTo').val();

  if (dateFrom && dateTo) {
    const url = `./includes/report.php?dateFrom=${dateFrom}&dateTo=${dateTo}`;

    // Close modal first
    $('#reportModal').modal('hide');

    // Open CSV download in a new tab
    window.open(url, '_blank');

    // Redirect to the transaction list page with the correct parameter
    setTimeout(function() {
      window.location.href = 'structure.php?page=Transactionlist';
    }, 500); // Short delay to ensure the download starts first
  } else {
    alert('Please select both Date From and Date To.');
  }
});


    //Informative
    function showSwalToast(message, type = 'success') {
      const Toast = Swal.mixin({
        toast: true,
        position: 'bottom',
        showConfirmButton: false,
        showCloseButton: true, // 👈 Add this line to show the "X" close button
        timer: 3000,
        timerProgressBar: true,
        didOpen: (toast) => {
          toast.addEventListener('mouseenter', Swal.stopTimer);
          toast.addEventListener('mouseleave', Swal.resumeTimer);
        }
      });

      Toast.fire({
        icon: type, // 'success', 'error', 'warning', 'info', 'question'
        title: message
      });
    }


  //Log-out
  $('#btnLogout').on('click', function () {
    const userFname = localStorage.getItem('userFname') || 'this account';

    Swal.fire({
      title: 'Are you sure?',
      text: `Do you want to logout ${userFname}?`,
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#d33',
      cancelButtonColor: '#6c757d',
      confirmButtonText: 'Yes, logout'
    }).then((result) => {
      if (result.isConfirmed) {
        localStorage.clear(); // clears all items in localStorage
        location.replace('./login.php'); // redirect to login
      }
    });
  });


});
