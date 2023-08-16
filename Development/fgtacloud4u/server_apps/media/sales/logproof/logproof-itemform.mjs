var this_page_id;
var this_page_options;

import {fgta4slideselect} from  '../../../../../index.php/asset/fgta/framework/fgta4libs/fgta4slideselect.mjs'

const txt_title = $('#pnl_edititemform-title')
const btn_edit = $('#pnl_edititemform-btn_edit')
const btn_save = $('#pnl_edititemform-btn_save')
const btn_delete = $('#pnl_edititemform-btn_delete')
const btn_prev = $('#pnl_edititemform-btn_prev')
const btn_next = $('#pnl_edititemform-btn_next')
const btn_addnew = $('#pnl_edititemform-btn_addnew')
const chk_autoadd = $('#pnl_edititemform-autoadd')


const pnl_form = $('#pnl_edititemform-form')
const obj = {
	txt_medialogproofitem_id: $('#pnl_edititemform-txt_medialogproofitem_id'),
	txt_mediaadslot_timestart: $('#pnl_edititemform-txt_mediaadslot_timestart'),
	txt_mediaadslot_timeend: $('#pnl_edititemform-txt_mediaadslot_timeend'),
	txt_mediaadslot_descr: $('#pnl_edititemform-txt_mediaadslot_descr'),
	txt_actual_timestart: $('#pnl_edititemform-txt_actual_timestart'),
	txt_actual_timeend: $('#pnl_edititemform-txt_actual_timeend'),
	txt_actual_duration: $('#pnl_edititemform-txt_actual_duration'),
	txt_spot_id: $('#pnl_edititemform-txt_spot_id'),
	txt_mediaorderitem_validr: $('#pnl_edititemform-txt_mediaorderitem_validr'),
	txt_mediaorderitem_ppnidr: $('#pnl_edititemform-txt_mediaorderitem_ppnidr'),
	cbo_pph_taxtype_id: $('#pnl_edititemform-cbo_pph_taxtype_id'),
	cbo_mediaorder_id: $('#pnl_edititemform-cbo_mediaorder_id'),
	cbo_mediaorderitem_id: $('#pnl_edititemform-cbo_mediaorderitem_id'),
	cbo_projbudget_id: $('#pnl_edititemform-cbo_projbudget_id'),
	cbo_projbudgettask_id: $('#pnl_edititemform-cbo_projbudgettask_id'),
	cbo_billoutpreprocess_id: $('#pnl_edititemform-cbo_billoutpreprocess_id'),
	txt_mediaordertype_id: $('#pnl_edititemform-txt_mediaordertype_id'),
	txt_logproof_partnerinfo: $('#pnl_edititemform-txt_logproof_partnerinfo'),
	txt_agency_partner_id: $('#pnl_edititemform-txt_agency_partner_id'),
	txt_advertiser_partner_id: $('#pnl_edititemform-txt_advertiser_partner_id'),
	txt_brand_id: $('#pnl_edititemform-txt_brand_id'),
	txt_project_id: $('#pnl_edititemform-txt_project_id'),
	txt_projecttask_id: $('#pnl_edititemform-txt_projecttask_id'),
	txt_medialogproof_id: $('#pnl_edititemform-txt_medialogproof_id')
}


let form;
let header_data;



