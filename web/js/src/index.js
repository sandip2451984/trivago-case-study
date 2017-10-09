window.AnalyzerGUI = (() => {

    init = (backendParameters) => {
        loadDependencies();

        AnalyzerGUI.baseUrl = "http://" + backendParameters.httpHost;

        AnalyzerGUI.GridConfig = require("./GridConfig");
        AnalyzerGUI.EventsManager = new (require("./EventsManager"))();
        AnalyzerGUI.Navigation = new (require("./Navigation"))();
        AnalyzerGUI.ReviewsAnalyzer = new (require("./ReviewsAnalyzer"))();
        AnalyzerGUI.CSVUploader = new (require("./CSVUploader"))();

        AnalyzerGUI.EventsManager.addEventListeners();
        AnalyzerGUI.GridConfig.allowDecimalFieldTypes();
        AnalyzerGUI.Navigation.loadGrid(AnalyzerGUI.GridConfig.Reviews);
    },

    loadDependencies = () => {
        window.jQuery = window.$ = require("jquery");
        require("../../node_modules/jsgrid/dist/jsgrid.min.css");
        require("../../node_modules/jsgrid/dist/jsgrid-theme.min.css");
        require("../../node_modules/jquery.json-viewer/json-viewer/jquery.json-viewer.css");
        require("bootstrap");
        require("jsgrid");
        require("../../node_modules/jquery.json-viewer/json-viewer/jquery.json-viewer.js");
    };

    return {init: init};

})();