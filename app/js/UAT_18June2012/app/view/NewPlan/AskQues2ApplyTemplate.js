Ext.define('COMS.view.NewPlan.AskQues2ApplyTemplate', {
	extend: 'Ext.window.Window',
	alias : 'widget.AskQues2ApplyTemplate',
        buttonAlign: 'center',
	title : 'Apply Template to Patient',
	layout: 'fit',
	autoShow: true,
	width: 400,

//        constructor: function(myStore){
//            this.initComponent(myStore.myStore);
//            this.show();
//        },

	initComponent: function() {
            debugger;
            if (this.ChangeTemplate) {
                alert("Ask for reason of changing template");
            }

            this.items = [ {
                    xtype: 'form',
                    cls: 'custom-form',
                    defaults : { labelAlign: 'top', margin: '5'},
                    items: [
							{ xtype : "RequiredFieldLabel" },





			{ xtype : "radiogroup", name : "Reason4EOTSAnswer", width: 200, hideLabel : true, columns : 1, vertical : true, hidden: !(this.ChangeTemplate), 
				items : [
					{ boxLabel : "Completed Prescribed Course", name : "EOTS_Reason", inputValue : "Completed Prescribed Course"},
					{ boxLabel : "Treatment Change", name : "EOTS_Reason", inputValue : "Treatment Change"},
					{ xtype : "radiogroup", name : "Reason4EOTS_TCReason", width: 200, hidden : true, hideLabel : true, margin: "0 10 0 20", columns : 1, vertical : true, items : [
						{ boxLabel : "Toxicity", name : "EOTS_TChange", inputValue : "Toxicity"},
						{ boxLabel : "Progression of the Disease", name : "EOTS_TChange", inputValue : "Progression of the Disease"},
						{ boxLabel : "Patient Refusal", name : "EOTS_TChange", inputValue : "Patient Refusal"},
						{ boxLabel : "Other", name : "EOTS_TChange", inputValue : "Other"},
						{ xtype : "textfield", margin: "0 10 0 20", hidden : true, name : "EOTS_TChangeOther", hideLabel : true }
					]},
					{ boxLabel : "Patient Discontinuation", name : "EOTS_Reason", inputValue : "Patient Discontinuation"},
					{ xtype : "radiogroup", name : "Reason4EOTS_PDReason", width: 200, hideLabel : true, hidden : true, margin: "0 10 0 20", columns : 1, vertical : true, items : [
						{ boxLabel : "Patient Terminated Regimen", name : "EOTS_PDChange", inputValue : "Patient Terminated Regimen"},
						{ boxLabel : "Patient Left VA System", name : "EOTS_PDChange", inputValue : "Patient Left VA System"},
						{ boxLabel : "Other", name : "EOTS_PDChange", inputValue : "Other"},
						{ xtype : "textfield", margin: "0 10 0 20", hidden : true, name : "EOTS_PDChangeOther", hideLabel : true }
					]},

					{ boxLabel : "Other ", name : "EOTS_Reason", inputValue : "Other"},
					{ xtype : "textfield", margin: "0 10 0 20", hidden : true, name : "EOTS_ReasonOther", hideLabel : true }
				]
			},




                            {
                                xtype: 'datefield', labelAlign: 'top', name : 'startdate', labelWidth: 100, width: 178, fieldLabel: 'Enter a Start Date <em>*</em>' 
                            },
                            {
                                xtype : "container",
                                layout : "hbox",
                                defaults : { margin : "5 10 5 0"},                                    
                                items : [
                                    {
                                        xtype: "combo",
                                        name: "BSA_FormulaWeight",
                                        fieldLabel: "Weight to use <em>*</em>",
                                        labelAlign: "top",
                                        width: 178,
                                        labelStyle: "font-weight: bold",
                                        store: {
                                                fields: ["weightType"],
                                                data: [ 
                                                        { weightType: "Actual Weight" },
                                                        { weightType: "Ideal Weight" },
                                                        { weightType: "Adjusted Weight" },
                                                        { weightType: "Lean Weight" },
                                                        { weightType: "Other" }
                                                ]
                                        },
                                        queryMode: "local",
                                        displayField: "weightType"
                                    },
                                    {
                                        xtype: "combo",
                                        name: "BSA_Formula",
                                        fieldLabel: "BSA Formula <em>*</em>",
                                        labelAlign: "top",
                                        width: 178,
                                        labelStyle: "font-weight: bold",
                                        store: {
                                                fields: ["formula"],
                                                data: [ 
                                                        { formula: "DuBois" },
                                                        { formula: "Mosteller" },
                                                        { formula: "Haycock" },
                                                        { formula: "Gehan and George" },
                                                        { formula: "Boyd" },
                                                        { formula: "Capped" }
                                                ]
                                        },
                                        queryMode: "local",
                                        displayField: "formula"
                                    }
                                ]
                            },                            
                            {
                                xtype: 'radiogroup',
                                labelAlign: 'top',
                                name: 'goalRadio',
                                layout: 'hbox',
                                fieldLabel: 'Select the goal for this Regimen <em>*</em>',
                                itemId: 'goalRadios',
                                columns: 1,
                                items: [
                                            { boxLabel  : 'Curative', name : 'Goal', inputValue: 'Curative', width: 100  }, 
                                            { boxLabel  : 'Palliative', name  : 'Goal', inputValue: 'Palliative', width: 125  }
                                    ]
                            },
                            {
                                xtype: 'radiogroup',
                                name: 'clinicalTrialRadio',
                                labelAlign: 'top',
                                layout: 'hbox',
                                fieldLabel: 'Specify the type of clinical trial <em>*</em>',
                                itemId: 'clinicalRadios',
                                columns: 1,
                                items: [
                                            { boxLabel  : 'Yes', name : 'ClinicalTrial', inputValue: true, width: 100  }, 
                                            { boxLabel  : 'No', name  : 'ClinicalTrial', inputValue: false, width: 125, checked: true  }
                                    ]
                            },
                            { 
                                xtype : "textfield", fieldLabel : "Type of Trial", width: 178, name: 'TypeOfTrial', hidden: true                                 
                            },
                            {
                                xtype: 'radiogroup',
                                name: 'amputeeRadio',
                                labelAlign: 'top',
                                layout: 'hbox',
                                fieldLabel: 'Is the Patient an Amputee? <em>*</em>',
                                itemId: 'amputeeRadios',
                                columns: 1,
                                items: [
                                            { boxLabel  : 'Yes', name : 'Amputee', inputValue: true, width: 100  }, 
                                            { boxLabel  : 'No', name  : 'Amputee', inputValue: false, width: 125, checked: true  }
                                    ]
                            },
                            {
                                xtype : "panel",
                                title : "Amputation Location <em>*</em>",
                                name : "amputationLocation",
                                hidden: true,
                                layout : "hbox",
                                defaults : { margin : "5 10 5 5", labelAlign : "top", labelWidth: 60},//, labelClsExtra : "NursingDocs-label" },
                                items : [
                                    { xtype: "checkboxgroup",
                                        //fieldLabel: "Left Side",
                                        vertical: true,
                                        width: 300,
                                        height: 150,
                                        columns: 2,
                                        shadow: true,
                                        name: "amputations",
                                        //border: '1',
                                        //style: {borderColor:'#000000', borderStyle:'solid', borderWidth:'1px'},
                                        items: [
                                        { boxLabel : "Upper Left Arm", name : "bodyPart1" },
                                        { boxLabel : "Lower Left Arm", name : "bodyPart2" },
                                        { boxLabel : "Left Hand and Fingers", name : "bodyPart3" },

                                        { boxLabel : "Left Thigh", name : "bodyPart4" },
                                        { boxLabel : "Lower Left Leg", name : "bodyPart5" },
                                        { boxLabel : "Left Foot", name : "bodyPart6" },

                                        { boxLabel : "Upper Right Arm", name : "bodyPart7" },
                                        { boxLabel : "Lower Right Arm", name : "bodyPart8" },
                                        { boxLabel : "Right Hand and Fingers", name : "bodyPart9" },

                                        { boxLabel : "Right Thigh", name : "bodyPart10" },
                                        { boxLabel : "Lower Right Leg", name : "bodyPart11" },
                                        { boxLabel : "Right Foot", name : "bodyPart12" }
                                        
                                        ]
                                    }
                                ]
                            },                            
                            {
                                xtype: "panel",
                                title : "Performance Status <em>*</em>",
                                name : "perfStatus",
                                defaults : { labelAlign : "right", labelWidth: 60},
                                items : [                                 
                                    {
                                        xtype: 'radiogroup',
                                        name: 'perfStatusRadio',
                                        labelAlign: 'top',
                                        //fieldLabel: 'Select the Performance Status',
                                        id: 'performanceRadios',
                                        margin: '5 5 25 5',
                                        columns: 1,
                                        items: this.itemsInGroup
                                    }
                                ]
                            }

                    ]
            } ];
            
            this.buttons = [
                    { text: 'Save', action: 'save' },
                    { text: 'Cancel', scope: this, handler: this.close }
            ];

            this.callParent(arguments);
	}
});