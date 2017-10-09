"use strict";

module.exports = class {

    addEventListeners() {
        $('#navReviewsButton').click(this.navReviewsButtonHasBeenClicked);
        $('#navTopicsButton').click(this.navTopicsButtonHasBeenClicked);
        $('#navCriteriaButton').click(this.navCriteriaButtonHasBeenClicked);
        $('#navEmphasizersButton').click(this.navEmphasizersButtonHasBeenClicked);
        $("#topicAliasesGridButton").click(this.switchToTopicAliasesButtonHasBeenClicked);

        $("#testAnalyzerButton").click(this.testAnalyzerButtonHasBeenClicked);
        $("#modalTestAnalyzeButton").click(this.testAnalyzeModalButtonHasBeenClicked);

        $('#uploadReviewsButton').click(this.uploadCSVReviewsButtoHasBeenClicked);
        $('#analyzeAllButton').click(this.analyzeAllReviewsButtonHasBeenClicked);
        $('#formButtonUploadCSV').click(this.formUploadCSVButtonHasBeenClicked);
    }

    navReviewsButtonHasBeenClicked(clickEvent) {
        AnalyzerGUI.Navigation.changeSection(
            $(clickEvent.currentTarget),
            AnalyzerGUI.GridConfig.Reviews
        );
    }

    navTopicsButtonHasBeenClicked(clickEvent) {
        AnalyzerGUI.Navigation.changeSection(
            $(clickEvent.currentTarget),
            AnalyzerGUI.GridConfig.Topics
        );
    }

    navCriteriaButtonHasBeenClicked(clickEvent) {
        AnalyzerGUI.Navigation.changeSection(
            $(clickEvent.currentTarget),
            AnalyzerGUI.GridConfig.Criteria
        );
    }

    navEmphasizersButtonHasBeenClicked(clickEvent) {
        AnalyzerGUI.Navigation.changeSection(
            $(clickEvent.currentTarget),
            AnalyzerGUI.GridConfig.Emphasizers
        );
    }

    switchToTopicAliasesButtonHasBeenClicked(clickEvent) {
        AnalyzerGUI.Navigation.loadGrid(AnalyzerGUI.GridConfig.TopicsAliases);
    }

    testAnalyzerButtonHasBeenClicked(clickEvent) {
        AnalyzerGUI.ReviewsAnalyzer.showTestAnalyzerModal(clickEvent);
    }

    testAnalyzeModalButtonHasBeenClicked(clickEvent) {
        AnalyzerGUI.ReviewsAnalyzer.testAnalyzeAndRenderResults(clickEvent);
    }

    uploadCSVReviewsButtoHasBeenClicked(clickEvent) {
        $('#modalUploadFile').modal();
    }

    analyzeAllReviewsButtonHasBeenClicked(clickEvent) {
        AnalyzerGUI.ReviewsAnalyzer.analyzeALL(clickEvent);
    }

    formUploadCSVButtonHasBeenClicked(clickEvent) {
        AnalyzerGUI.CSVUploader.uploadFile(clickEvent);
    }

};