export async function init(opt) {
	this_page_id = opt.id
	this_page_options = opt;

	
	form = new global.fgta4form(pnl_form, {
		primary: obj.txt_medialogproofitem_id,
		autoid: true,
		logview: 'trn_medialogproofitem',
		btn_edit: btn_edit,
		btn_save: btn_save,
		btn_delete: btn_delete,		
		objects : obj,
		OnDataSaving: async (data, options) => { await form_datasaving(data, options) },
		OnDataSaved: async (result, options) => {  await form_datasaved(result, options) },
		OnDataDeleting: async (data, options) => { await form_deleting(data, options) },
		OnDataDeleted: async (result, options) => { await form_deleted(result, options) },
		OnIdSetup : (options) => { form_idsetup(options) },
		OnViewModeChanged : (viewonly) => { form_viewmodechanged(viewonly) }
	})	

	form.AllowAddRecord = true
	form.AllowRemoveRecord = true
	form.AllowEditRecord = true
	form.CreateRecordStatusPage(this_page_id)
	form.CreateLogPage(this_page_id)




	obj.cbo_pph_taxtype_id.name = 'pnl_edititemform-cbo_pph_taxtype_id'		
	new fgta4slideselect(obj.cbo_pph_taxtype_id, {
		title: 'Pilih pph_taxtype_id',
		returnpage: this_page_id,
		api: $ui.apis.load_pph_taxtype_id,
		fieldValue: 'pph_taxtype_id',
		fieldValueMap: 'taxtype_id',
		fieldDisplay: 'taxtype_name',
		fields: [
			{mapping: 'taxtype_id', text: 'taxtype_id'},
			{mapping: 'taxtype_name', text: 'taxtype_name'},
		],
		OnDataLoading: (criteria, options) => {
				
		},
		OnDataLoaded : (result, options) => {
			result.records.unshift({taxtype_id:'--NULL--', taxtype_name:'NONE'});	
		},
		OnSelected: (value, display, record, args) => {
			if (value!=args.PreviousValue ) {
			}			
		}
	})				
			
	obj.cbo_mediaorder_id.name = 'pnl_edititemform-cbo_mediaorder_id'		
	new fgta4slideselect(obj.cbo_mediaorder_id, {
		title: 'Pilih mediaorder_id',
		returnpage: this_page_id,
		api: $ui.apis.load_mediaorder_id,
		fieldValue: 'mediaorder_id',
		fieldValueMap: 'mediaorder_id',
		fieldDisplay: 'mediaorder_descr',
		fields: [
			{mapping: 'mediaorder_id', text: 'mediaorder_id'},
			{mapping: 'mediaorder_descr', text: 'mediaorder_descr'},
		],
		OnDataLoading: (criteria, options) => {
				
		},
		OnDataLoaded : (result, options) => {
			result.records.unshift({mediaorder_id:'--NULL--', mediaorder_descr:'NONE'});	
		},
		OnSelected: (value, display, record, args) => {
			if (value!=args.PreviousValue ) {
			}			
		}
	})				
			
	obj.cbo_mediaorderitem_id.name = 'pnl_edititemform-cbo_mediaorderitem_id'		
	new fgta4slideselect(obj.cbo_mediaorderitem_id, {
		title: 'Pilih mediaorderitem_id',
		returnpage: this_page_id,
		api: $ui.apis.load_mediaorderitem_id,
		fieldValue: 'mediaorderitem_id',
		fieldValueMap: 'mediaorderitem_id',
		fieldDisplay: 'mediaorderitem_descr',
		fields: [
			{mapping: 'mediaorderitem_id', text: 'mediaorderitem_id'},
			{mapping: 'mediaorderitem_descr', text: 'mediaorderitem_descr'},
		],
		OnDataLoading: (criteria, options) => {
				
		},
		OnDataLoaded : (result, options) => {
			result.records.unshift({mediaorderitem_id:'--NULL--', mediaorderitem_descr:'NONE'});	
		},
		OnSelected: (value, display, record, args) => {
			if (value!=args.PreviousValue ) {
			}			
		}
	})				
			
	obj.cbo_projbudget_id.name = 'pnl_edititemform-cbo_projbudget_id'		
	new fgta4slideselect(obj.cbo_projbudget_id, {
		title: 'Pilih projbudget_id',
		returnpage: this_page_id,
		api: $ui.apis.load_projbudget_id,
		fieldValue: 'projbudget_id',
		fieldValueMap: 'projbudget_id',
		fieldDisplay: 'projbudget_name',
		fields: [
			{mapping: 'projbudget_id', text: 'projbudget_id'},
			{mapping: 'projbudget_name', text: 'projbudget_name'},
		],
		OnDataLoading: (criteria, options) => {
				
		},
		OnDataLoaded : (result, options) => {
				
		},
		OnSelected: (value, display, record, args) => {
			if (value!=args.PreviousValue ) {
				form.setValue(obj.txt_project_id, record.project_id);			
						
			}			
		}
	})				
			
	obj.cbo_projbudgettask_id.name = 'pnl_edititemform-cbo_projbudgettask_id'		
	new fgta4slideselect(obj.cbo_projbudgettask_id, {
		title: 'Pilih projbudgettask_id',
		returnpage: this_page_id,
		api: $ui.apis.load_projbudgettask_id,
		fieldValue: 'projbudgettask_id',
		fieldValueMap: 'projbudgettask_id',
		fieldDisplay: 'projbudgettask_name',
		fields: [
			{mapping: 'projbudgettask_id', text: 'projbudgettask_id'},
			{mapping: 'projbudgettask_name', text: 'projbudgettask_name'},
		],
		OnDataLoading: (criteria, options) => {
			criteria.projbudget_id = form.getValue(obj.cbo_projbudget_id);	
		},
		OnDataLoaded : (result, options) => {
			result.records.unshift({projbudgettask_id:'--NULL--', projbudgettask_name:'NONE'});	
		},
		OnSelected: (value, display, record, args) => {
			if (value!=args.PreviousValue ) {
				form.setValue(obj.txt_projecttask_id, record.projecttask_id);			
						
			}			
		}
	})				
			
	obj.cbo_billoutpreprocess_id.name = 'pnl_edititemform-cbo_billoutpreprocess_id'		
	new fgta4slideselect(obj.cbo_billoutpreprocess_id, {
		title: 'Pilih billoutpreprocess_id',
		returnpage: this_page_id,
		api: $ui.apis.load_billoutpreprocess_id,
		fieldValue: 'billoutpreprocess_id',
		fieldValueMap: 'billoutpreprocess_id',
		fieldDisplay: 'billoutpreprocess_name',
		fields: [
			{mapping: 'billoutpreprocess_id', text: 'billoutpreprocess_id'},
			{mapping: 'billoutpreprocess_name', text: 'billoutpreprocess_name'},
		],
		OnDataLoading: (criteria, options) => {
				
		},
		OnDataLoaded : (result, options) => {
				
		},
		OnSelected: (value, display, record, args) => {
			if (value!=args.PreviousValue ) {
			}			
		}
	})				
			


	btn_addnew.linkbutton({ onClick: () => { btn_addnew_click() }  })
	btn_prev.linkbutton({ onClick: () => { btn_prev_click() } })
	btn_next.linkbutton({ onClick: () => { btn_next_click() } })

	document.addEventListener('keydown', (ev)=>{
		if ($ui.getPages().getCurrentPage()==this_page_id) {
			if (ev.code=='KeyS' && ev.ctrlKey==true) {
				if (!form.isInViewMode()) {
					form.btn_save_click();
				}
				ev.stopPropagation()
				ev.preventDefault()
			}
		}
	}, true)
	
	document.addEventListener('OnButtonBack', (ev) => {
		if ($ui.getPages().getCurrentPage()==this_page_id) {
			ev.detail.cancel = true;
			if (form.isDataChanged()) {
				form.canceledit(()=>{
					$ui.getPages().show('pnl_edititemgrid', ()=>{
						form.setViewMode()
						$ui.getPages().ITEMS['pnl_edititemgrid'].handler.scrolllast()
					})					
				})
			} else {
				$ui.getPages().show('pnl_edititemgrid', ()=>{
					form.setViewMode()
					$ui.getPages().ITEMS['pnl_edititemgrid'].handler.scrolllast()
				})
			}
		
		}		
	})

	document.addEventListener('OnButtonHome', (ev) => {
		if ($ui.getPages().getCurrentPage()==this_page_id) {
			ev.detail.cancel = true;
		}
	})

	document.addEventListener('OnSizeRecalculated', (ev) => {
		OnSizeRecalculated(ev.detail.width, ev.detail.height)
	})
	
	
	document.addEventListener('OnViewModeChanged', (ev) => {
		if (ev.detail.viewmode===true) {
			form.lock(true)
			btn_addnew.allow = false
			btn_addnew.linkbutton('disable')
			chk_autoadd.attr("disabled", true);	
			chk_autoadd.prop("checked", false);			
		} else {
			form.lock(false)
			btn_addnew.allow = true
			btn_addnew.linkbutton('enable')
			chk_autoadd.removeAttr("disabled");
			chk_autoadd.prop("checked", false);
		}
	})
}


