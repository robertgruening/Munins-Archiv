var _viewModelFormFund = null;

$(document).ready(function () {
	var viewModelFactory = new ViewModelFactory();
	_viewModelFormFund = viewModelFactory.getViewModelFormFund();

	InitStatusChanged();
	InitDataChanged();
	InitBreadcrumb();
	InitButtonNew();
	InitButtonSave();
	InitButtonDelete();
	InitButtonUndo();
	InitButtonSelectFundAttribut();
	InitButtonSelectKontext();
	InitButtonSelectAblage();

	InitFieldId();
	InitFieldBeschriftung();
	InitFieldFundattribute();
	InitFieldAnzahl();
	InitFieldDimension1();
	InitFieldDimension2();
	InitFiledDimension3();
	InitFieldMasse();
	InitFieldAblage();
	InitFieldKontext();

	if (getUrlParameterValue("Id")) {
		_viewModelFormFund.load(getUrlParameterValue("Id"));
	}
	else {
		_viewModelFormFund.updateAllListeners();
	}
});

function InitStatusChanged() {
	_viewModelFormFund.register("load", new GuiClient(showMessageLoaded, showErrorMessages));
	_viewModelFormFund.register("create", new GuiClient(showMessageCreated, showErrorMessages));
	_viewModelFormFund.register("save", new GuiClient(showMessageSaved, showErrorMessages));
	_viewModelFormFund.register("delete", new GuiClient(showMessageDeleted, showErrorMessages));
}

function InitDataChanged() {
	_viewModelFormFund.register("dataChanged", new GuiClient(EnableButtonUndo, showErrorMessages));
}

function InitBreadcrumb() {
	if (getFormMode() == "create") {
		$("#breadcrumb").Breadcrumb({
			PageName: "FundFormNew"
		});
	}
	else if (getFormMode() == "edit") {
		$("#breadcrumb").Breadcrumb({
			PageName: "FundFormEdit"
		});
	}
}

//#region form actions
//#region new
function InitButtonNew() {
	EnableButtonNew();
	$("#buttonNew").click(openFormNewElement);
}

function EnableButtonNew() {
	$("#buttonNew").removeClass("disabled");
	$("#buttonNew").prop("disabled", false);
}

function DisableButtonNew() {
	$("#buttonNew").addClass("disabled");
	$("#buttonNew").prop("disabled", true);
}
//#endregion

//#region save
function InitButtonSave() {
	EnableButtonSave();
	$("#buttonSave").click(function () { _viewModelFormFund.save(); });
}

function EnableButtonSave() {
	$("#buttonSave").removeClass("disabled");
	$("#buttonSave").prop("disabled", false);
}

function DisableButtonSave() {
	$("#buttonSave").addClass("disabled");
	$("#buttonSave").prop("disabled", true);
}
//#endregion

//#region delete
function InitButtonDelete() {
	DisableButtonDelete();
	$("#buttonDelete").click(ShowDialogDelete);
}

function EnableButtonDelete() {
	$("#buttonDelete").removeClass("disabled");
	$("#buttonDelete").prop("disabled", false);
}

function DisableButtonDelete() {
	$("#buttonDelete").addClass("disabled");
	$("#buttonDelete").prop("disabled", true);
}

function ShowDialogDelete() {
	$("#dialogDelete").empty();
	$("#dialogDelete").append(
		$("<p>").append("Möchten Sie diesen Fund löschen?")
	);
	$("#dialogDelete").dialog({
		height: "auto",
		width: 750,
		modal: true,
		buttons: {
			"Löschen": function () {
				_viewModelFormFund.delete();

				$(this).dialog("close");
			},
			"Abbrechen": function () {
				$(this).dialog("close");
			}
		}
	});

	$("#DialogDelete").dialog("open");
}
//#endregion

//#region undo
function InitButtonUndo() {
	DisableButtonUndo();
	_viewModelFormFund.register("dataResetted", new GuiClient(DisableButtonUndo, showErrorMessages));
	_viewModelFormFund.register("dataResetted", new GuiClient(ResetPropertiesMessages, showErrorMessages));
	$("#buttonUndo").click(function () { _viewModelFormFund.undoAllChanges(); });
}

function EnableButtonUndo() {
	$("#buttonUndo").removeClass("disabled");
	$("#buttonUndo").prop("disabled", false);
}

function DisableButtonUndo() {
	$("#buttonUndo").addClass("disabled");
	$("#buttonUndo").prop("disabled", true);
}

function ResetPropertiesMessages() {
	$(".fieldValue div[name=messages]").empty();
}
//#endregion
//#endregion

function GetIcon(type, state) {
	return IconConfig.getCssClasses(type, state);
}

