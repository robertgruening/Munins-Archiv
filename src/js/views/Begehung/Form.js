var _viewModelFormBegehung = null;
var _viewModelListKontextType = null;

$(document).ready(function () {
	var viewModelFactory = new ViewModelFactory();
	_viewModelFormBegehung = viewModelFactory.getViewModelFormBegehung();
	_viewModelListKontextType = viewModelFactory.getViewModelListKontextType();

	InitStatusChanged();
	InitDataChanged();
	InitBreadcrumb();
	InitButtonNew();
	InitButtonSave();
	InitButtonDelete();
	InitButtonUndo();
	InitButtonToOverview();
	InitButtonSelectLfdNummer();

	InitFieldId();
	InitFieldType();
	InitFieldBezeichnung();
	InitFieldPath();
	InitFieldCountOfChildren();
	InitFieldLfdNummern();
	InitFieldDatum();
	InitFieldKommentar();
	InitFieldCountOfFunde();
	InitGridFunde();
});

function loadForm() {
	console.info("loading form");

	if (getUrlParameterValue("Id")) {
		console.debug("Kontext is requested by ID", getUrlParameterValue("Id"));
		_viewModelFormBegehung.load(getUrlParameterValue("Id"));
	}
	else if (getUrlParameterValue("Parent_Id")) {
		console.debug("creation of a new Kontext is requested with parent ID", getUrlParameterValue("Parent_Id"));
		var parent = new Kontext();
		parent.Id = getUrlParameterValue("Parent_Id");

		_viewModelFormBegehung.setParent(parent);
		showMessageParentSet();
		_viewModelFormBegehung.updateAllListeners();
	}
	else {
		console.debug("there is no Kontext requested");
		_viewModelFormBegehung.updateAllListeners();
	}
}

function InitStatusChanged() {
	_viewModelFormBegehung.register("load", new GuiClient(showMessageLoaded, showErrorMessages));
	_viewModelFormBegehung.register("create", new GuiClient(loadCreatedElement, showErrorMessages));
	_viewModelFormBegehung.register("save", new GuiClient(showMessageSaved, showErrorMessages));
	_viewModelFormBegehung.register("delete", new GuiClient(showMessageDeleted, showErrorMessages));
}

function InitDataChanged() {
	_viewModelFormBegehung.register("dataChanged", new GuiClient(EnableButtonUndo, showErrorMessages));
}

function InitBreadcrumb()
{
	if (getFormMode() == "create") {
		$("#breadcrumb").Breadcrumb({
			PageName: "BegehungFormNew"
		});
	}
	else if (getFormMode() == "edit") {
		$("#breadcrumb").Breadcrumb({
			PageName: "BegehungFormEdit"
		});
	}
}

function loadCreatedElement(element) {
	window.open(window.location.href.replace(window.location.search, "") + "?Id=" + element.Id, "_self");
}

//#region messages
function showMessageParentSet() {
	$.toast({
		heading: "Information",
		text: "übergeordnete Kontext gesetzt",
		icon: "info"
	});
}

function showMessageLoaded(element) {
	$.toast({
		heading: "Information",
		text: "Kontext \"" + element.Bezeichnung + "\" (" + element.Type.Bezeichnung + ") geladen",
		icon: "success"
	});
}

function showMessageSaved(element) {
	$.toast({
		heading: "Information",
		text: "Kontext \"" + element.Bezeichnung + "\" (" + element.Type.Bezeichnung + ") gespeichert",
		icon: "success"
	});
}

function showMessageDeleted(element) {
	$.toast({
		heading: "Information",
		text: "Kontext \"" + element.Bezeichnung + "\" (" + element.Type.Bezeichnung + ") gelöscht",
		icon: "success"
	});
}
//#endregion

//#region form fields

//#region Id
function InitFieldId() {
	_viewModelFormBegehung.register("id", new GuiClient(setId, showErrorMessages));
}

function setId(id) {
	if (id == null) {
		document.title = "Kontext";
		DisableButtonDelete();
	}
	else {
		document.title = "Kontext: (" + id + ")";
		EnableButtonDelete();
	}
}
//#endregion

//#region Type
function InitFieldType() {
	console.info("initialising field 'Kontext type'");

	_viewModelFormBegehung.register("type", new GuiClient(setType, showMessagesType));

	_viewModelListKontextType.register("loadAll", new GuiClient(fillSelectionKontextType, showMessagesType));
	_viewModelListKontextType.loadAll();

	$("#selectType").change(function () {
		var kontextType = new KontextType();
		kontextType.Id = $("#selectType").val();
		kontextType.Bezeichnung = $("#selectType option:selected").text();

		_viewModelFormBegehung.setType(kontextType);
	});
}

function fillSelectionKontextType(kontextTypes) {
	console.info("setting values of field 'Kontext type'");
	console.debug("values of 'Kontext type'", kontextTypes);
	$("#selectType").empty();
	$("#selectType").append("<option value='' >Bitte wählen</option>");

	kontextTypes.forEach(kontextType => {
		$("#selectType").append("<option value=" + kontextType.Id + ">" + kontextType.Bezeichnung + "</option>");
	});

	loadForm();
}

