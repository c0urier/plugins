
<div class="flash_message" style="display: none;"></div>

<div id="page">
	<table class="datatable">
		<thead>
		<tr>
			<th style="width:10%;">{TR_NAME}</th>
			<th style="width:25%;">{TR_BINARY}</th>
			<th style="width:25%;">{TR_CONFDIR}</th>
			<th style="width:30%;">{TR_STATUS}</th>
			<th style="width:10%;">{TR_ACTIONS}</th>
		</tr>
		</thead>
		<tfoot>
		<tr>
			<td>{TR_NAME}</td>
			<td>{TR_BINARY}</td>
			<td>{TR_CONFDIR}</td>
			<td>{TR_STATUS}</td>
			<td>{TR_ACTIONS}</td>
		</tr>
		</tfoot>
		<tbody>
		<tr>
			<td colspan="6" class="dataTables_empty">{TR_PROCESSING_DATA}</td>
		</tr>
		</tbody>
	</table>

	<div class="buttons">
		<button data-action="add">{TR_ADD_NEW_VERSION}</button>
	</div>
</div>

<div id="php_dialog" style="display: none;">
	<form id="php_frm">
		<table class="firstColFixed">
			<tr>
				<td><label for="version_name">{TR_NAME}</label></td>
				<td><input type="text" name="version_name" id="version_name" maxlength="30" value=""></td>
			</tr>
			<tr>
				<td><label for="version_binary_path">{TR_BINARY_PATH}</label></td>
				<td><input type="text" name="version_binary_path" id="version_binary_path" maxlength="255" value=""></td>
			</tr>
			<tr>
				<td><label for="version_confdir_path">{TR_CONFDIR_PATH}</span></label></td>
				<td><input type="text" name="version_confdir_path" id="version_confdir_path" maxlength="255" value=""></td>
			</tr>
		</table>
		<input type="hidden" name="version_id" id="version_id" value="">
	</form>
</div>

<script>
	var oTable;

	function doRequest(rType, action, data)
	{
		return $.ajax({
			dataType: "json",
			type: rType,
			url: "/admin/phpswitcher?action=" + action,
			data: data,
			timeout: 3000
		});
	}

	function createDialog(title, action)
	{
		return $("#php_dialog").dialog({
			autoOpen: false,
			height: 330,
			width: 550,
			modal: true,
			title: title,
			buttons: {
				"{TR_SAVE}": function() {
					doRequest('POST', action, $("#php_frm").serialize()).done(function(data) {
						$("#php_dialog").dialog("close");
						flashMessage('success', data.message);
						oTable.fnDraw();
					});
				},
				"{TR_CANCEL}": function() { $(this).dialog("close"); }
			},
			open: function() {
				if(action == 'edit') {
					frm = $("#php_frm");
					$.each($(this).data('data'), function(k,v) { $("#" + k, frm).val(v);})
				}
			},
			close: function() {
				$("#php_frm").get(0).reset()
			}
		});
	}

	function flashMessage(type, message)
	{
		var flashMessage = $(".flash_message").text(message).addClass(type);

		setTimeout(function () { flashMessage.fadeOut(1000); }, 3000);
		setTimeout(function () { flashMessage.removeClass(type); }, 4000);

		flashMessage.show();
	}

	$(document).ready(function() {
		jQuery.fn.dataTableExt.oApi.fnProcessingIndicator = function (oSettings, onoff) {
			if (typeof(onoff) == "undefined") { onoff = true; }
			this.oApi._fnProcessingDisplay(oSettings, onoff);
		};

		oTable = $(".datatable").dataTable({
			oLanguage: {DATATABLE_TRANSLATIONS},
			iDisplayLength: 5,
			bProcessing: true,
			bServerSide: true,
			sAjaxSource: "/admin/phpswitcher?action=table",
			bStateSave: true,
			aoColumnDefs: [ { bSortable: false, bSearchable: false, aTargets: [ 4 ] } ],
			aoColumns: [
				{ mData: "version_name" },
				{ mData: "version_binary_path" },
				{ mData: "version_confdir_path" },
				{ mData: "version_status" },
				{ mData: "actions" }
			],
			fnServerData: function (sSource, aoData, fnCallback) {
				$.ajax( {
					dataType: "json",
					type: "GET",
					url: sSource,
					data: aoData,
					success: fnCallback,
					timeout: 5000,
					error: function(xhr, textStatus, error) { oTable.fnProcessingIndicator(false); }
				}).done(function() {
					oTable.find("span").imscpTooltip({ extraClass: "tooltip_icon tooltip_notice" });
				});
			}
		});

		$("#page").on("click", "span[data-action], button", function() {
			action = $(this).data('action');
			versionName = $(this).data('version-name');
			versionId = $(this).data('version-id');

			 switch (action) {
			 	case "add":
					createDialog(
						"{TR_NEW}", action).data({ version_id: versionId, version_name: versionName }
					).dialog("open");
					break;
				case "edit":
					doRequest("GET", "get", { version_id: versionId, version_name: versionName }).done(
						function(data) {
							createDialog(sprintf("{TR_EDIT}", versionName), action).data({ data: data }).dialog("open");
						}
					);

					break;
				 case "delete":
					 if(confirm("{TR_DELETE_CONFIRM}")) {
						 doRequest( "POST", action, { version_id: versionId, version_name: versionName } ).done(
						 	function(data) { oTable.fnDraw(); flashMessage('success', data.message); }
						 );
					 }
					 break;
			 	default:
			 		alert("{TR_UNKNOWN_ACTION}");
			 }
		});

		$(document).ajaxStart(function() { oTable.fnProcessingIndicator(); });
		$(document).ajaxStop(function() { oTable.fnProcessingIndicator(false); });
		$(document).ajaxError(function(e, jqXHR, settings, exception) {
			if(jqXHR.responseJSON != "") {
				flashMessage("error", jqXHR.responseJSON.message);
			} else if(exception == "timeout") {
				flashMessage("error", {TR_REQUEST_TIMEOUT});
			} else {
				flashMessage("error", {TR_REQUEST_ERROR});
			}
		});
	});
</script>