//#region Id
function InitFieldId() {
	_viewModelFormFund.register("id", new GuiClient(setId, showErrorMessages));
}

function setId(id) {
	if (id == null) {
		document.title = "Fund";
		DisableButtonDelete();
	}
	else {
		document.title = "Fund: (" + id + ")";
		EnableButtonDelete();
	}
}
//#endregion

//#region Bezeichnung
function InitFieldBeschriftung() {
	_viewModelFormFund.register("bezeichnung", new GuiClient(setBezeichnung, showMessagesBeschriftung));
	$("#textboxBeschriftung").change(function () {
		_viewModelFormFund.setBezeichnung($("#textboxBeschriftung").val())
	});
}

function setBezeichnung(bezeichnung) {
	$("#textboxBeschriftung").val(bezeichnung);
}

function showMessagesBeschriftung(messages) {
	$("#divBeschriftung .fieldValue div[name=messages]").text(messages);
}
//#endregion

//#region Fundattribute
function InitFieldFundattribute() {
	_viewModelFormFund.register("fundAttribute", new GuiClient(setFundAttribute, showMessagesFundAttribute));
}

function InitButtonSelectFundAttribut() {
	$("#buttonAddFundAttribut").click(ShowFormSelectFundAttribut);
}

function ShowFormSelectFundAttribut() {
	$("#dialogSelectFundAttribut").dialog({
		height: "auto",
		width: 750,
		title: "Fundattribut auswählen",
		modal: true,
		resizable: false,
		buttons: {
			"Speichern": function () {
				_viewModelFormFund.addFundAttribut(GetSelectedFundAttribut());
				$(this).dialog("close");
			},
			"Abbrechen": function () {
				$(this).dialog("close");
			}
		}
	});

	$("#treeSelectFundAttribut").jstree(true).refresh();

	$("#dialogSelectFundAttribut").dialog("open");
}

function setFundAttribute(fundAttribute) {
	$("#divFundAttribute div #divList").empty();
	$("#divFundAttribute div #divList").append($("<ul>"));

	fundAttribute.forEach(fundAttribut => {
		var li = $("<li>");
		var linkFundAttribut = $("<a>");
		linkFundAttribut.attr("title", "gehe zu");
		linkFundAttribut.attr("href", "../FundAttribut/Explorer.html?Id=" + fundAttribut.Id);
		linkFundAttribut.text("/" + fundAttribut.Path);
		li.append(linkFundAttribut);

		var linkButtonDelete = $("<a>");
		linkButtonDelete.attr("title", "löschen");
		linkButtonDelete.attr("class", "linkButton riskyAction");
		linkButtonDelete.attr("href", "javascript:removeFundAttribut(" + fundAttribut.Id + ");");

		var icon = $("<i>");
		icon.attr("class", "fas fa-trash-alt");
		linkButtonDelete.append(icon);
		li.append(linkButtonDelete);


		$("#divFundAttribute div #divList ul").append(li);
	});
}

function removeFundAttribut(fundAttributId) {
	var fundAttribut = new Object();
	fundAttribut.Id = fundAttributId;

	_viewModelFormFund.removeFundAttribut(fundAttribut);
}

function showMessagesFundAttribute(messages) {
	$("#divFundAttribute .fieldValue div[name=messages]").text(messages);
}
//#endregion

//#region Anzahl
function InitFieldAnzahl() {
	_viewModelFormFund.register("anzahl", new GuiClient(setAnzahl, showMessagesAnzahl));
	$("#textboxAnzahl").change(function () {
		_viewModelFormFund.setAnzahl($("#textboxAnzahl").val())
	});
}

function setAnzahl(anzahl) {
	$("#textboxAnzahl").val(anzahl);
}

function showMessagesAnzahl(messages) {
	$("#divAnzahl .fieldValue div[name=messages]").text(messages);
}
//#endregion

//#region Dimension1
function InitFieldDimension1() {
	_viewModelFormFund.register("dimension1", new GuiClient(setDimension1, showMessagesDimension1));
	$("#textboxDimension1").change(function () {
		_viewModelFormFund.setDimension1($("#textboxDimension1").val())
	});
}

function setDimension1(dimension1) {
	$("#textboxDimension1").val(dimension1);
}

function showMessagesDimension1(messages) {
	$("#divDimension1 .fieldValue div[name=messages]").text(messages);
}
//#endregion

//#region Dimension2
function InitFieldDimension2() {
	_viewModelFormFund.register("dimension2", new GuiClient(setDimension2, showMessagesDimension2));
	$("#textboxDimension2").change(function () {
		_viewModelFormFund.setDimension2($("#textboxDimension2").val())
	});
}