export function OnSizeRecalculated(width, height) {
}


export function getForm() {
	return form
}

export function open(data, rowid, hdata) {
	// console.log(header_data)
	txt_title.html(hdata.medialogproof_date)
	header_data = hdata

	var pOpt = form.getDefaultPrompt(false)
	var fn_dataopening = async (options) => {
		options.api = `${global.modulefullname}/item-open`
		options.criteria[form.primary.mapping] = data[form.primary.mapping]
	}

	var fn_dataopened = async (result, options) => {
		var record = result.record;
		updatefilebox(result.record);
/*
		if (record.pph_taxtype_id==null) { record.pph_taxtype_id='--NULL--'; record.pph_taxtype_name='NONE'; }
		if (record.mediaorder_id==null) { record.mediaorder_id='--NULL--'; record.mediaorder_descr='NONE'; }
		if (record.mediaorderitem_id==null) { record.mediaorderitem_id='--NULL--'; record.mediaorderitem_descr='NONE'; }
		if (record.projbudgettask_id==null) { record.projbudgettask_id='--NULL--'; record.projbudgettask_name='NONE'; }

*/
		for (var objid in obj) {
			let o = obj[objid]
			if (o.isCombo() && !o.isRequired()) {
				var value =  result.record[o.getFieldValueName()];
				if (value==null ) {
					record[o.getFieldValueName()] = pOpt.value;
					record[o.getFieldDisplayName()] = pOpt.text;
				}
			}
		}
		form.SuspendEvent(true);
		form
			.fill(record)
			.setValue(obj.cbo_pph_taxtype_id, record.pph_taxtype_id, record.pph_taxtype_name)
			.setValue(obj.cbo_mediaorder_id, record.mediaorder_id, record.mediaorder_descr)
			.setValue(obj.cbo_mediaorderitem_id, record.mediaorderitem_id, record.mediaorderitem_descr)
			.setValue(obj.cbo_projbudget_id, record.projbudget_id, record.projbudget_name)
			.setValue(obj.cbo_projbudgettask_id, record.projbudgettask_id, record.projbudgettask_name)
			.setValue(obj.cbo_billoutpreprocess_id, record.billoutpreprocess_id, record.billoutpreprocess_name)
			.setViewMode()
			.rowid = rowid



		/* tambahkan event atau behaviour saat form dibuka
		   apabila ada rutin mengubah form dan tidak mau dijalankan pada saat opening,
		   cek dengan form.isEventSuspended()
		*/ 



		form.commit()
		form.SuspendEvent(false);


		// Editable
		if (form.AllowEditRecord!=true) {
			btn_edit.hide();
			btn_save.hide();
			btn_delete.hide();
		}
		

		// tambah baris
		if (form.AllowAddRecord) {
			btn_addnew.show()
		} else {
			btn_addnew.hide()
		}	

		// hapus baris
		if (form.AllowRemoveRecord) {
			btn_delete.show()
		} else {
			btn_delete.hide()
		}

		var prevnode = $(`#${rowid}`).prev()
		if (prevnode.length>0) {
			btn_prev.linkbutton('enable')
		} else {
			btn_prev.linkbutton('disable')
		}

		var nextode = $(`#${rowid}`).next()
		if (nextode.length>0) {
			btn_next.linkbutton('enable')
		} else {
			btn_next.linkbutton('disable')
		}		
	}

	var fn_dataopenerror = (err) => {
		$ui.ShowMessage('[ERROR]'+err.errormessage);
	}

	form.dataload(fn_dataopening, fn_dataopened, fn_dataopenerror)	
}

