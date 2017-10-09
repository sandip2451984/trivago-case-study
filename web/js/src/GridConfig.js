"use strict";

module.exports = {

    postAnalyzeFlag: false,

    Reviews: {
        api: 'reviews',

        afterUpdateHandler: (serverResponse, jsgridRow) => {
            $("#jsGrid").jsGrid("loadData");
        },

        deleteConfirm: (item) => {
            return "Are you sure that you want to remove the review with the ID " + item.id + "?";
        },

        fields: [{
            name: "id",
            type: "number",
            title: "ID",
            width: 10,
            inserting: false,
            editing: false 
        },{
            name: "text",
            type: "textarea",
            title: "Review",
            width: 120 
        },{
            name: "total_score",
            title: "Score",
            type: "text",
            width: 20,
            inserting: false,
            editing: false,
            align: "center",
            cellRenderer: (value, item) => {
                const td = $('<td width="20px">' + (value !== undefined ? value : '') + '</td>');

                if (value !== undefined)
                    td.addClass(value > 0 ? 'positiveReview' : 'negativeReview');

                return td;
            }
        },{
            name: "detailed_results",
            title: "Detailed results",
            width: 50,
            type: "text",
            inserting: false,
            editing: false,
            filtering: false,
            align: "center",
            cellRenderer: (value, item) => {
                const td = $('<td width="50px"></td>');
                const button = $('<button class="btn btn-default">Click to show!</button>');

                if (item.analysis && item.analysis.length === 0)
                    button.attr('disabled', true);

                button.appendTo(td);

                button.click((clickEvent) => {
                    clickEvent.preventDefault();
                    clickEvent.stopPropagation();
                    AnalyzerGUI.ReviewsAnalyzer.modalAnalysisResults(item.analysis);
                });

                return td;
            }
        },{
            title: "Analyze",
            type: "text",
            width: 30,
            inserting: false,
            editing: false,
            filtering: false,
            align: "center",
            cellRenderer: (value, item) => {
                const td = $('<td width="30px" style="text-align: center"></td>');
                const button = $('<button class="btn btn-default">Analyze!</button>');

                if (item.analysis && item.analysis.length > 0)
                    button.attr('disabled', true);

                button.appendTo(td);

                button.click((clickEvent) => {
                    clickEvent.preventDefault();
                    clickEvent.stopPropagation();

                    AnalyzerGUI.ReviewsAnalyzer.analyzeSingleReview(item, button);
                });

                return td;
            }
        },{
            type: "control",
            width: 20
        }]
    },



    Topics: {
        api: 'topics',

        afterLoadHandler: (serverResponse) => {
            serverResponse.forEach((element) => {
                let aliases = [];
                element.aliases.forEach((element) => {
                    aliases.push(element.alias);
                });
                element.alias = aliases.join(', ');
            });
        },

        deleteConfirm: (item) => {
            return "Are you sure that you want to remove this topic? ("+item.name+")";
        },

        fields: [{
            name: "id",
            type: "number",
            title: "ID",
            width: 20,
            editing: false,
            inserting: false
        },{
            name: "name",
            type: "text",
            title: "Name",
            width: 30
        },{
            name: "alias",
            type: "text",
            title: "Aliases",
            width: 60,
            inserting: false,
            editing: false
        },{
            name: "priority",
            type: "number",
            title: "Priority",
            width: 10
        },{
            type: "control",
            width: 20
        }]
    },



    TopicsAliases: {
        api: 'topics/aliases',

        afterLoadHandler: (serverResponse) => {
            serverResponse.forEach((element) => {
                element.topic_name = element.topic.name;
            });
        },

        deleteConfirm: (item) => {
            return "Are you sure that you want to remove this alias? ("+item.alias+")";
        },

        fields: [{
            name: "id",
            type: "number",
            title: "ID",
            width: 20,
            editing: false,
            inserting: false
        },{
            name: "topic_name",
            type: "text",
            title: "Topic",
            width: 40,
            editing: false
        },{
            name: "alias",
            type: "text",
            title: "Alias",
            width: 40
        },{
            type: "control",
            width: 20
        }]
    },



    Criteria: {
        api: 'criteria',

        deleteConfirm: (item) => {
            return "Are you sure that you want to remove this criteria? (" + item.keyword + ")";
        },

        fields: [{
            name: "id",
            type: "number",
            title: "ID",
            width: 20,
            editing: false,
            inserting: false
        },{
            name: "keyword",
            type: "text",
            title: "Keyword",
            width: 50
        },{
            name: "score",
            type: "number",
            title: "Score",
            width: 30
        },{
            type: "control",
            width: 20
        }]
    },



    Emphasizers: {
        api: 'emphasizers',

        deleteConfirm: (item) => {
            return "Are you sure that you want to remove this emphasizer? ("+item.name+")";
        },
    
        fields: [{
            name: "id",
            type: "number",
            title: "ID",
            width: 20,
            editing: false,
            inserting: false
        },{
            name: "name",
            type: "text",
            title: "Name",
            width: 50
        },{
            name: "score_modifier",
            type: "decimal",
            title: "Score modifier",
            width: 30
        },{
            type: "control",
            width: 20
        }]
    },


    allowDecimalFieldTypes: () => {
        function DecimalField(config) {
            jsGrid.fields.number.call(this, config);
        }
        DecimalField.prototype = new jsGrid.fields.number({
            filterValue: function() {
                return this.filterControl.val()
                    ? parseFloat(this.filterControl.val() || 0, 10)
                    : undefined;
            },
            insertValue: function() {
                return this.insertControl.val()
                    ? parseFloat(this.insertControl.val() || 0, 10)
                    : undefined;
            },
            editValue: function() {
                return this.editControl.val()
                    ? parseFloat(this.editControl.val() || 0, 10)
                    : undefined;
            }
        });
        jsGrid.fields.decimal = jsGrid.DecimalField = DecimalField;
    }
    
}