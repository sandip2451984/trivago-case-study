"use strict";

module.exports = class {

    showTestAnalyzerModal() {
        $('#testAnalyzerModal').modal();
    };

    testAnalyzeAndRenderResults() {
        const jsonRenderer = $('#json-renderer');

        jsonRenderer.empty();
        $('<span>Analyzing...</span>').appendTo(jsonRenderer);
        $.ajax({
            url: AnalyzerGUI.baseUrl + "/api/reviews/testAnalyzer/",
            method: "POST",
            data: $('#textareaReviewTestAnalyze').val(),
        }).done((response) => {
            let totalScore = 0;
            
            for (var el in response)
                totalScore += response[el].score;

            jsonRenderer.jsonViewer({
                totalScore: totalScore,
                detailedResults: response
            });
        });
    };

    analyzeALL(clickEvent) {
        const analyzeAllButton = $('#analyzeAllButton');

        analyzeAllButton.attr('disabled', true).html('Analyzing all... please wait');

        $.ajax({
            url: AnalyzerGUI.baseUrl + "/api/reviews/analyze/all/",
            method: "POST"
        }).done((response) => {
            alert('Success!');
            $("#jsGrid").jsGrid("loadData");
        }).always(() => {
            analyzeAllButton.removeAttr('disabled').html('Analyze ALL reviews');
        }).fail(() => {
            alert('An error ocurred');
        });
    };


    modalAnalysisResults(analysis) {
        const tableBody = $('#modalReviewDetailedResults .modal-body .table tbody');

        $('#modalReviewDetailedResults').modal();
        tableBody.empty();
        analysis.forEach((row) => {
            const tableRow = $('<tr></tr>');
            let criteriaString = '';

            $('<td></td>').html(row.topic.name).appendTo(tableRow);
            row.analysis_criteria.forEach((element) => {
                criteriaString += (element.negated ? 'not ' : ' ') + (element.emphasizer ? element.emphasizer.name : '') + ' ' + element.criteria.keyword + ', ';
            });
            $('<td></td>').html(criteriaString.substring(0, criteriaString.length - 2)).appendTo(tableRow);
            $('<td></td>').html(row.score).addClass(row.score > 0 ? 'positiveReview' : 'negativeReview').appendTo(tableRow);
            tableRow.appendTo(tableBody);
        });
    };

    analyzeSingleReview(jsGridRow, analyzeButton) {
        analyzeButton.attr('disabled', true);
        analyzeButton.html('Analyzing ...');
        $.ajax({
            url: AnalyzerGUI.baseUrl + "/api/reviews/analyze/" + jsGridRow.id,
            method: "POST"
        })
        .done((response) => {
            AnalyzerGUI.GridConfig.postAnalyzeFlag = true;
            $("#jsGrid").jsGrid("updateItem", jsGridRow, response);
        })
        .fail((obj) => {
            alert('An error happened.');
        })
        .always(() => {
            analyzeButton.removeAttr('disabled');
            analyzeButton.html('Analyze!');
        });
    }

};