export function createnew(hdata) {
	header_data = hdata

	txt_title.html('Create New Row')
	form.createnew(async (data, options)=>{
		data.medialogproof_id= hdata.medialogproof_id
		data.item_value = 0

		data.actual_duration = 0
		data.mediaorderitem_validr = 0
		data.mediaorderitem_ppnidr = 0

			data.pph_taxtype_id = '--NULL--'
			data.pph_taxtype_name = 'NONE'
			data.mediaorder_id = '--NULL--'
			data.mediaorder_descr = 'NONE'
			data.mediaorderitem_id = '--NULL--'
			data.mediaorderitem_descr = 'NONE'
			data.projbudget_id = '0'
			data.projbudget_name = '-- PILIH --'
			data.projbudgettask_id = '--NULL--'
			data.projbudgettask_name = 'NONE'
			data.billoutpreprocess_id = '0'
			data.billoutpreprocess_name = '-- PILIH --'




		form.rowid = null
		options.OnCanceled = () => {
			$ui.getPages().show('pnl_edititemgrid')
		}
	})
}


async function form_datasaving(data, options) {
	options.api = `${global.modulefullname}/item-save`

	// options.skipmappingresponse = ['pph_taxtype_id', 'mediaorder_id', 'mediaorderitem_id', 'projbudgettask_id', ];
	options.skipmappingresponse = [];
	for (var objid in obj) {
		var o = obj[objid]
		if (o.isCombo() && !o.isRequired()) {
			var id = o.getFieldValueName()
			options.skipmappingresponse.push(id)
			console.log(id)
		}
	}	
}

