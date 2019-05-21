$(document).ready(function () {
    Dropzone.autoDiscover = false;
    $("#dZUpload").dropzone({
        url: "{{ oneup_uploader_endpoint('gallery') }}",
        addRemoveLinks: true,
        maxFilesize: 5,
        maxFiles: 5,
        acceptedFiles: "image/*",
        dictDefaultMessage: "Drop images here or click to upload...",
        // parallelUploads: 5,
        success: function (file, response) {
            var imgName = response;
            file.previewElement.classList.add("dz-success");
            console.log("Successfully uploaded :" + imgName);
        },
        error: function (file, response) {
            file.previewElement.classList.add("dz-error");
        }
    });
});