function setType(type) {
	console.info("setting value of 'type'");
	console.debug("type is ", type);
	$("#selectType option[value='" + type.Id + "']").attr("selected","selected");
}

function showMessagesType(messages) {
	$("#divType .fieldValue div[name=messages]").text(messages);
}
//#endregion

//#region Bezeichnung
function InitFieldBezeichnung() {
	_viewModelFormBegehung.register("bezeichnung", new GuiClient(setBezeichnung, showMessagesBezeichnung));
	$("#textboxBezeichnung").change(function () {
		_viewModelFormBegehung.setBezeichnung($("#textboxBezeichnung").val())
	});
}

function setBezeichnung(bezeichnung) {
	console.log("setting value of 'Bezeichnung' to " + bezeichnung);
	$("#textboxBezeichnung").val(bezeichnung);
}

function showMessagesBezeichnung(messages) {
	$("#divBezeichnung .fieldValue div[name=messages]").text(messages);
}
//#endregion

//#region Path
function InitFieldPath() {
	_viewModelFormBegehung.register("path", new GuiClient(setPath, null));
}

function setPath(path) {
	console.info("setting value of 'Path'");
	console.debug("'Path' is", path);

	if (!path.startsWith("/")) {
		console.warn("added '/' to path");
		path = "/" + path;
	}

	$("#labelPath").text(path);
}
//#endregion

//#region Anzahl von Children
function InitFieldCountOfChildren() {
	_viewModelFormBegehung.register("children", new GuiClient(setCountOfChildren, null));
}

function setCountOfChildren(children) {
	console.info("setting value of 'count of children'");
	console.debug("children are ", children);
	$("#labelCountOfChildren").text(children.length);
}
//#endregion

//#region LfD-Nummern
function InitFieldLfdNummern() {
	_viewModelFormBegehung.register("lfdNummern", new GuiClient(setLfdNummern, showMessagesLfdNummern));
}

function InitButtonSelectLfdNummer() {
	$("#buttonSelectLfdNummer").click(ShowFormSelectLfdNummer);
}

function ShowFormSelectLfdNummer() {
	$("#dialogSelectLfdNummer").dialog({
		height: "auto",
		width: 750,
		title: "Lfd-Nummer auswählen",
		modal: true,
		resizable: false,
		buttons: {
			"Speichern": function () {
				_viewModelFormBegehung.addLfdNummer(GetSelectedLfdNummerNode());
				$(this).dialog("close");
			},
			"Abbrechen": function () {
				$(this).dialog("close");
			}
		}
	});
	_webServiceClientLfdNummer.LoadAll("listSelect.loaded");
	$("#dialogSelectLfdNummer").dialog("open");
}

function setLfdNummern(lfdNummern) {
	console.info("setting value of 'LfdNummern'");
	console.debug("LfdNummern are ", lfdNummern);

	$("#divLfdNummern div #divList").empty();

	if (lfdNummern.length == 0) {
		return;
	}

	$("#divLfdNummern div #divList").append($("<ul>"));

	lfdNummern.forEach(lfdNummer => {
		var li = $("<li>");
		var labelLfdNummer = $("<label>");
		labelLfdNummer.text(lfdNummer.Bezeichnung);
		li.append(labelLfdNummer);

		var linkButtonDelete = $("<a>");
		linkButtonDelete.attr("title", "löschen");
		linkButtonDelete.attr("class", "linkButton riskyAction");
		linkButtonDelete.attr("href", "javascript:removeLfdNummer(" + lfdNummer.Id + ");");

		var icon = $("<i>");
		icon.attr("class", "fas fa-trash-alt");
		linkButtonDelete.append(icon);
		li.append(linkButtonDelete);


		$("#divLfdNummern div #divList ul").append(li);
	});
}

function removeLfdNummer(lfdNummerId) {
	var lfdNummer = new Object();
	lfdNummer.Id = lfdNummerId;

	_viewModelFormBegehung.removeLfdNummer(lfdNummer);
}

function showMessagesLfdNummern(messages) {
	$("#divLfdNummern .fieldValue div[name=messages]").text(messages);
}
//#endregion

//#region Datum
function InitFieldDatum() {
	_viewModelFormBegehung.register("datum", new GuiClient(setDatum, showMessagesDatum));
	$("#textboxDatum").change(function () {
		_viewModelFormBegehung.setDatum($("#textboxDatum").val())
	});
}

function setDatum(datum) {
	console.log("setting value of 'Datum' to " + datum);
	$("#textboxDatum").val(datum);
}

function showMessagesDatum(messages) {
	$("#divDatum .fieldValue div[name=messages]").text(messages);
}
//#endregion

//#region Kommentar
function InitFieldKommentar() {
	_viewModelFormBegehung.register("kommentar", new GuiClient(setKommentar, showMessagesKommentar));
	$("#textareaKommentar").change(function () {
		_viewModelFormBegehung.setKommentar($("#textareaKommentar").val())
	});
}

function setKommentar(kommentar) {
	console.log("setting value of 'Kommentar' to " + kommentar);
	$("#textareaKommentar").val(kommentar);
}

