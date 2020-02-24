var _viewModelFormFundAttribut = null;
var _viewModelListFundAttributType = null;

$(document).ready(function () {
	var viewModelFactory = new ViewModelFactory();
	_viewModelFormFundAttribut = viewModelFactory.getViewModelFormFundAttribut();
	_viewModelListFundAttributType = viewModelFactory.getViewModelListFundAttributType();

	InitStatusChanged();
	InitDataChanged();
	InitBreadcrumb();
	InitButtonNew();
	InitButtonSave();
	InitButtonDelete();
	InitButtonUndo();
	InitButtonToOverview();

	InitFieldId();
	InitFieldType();
	InitFieldBezeichnung();
	InitFieldPath();
	InitFieldCountOfChildren();
	InitFieldCountOfFunde();
});

function loadForm() {
	console.info("loading form");

	if (getUrlParameterValue("Id")) {
		console.debug("Fundattribut is requested by ID", getUrlParameterValue("Id"));
		_viewModelFormFundAttribut.load(getUrlParameterValue("Id"));
	}
	else if (getUrlParameterValue("Parent_Id")) {
		console.debug("creation of a new Fundattribut is requested with parent ID", getUrlParameterValue("Parent_Id"));
		var parent = new FundAttribut();
		parent.Id = getUrlParameterValue("Parent_Id");

		_viewModelFormFundAttribut.setParent(parent);
		showMessageParentSet();
		_viewModelFormFundAttribut.updateAllListeners();
	}
	else {
		console.debug("there is no Fundattribut requested");
		_viewModelFormFundAttribut.updateAllListeners();
	}
}

function InitStatusChanged() {
	_viewModelFormFundAttribut.register("load", new GuiClient(showMessageLoaded, showErrorMessages));
	_viewModelFormFundAttribut.register("create", new GuiClient(loadCreatedElement, showErrorMessages));
	_viewModelFormFundAttribut.register("save", new GuiClient(showMessageSaved, showErrorMessages));
	_viewModelFormFundAttribut.register("delete", new GuiClient(showMessageDeleted, showErrorMessages));
}

function InitDataChanged() {
	_viewModelFormFundAttribut.register("dataChanged", new GuiClient(EnableButtonUndo, showErrorMessages));
}

function InitBreadcrumb()
{
	if (getFormMode() == "create") {
		$("#breadcrumb").Breadcrumb({
			PageName: "FundAttributFormNew"
		});
	}
	else if (getFormMode() == "edit") {
		$("#breadcrumb").Breadcrumb({
			PageName: "FundAttributFormEdit"
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
		text: "übergeordnetes Fundattribut gesetzt",
		icon: "info"
	});
}

function showMessageLoaded(element) {
	$.toast({
		heading: "Information",
		text: "Fundattribut \"" + element.Bezeichnung + "\" (" + element.Type.Bezeichnung + ") geladen",
		icon: "success"
	});
}

function showMessageSaved(element) {
	$.toast({
		heading: "Information",
		text: "Fundattribut \"" + element.Bezeichnung + "\" (" + element.Type.Bezeichnung + ") gespeichert",
		icon: "success"
	});
}

function showMessageDeleted(element) {
	$.toast({
		heading: "Information",
		text: "Fundattribut \"" + element.Bezeichnung + "\" (" + element.Type.Bezeichnung + ") gelöscht",
		icon: "success"
	});
}
//#endregion

//#region form fields

//#region Id
function InitFieldId() {
	_viewModelFormFundAttribut.register("id", new GuiClient(setId, showErrorMessages));
}

function setId(id) {
	if (id == null) {
		document.title = "Fundattribut";
		DisableButtonDelete();
	}
	else {
		document.title = "Fundattribut: (" + id + ")";
		EnableButtonDelete();
	}
}
//#endregion

//#region Type
function InitFieldType() {
	console.info("initialising field 'Fundattribut type'");

	_viewModelFormFundAttribut.register("type", new GuiClient(setType, showMessagesType));

	_viewModelListFundAttributType.register("loadAll", new GuiClient(fillSelectionFundAttributType, showMessagesType));
	_viewModelListFundAttributType.loadAll();

	$("#selectType").change(function () {
		var fundAttributType = new FundAttributType();
		fundAttributType.Id = $("#selectType").val();

		_viewModelFormFundAttribut.setType(fundAttributType);
	});
}

function fillSelectionFundAttributType(fundAttributTypes) {
	console.info("setting values of field 'Fundattribut type'");
	console.debug("values of 'Fundattribut type'", fundAttributTypes);
	$("#selectType").empty();
	$("#selectType").append("<option value='' >Bitte wählen</option>");

	fundAttributTypes.forEach(fundAttributType => {
		$("#selectType").append("<option value=" + fundAttributType.Id + ">" + fundAttributType.Bezeichnung + "</option>");
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
	_viewModelFormFundAttribut.register("bezeichnung", new GuiClient(setBezeichnung, showMessagesBezeichnung));
	$("#textboxBezeichnung").change(function () {
		_viewModelFormFundAttribut.setBezeichnung($("#textboxBezeichnung").val())
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
	_viewModelFormFundAttribut.register("path", new GuiClient(setPath, null));
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
	_viewModelFormFundAttribut.register("children", new GuiClient(setCountOfChildren, null));
}

function setCountOfChildren(children) {
	console.info("setting value of 'count of children'");
	console.debug("children are ", children);
	$("#labelCountOfChildren").text(children.length);
}
//#endregion

//#region Anzahl von Funde
function InitFieldCountOfFunde() {
	_viewModelFormFundAttribut.register("countOfFunde", new GuiClient(setCountOfFunde, null));
}

function setCountOfFunde(countOfFunde) {
	console.info("setting value of 'count of funde'");
	console.debug("Funde are ", countOfFunde);
	$("#labelCountOfFunde").text(countOfFunde);
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
	$("#buttonSave").click(function () { _viewModelFormFundAttribut.save(); });
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
		$("<p>").append("Möchten Sie dieses Fundattribut löschen?")
	);
	$("#dialogDelete").dialog({
		height: "auto",
		width: 750,
		modal: true,
		buttons: {
			"Löschen": function () {
				_viewModelFormFundAttribut.delete();

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
	_viewModelFormFundAttribut.register("dataResetted", new GuiClient(DisableButtonUndo, showErrorMessages));
	_viewModelFormFundAttribut.register("dataResetted", new GuiClient(ResetPropertiesMessages, showErrorMessages));
	$("#buttonUndo").click(function () {
		console.log("button 'undo' clicked");
		_viewModelFormFundAttribut.undoAllChanges();
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
	_viewModelFormFundAttribut.register("parent", new GuiClient(EnableButtonToOverview, showErrorMessages));
	$("#buttonToOverview").attr("href", "/Munins Archiv/src/pages/FundAttribut/Explorer.html", "_self");
}

function EnableButtonToOverview(parent) {
	if (parent === undefined ||
		parent === null ||
		parent.Id === undefined) {

			$("#buttonToOverview").attr("href", "/Munins Archiv/src/pages/FundAttribut/Explorer.html", "_self");
		}
		else {
			$("#buttonToOverview").attr("href", "/Munins Archiv/src/pages/FundAttribut/Explorer.html?Id=" + parent.Id, "_self");
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