function setDimension2(dimension2) {
	$("#textboxDimension2").val(dimension2);
}

function showMessagesDimension2(messages) {
	$("#divDimension2 .fieldValue div[name=messages]").text(messages);
}
//#endregion

//#region Dimension3
function InitFiledDimension3() {
	_viewModelFormFund.register("dimension3", new GuiClient(setDimension3, showMessagesDimension3));
	$("#textboxDimension3").change(function () {
		_viewModelFormFund.setDimension3($("#textboxDimension3").val())
	});
}

function setDimension3(dimension3) {
	$("#textboxDimension3").val(dimension3);
}

function showMessagesDimension3(messages) {
	$("#divDimension3 .fieldValue div[name=messages]").text(messages);
}
//#endregion

//#region Masse
function InitFieldMasse() {
	_viewModelFormFund.register("masse", new GuiClient(setMasse, showMessagesMasse));
	$("#textboxMasse").change(function () {
		_viewModelFormFund.setMasse($("#textboxMasse").val())
	});
}

function setMasse(masse) {
	$("#textboxMasse").val(masse);
}

function showMessagesMasse(messages) {
	$("#divMasse .fieldValue div[name=messages]").text(messages);
}
//#endregion

//#region Ablage
function InitFieldAblage() {
	_viewModelFormFund.register("ablage", new GuiClient(setAblage, showMessagesAblage));
}

function InitButtonSelectAblage() {
	$("#buttonSelectAblage").click(ShowFormSelectAblage);
}

function setAblage(ablage) {
	if (ablage == null) {
		$("#linkSelectedAblage").text("");
		$("#linkSelectedAblage").attr("href", "");
	}
	else {
		$("#linkSelectedAblage").text("/" + ablage.Path);
		$("#linkSelectedAblage").attr("href", "../Ablage/Explorer.html?Id=" + ablage.Id);
	}
}

function ShowFormSelectAblage() {
	$("#dialogSelectAblage").dialog({
		height: "auto",
		width: 750,
		title: "Ablage auswählen",
		modal: true,
		resizable: false,
		buttons: {
			"Speichern": function () {
				_viewModelFormFund.setAblage(GetSelectedAblage());
				setAblage(_viewModelFormFund.getAblage());
				$(this).dialog("close");
			},
			"Abbrechen": function () {
				$(this).dialog("close");
			}
		}
	});

	$("#treeSelectAblage").jstree(true).refresh();

	$("#dialogSelectAblage").dialog("open");
}

function showMessagesAblage(messages) {
	$("#divAblage .fieldValue div[name=messages]").text(messages);
}
//#endregion

//#region Kontext
function InitFieldKontext() {
	_viewModelFormFund.register("kontext", new GuiClient(setKontext, showMessagesKontext));
}

function InitButtonSelectKontext() {
	$("#buttonSelectKontext").click(ShowFormSelectKontext);
}

function setKontext(kontext) {
	if (kontext == null) {
		$("#linkSelectedKontext").text("");
		$("#linkSelectedKontext").attr("href", "");
	}
	else {
		$("#linkSelectedKontext").text("/" + kontext.Path);
		$("#linkSelectedKontext").attr("href", "../Kontext/Explorer.html?Id=" + kontext.Id);
	}
}

function ShowFormSelectKontext() {
	$("#dialogSelectKontext").dialog({
		height: "auto",
		width: 750,
		title: "Kontext auswählen",
		modal: true,
		resizable: false,
		buttons: {
			"Speichern": function () {
				_viewModelFormFund.setKontext(GetSelectedKontext());
				setKontext(_viewModelFormFund.getKontext());
				$(this).dialog("close");
			},
			"Abbrechen": function () {
				$(this).dialog("close");
			}
		}
	});

	$("#treeSelectKontext").jstree(true).refresh();

	$("#dialogSelectKontext").dialog("open");
}

function showMessagesKontext(messages) {
	$("#divKontext .fieldValue div[name=messages]").text(messages);
}
//#endregion


function showMessageLoaded(element) {
    $.toast({
        heading: "Information",
        text: "Fund (" + element.Id + ") geladen",
        icon: "info"
    });
}

function showMessageCreated(element) {
    $.toast({
        heading: "Information",
        text: "Fund (" + element.Id + ") erzeugt",
        icon: "success"
    });
}

function showMessageSaved(element) {
    $.toast({
        heading: "Information",
        text: "Fund (" + element.Id + ") gespeichert",
        icon: "success"
    });
}

function showMessageDeleted(element) {
    $.toast({
        heading: "Information",
        text: "Fund (" + element.Id + ") gelöscht",
        icon: "success"
    });
}