function showMessagesKommentar(messages) {
	$("#divKommentar .fieldValue div[name=messages]").text(messages);
}
//#endregion

//#region Anzahl von Funde
function InitFieldCountOfFunde() {
	_viewModelFormBegehung.register("funde", new GuiClient(setCountOfFunde, null));
}

function setCountOfFunde(funde) {
	console.info("setting value of 'count of funde'");
	console.debug("Funde are ", funde);
	$("#labelCountOfFunde").text(funde.length);
}
//#endregion

//#region Funde
var IconField = function(config) {
	jsGrid.Field.call(this, config);
}

IconField.prototype = new jsGrid.Field({
	itemTemplate: function(value) {
		return $("<i>").addClass(value);
	}
});

var LinkToFundFormField = function(config) {
	jsGrid.Field.call(this, config);
}

LinkToFundFormField.prototype = new jsGrid.Field({
	itemTemplate: function(value) {
		var icon = $("<i>").addClass("fas fa-external-link-alt");
		var link = $("<a>").append(icon);
		$(link).attr("href", "/Munins Archiv/src/pages/Fund/Form.html?Id=" + value);
		$(link).attr("target", "_blank");
		$(link).attr("title", "Fundformular öffnen");
		return link;
	}
});

function InitGridFunde()
{
	console.info("initializing grid of 'funde'");
	_viewModelFormBegehung.register("funde", new GuiClient(UpdateGridData, showErrorMessages));

	jsGrid.locale("de");
	jsGrid.fields.icon = IconField;
	jsGrid.fields.linkToFundFormField = LinkToFundFormField;

	$("#gridFunde").jsGrid({
		width: "100%",

		inserting: false,
		editing: false,
		sorting: false,
		paging: false,
		autoload: false,

		fields: [
			{
				title: "",
				name: "Id",
				type: "linkToFundFormField",
				width: 35
			},
			{
				title: "Icon",
				name: "Icon",
				type: "icon"
			},
			{
				name: "Anzahl",
				type: "text"
			},
			{
				title: "Beschriftung",
				name: "Bezeichnung",
				type: "text"
			},
			{
				title: "Fundattribute",
				name: "FundAttributAnzeigeTexte",
				type: "text"
			}
		],

		rowDoubleClick: function(args) {
			console.info("row double clicked");
			console.debug("selected grid item", args.item);

			window.open("/Munins Archiv/src/pages/Fund/Form.html?Id=" + args.item.Id, "_self");
		}
	});
}

function UpdateGridData(funde) {
	console.info("setting value of 'funde'");
	console.debug("'funde'", funde);
	$("#gridFunde").empty();

	var entries = new Array();

	funde.forEach(fund => {
		var copy = JSON.parse(JSON.stringify(fund));
		copy.Icon = IconConfig.getCssClasses("Fund");
		copy.Anzahl = fund.Anzahl.replace("-", ">");
		copy.FundAttributAnzeigeTexte = "<ul>"

		fund.FundAttribute.forEach(fundAttribut => {
			copy.FundAttributAnzeigeTexte += "<li>" + fundAttribut.Type.Bezeichnung + ": " + fundAttribut.Bezeichnung + "</li>"
		});

		copy.FundAttributAnzeigeTexte += "</ul>"
		entries.push(copy);
	});

	$("#gridFunde").jsGrid({
		data: entries
	});
}
//#endregion
//#endregion

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
	$("#buttonSave").click(function () { _viewModelFormBegehung.save(); });
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
		$("<p>").append("Möchten Sie diese Kontext löschen?")
	);
	$("#dialogDelete").dialog({
		height: "auto",
		width: 750,
		modal: true,
		buttons: {
			"Löschen": function () {
				_viewModelFormBegehung.delete();

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
	_viewModelFormBegehung.register("dataResetted", new GuiClient(DisableButtonUndo, showErrorMessages));
	_viewModelFormBegehung.register("dataResetted", new GuiClient(ResetPropertiesMessages, showErrorMessages));
	$("#buttonUndo").click(function () {
		console.log("button 'undo' clicked");
		_viewModelFormBegehung.undoAllChanges();
	});
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

//#region open overview
function InitButtonToOverview() {
	EnableButtonToOverview();
	_viewModelFormBegehung.register("parent", new GuiClient(EnableButtonToOverview, showErrorMessages));
	$("#buttonToOverview").attr("href", "/Munins Archiv/src/pages/Kontext/Explorer.html", "_self");
}

function EnableButtonToOverview(parent) {
	if (parent === undefined ||
		parent === null ||
		parent.Id === undefined) {

			$("#buttonToOverview").attr("href", "/Munins Archiv/src/pages/Kontext/Explorer.html", "_self");
		}
		else {
			$("#buttonToOverview").attr("href", "/Munins Archiv/src/pages/Kontext/Explorer.html?Id=" + parent.Id, "_self");
		}

		$("#buttonToOverview").removeClass("disabled");
		$("#buttonToOverview").prop("disabled", false);
	}

	function DisableButtonToOverview() {
		$("#buttonToOverview").addClass("disabled");
		$("#buttonToOverview").prop("disabled", true);
	}
	//#endregion
	//#endregion
