"use strict";

module.exports = class {

    changeSection(navButtonClicked, gridConfig) {
        $("ul.navbar-nav li.active").removeClass("active");
        navButtonClicked.addClass("active");

        this.navButtonClicked = navButtonClicked;

        this.sectionButtonsUpdate($('#navReviewsButton'), $("#reviewsButtons"));
        this.sectionButtonsUpdate($('#navTopicsButton'), $("#topicsButtons"));

        this.loadGrid(gridConfig);
    }

    sectionButtonsUpdate(navButton, buttonsDiv) {
        if (this.navButtonClicked.attr('id') === navButton.attr("id")) 
            buttonsDiv.css('display', 'block');
        else
            buttonsDiv.css('display', 'none');
    }

    loadGrid(gridConfig) {
        $("#jsGrid").jsGrid('reset');
        $("#jsGrid").jsGrid({
            height: "auto",
            width: "100%",
    
            filtering: true,
            sorting: true,
            autoload: true,
            editing: true,
            paging: true,
            inserting: true,

            deleteConfirm: gridConfig.deleteConfirm,
    
            controller: {
                loadData: (filters) => {
                    return new Promise((resolve) => {
                        $.ajax({
                            url: AnalyzerGUI.baseUrl + "/api/" + gridConfig.api + "?" + $.param(filters)
                        }).done((response) => {
                            gridConfig.afterLoadHandler && gridConfig.afterLoadHandler(response);
                            resolve(response);
                        });
                    });
                },

                insertItem: (item) => {
                    return new Promise((resolve, reject) => {
                        $.ajax({
                            url: AnalyzerGUI.baseUrl + "/api/" + gridConfig.api + "/new/",
                            dataType: "json",
                            method: "POST",
                            data: JSON.stringify(item)
                        })
                        .done(() => {
                            alert('Success');
                            resolve();
                            $("#jsGrid").jsGrid("loadData");
                        })
                        .fail((obj) => {
                            this.errorHandler(obj.responseText);
                            reject();
                        });
                    });
                },

                updateItem: (item) => {
                    if (AnalyzerGUI.GridConfig.postAnalyzeFlag) {
                        AnalyzerGUI.GridConfig.postAnalyzeFlag = false;
                        return null;
                    }
                    return new Promise((resolve, reject) => {
                        $.ajax({
                            url: AnalyzerGUI.baseUrl + "/api/" + gridConfig.api + "/modify/",
                            dataType: "json",
                            method: "POST",
                            data: JSON.stringify(item)
                        })
                        .done((response) => {
                            alert('Success');
                            resolve();
                            gridConfig.afterUpdateHandler && gridConfig.afterUpdateHandler(response, item);
                        })
                        .fail((obj) => {
                            this.errorHandler(obj.responseText);
                            reject();
                        });
                    });
                },
                
                deleteItem: (item) => {
                    return new Promise((resolve, reject) => {
                        $.ajax({
                            url: AnalyzerGUI.baseUrl + "/api/" + gridConfig.api + "/delete/" + item.id,
                            method: "DELETE",
                        })
                        .done(() => {
                            alert('Success');
                            resolve();
                        })
                        .fail((obj) => {
                            this.errorHandler(obj.responseText);
                            reject();
                        });
                    });
                }
            },
    
            fields: gridConfig.fields
        });
    }


    errorHandler(errorObj) {
        let parsedError;

        try {
            parsedError = JSON.parse(errorObj).error;
        } catch (Exception) {
            parsedError = errorObj;
        }

        alert('An error ocurred: ' + parsedError);
    }

}