"use strict";

module.exports = class {

    uploadFile(clickEvent) {
        const csvUploadProgressBar = $('#csvUploadProgress');

        clickEvent.preventDefault();
        clickEvent.stopPropagation();
        if (! this.validations()) return;

        csvUploadProgressBar.css('visibility', 'visible');
        csvUploadProgressBar.attr({
            value: 0,
            max: 0,
        });

        $.ajax({
            url: AnalyzerGUI.baseUrl + "/api/reviews/upload/",
            type: "POST",
            data: new FormData($('#uploadCSVForm')[0]),
            cache: false,
            contentType: false,
            processData: false,
            
            xhr: () => {
                const myXhr = $.ajaxSettings.xhr();
                if (! myXhr.upload) return myXhr;

                myXhr.upload.addEventListener('progress', (event) => {
                    if (! event.lengthComputable) return null;
                    csvUploadProgressBar.attr({
                        value: event.loaded,
                        max: event.total,
                    });
                } , false);

                return myXhr;
            }
        })
        .done((response) => {
            alert('Success');
            $('#modalUploadFile').modal('hide');
            $("#jsGrid").jsGrid("loadData");
        })
        .always((response) => {
            csvUploadProgressBar.css('visibility', 'hidden');
        })
        .fail((response) => {
            alert('An error ocurred: ' + response.responseText);
        });
    }

    
    validations() {
        // must have .csv extension

        return true;
    }

}