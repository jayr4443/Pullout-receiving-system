$(document).ready(function() {
    $("#uploadBtn").on("click", function() {
        uploadExcelFile();
    });
});

function uploadExcelFile() {
    var input = document.getElementById("file");
    console.log(input);
    file = input.files[0];
    console.log(file);
    
    if (file != undefined) {
        formData = new FormData();
        if (file.type === "application/vnd.ms-excel" || file.type === "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet") {
            console.log('Excel file detected');
            formData.append("excelFile", file);
            $.ajax({
                url: "upload.php",
                type: "POST",
                data: formData,
                processData: false,
                contentType: false,
                success: function(data) {
                    $('#message').html(data); // Display server response in the message div
                }
            });
        } else {
            console.log('Invalid file format. Only Excel files are allowed.');
        }
    }
}