async function form_datasaved(result, options) {
	var data = {}
	Object.assign(data, form.getData(), result.dataresponse)

	/*
	form.setValue(obj.cbo_pph_taxtype_id, result.dataresponse.pph_taxtype_name!=='--NULL--' ? result.dataresponse.pph_taxtype_id : '--NULL--', result.dataresponse.pph_taxtype_name!=='--NULL--'?result.dataresponse.pph_taxtype_name:'NONE')
	form.setValue(obj.cbo_mediaorder_id, result.dataresponse.mediaorder_descr!=='--NULL--' ? result.dataresponse.mediaorder_id : '--NULL--', result.dataresponse.mediaorder_descr!=='--NULL--'?result.dataresponse.mediaorder_descr:'NONE')
	form.setValue(obj.cbo_mediaorderitem_id, result.dataresponse.mediaorderitem_descr!=='--NULL--' ? result.dataresponse.mediaorderitem_id : '--NULL--', result.dataresponse.mediaorderitem_descr!=='--NULL--'?result.dataresponse.mediaorderitem_descr:'NONE')
	form.setValue(obj.cbo_projbudgettask_id, result.dataresponse.projbudgettask_name!=='--NULL--' ? result.dataresponse.projbudgettask_id : '--NULL--', result.dataresponse.projbudgettask_name!=='--NULL--'?result.dataresponse.projbudgettask_name:'NONE')

	*/

	var pOpt = form.getDefaultPrompt(false)
	for (var objid in obj) {
		var o = obj[objid]
		if (o.isCombo() && !o.isRequired()) {
			var value =  result.dataresponse[o.getFieldValueName()];
			var text = result.dataresponse[o.getFieldDisplayName()];
			if (value==null ) {
				value = pOpt.value;
				text = pOpt.text;
			}
			form.setValue(o, value, text);
		}
	}
	form.rowid = $ui.getPages().ITEMS['pnl_edititemgrid'].handler.updategrid(data, form.rowid)

	var autoadd = chk_autoadd.prop("checked")
	if (autoadd) {
		setTimeout(()=>{
			btn_addnew_click()
		}, 1000)
	}
}

async function form_deleting(data, options) {
	options.api = `${global.modulefullname}/item-delete`
}

async function form_deleted(result, options) {
	options.suppressdialog = true
	$ui.getPages().show('pnl_edititemgrid', ()=>{
		$ui.getPages().ITEMS['pnl_edititemgrid'].handler.removerow(form.rowid)
	})
	
}

function updatefilebox(record) {
	// apabila ada keperluan untuk menampilkan data dari object storage

}

function form_viewmodechanged(viewonly) {
	if (viewonly) {
		btn_prev.linkbutton('enable')
		btn_next.linkbutton('enable')
		if (btn_addnew.allow) {
			btn_addnew.linkbutton('enable')
		} else {
			btn_addnew.linkbutton('disable')
		}
	} else {
		btn_prev.linkbutton('disable')
		btn_next.linkbutton('disable')
		btn_addnew.linkbutton('disable')
	}
}


function form_idsetup(options) {
	var objid = obj.txt_medialogproofitem_id
	switch (options.action) {
		case 'fill' :
			objid.textbox('disable') 
			break;

		case 'createnew' :
			// console.log('new')
			if (form.autoid) {
				objid.textbox('disable') 
				objid.textbox('setText', '[AUTO]') 
			} else {
				objid.textbox('enable') 
			}
			break;
			
		case 'save' :
			objid.textbox('disable') 
			break;	
	}
}

function btn_addnew_click() {
	createnew(header_data)
}


function btn_prev_click() {
	var prevode = $(`#${form.rowid}`).prev()
	if (prevode.length==0) {
		return
	} 
	
	var trid = prevode.attr('id')
	var dataid = prevode.attr('dataid')
	var record = $ui.getPages().ITEMS['pnl_edititemgrid'].handler.getGrid().DATA[dataid]

	open(record, trid, header_data)
}

function btn_next_click() {
	var nextode = $(`#${form.rowid}`).next()
	if (nextode.length==0) {
		return
	} 

	var trid = nextode.attr('id')
	var dataid = nextode.attr('dataid')
	var record = $ui.getPages().ITEMS['pnl_edititemgrid'].handler.getGrid().DATA[dataid]

	open(record, trid, header_data)
}