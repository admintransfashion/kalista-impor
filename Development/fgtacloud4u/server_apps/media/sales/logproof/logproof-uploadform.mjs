var this_page_id;
var this_page_options;

import {fgta4slideselect} from  '../../../../../index.php/asset/fgta/framework/fgta4libs/fgta4slideselect.mjs'

const txt_title = $('#pnl_edituploadform-title')
const btn_edit = $('#pnl_edituploadform-btn_edit')
const btn_save = $('#pnl_edituploadform-btn_save')
const btn_delete = $('#pnl_edituploadform-btn_delete')
const btn_prev = $('#pnl_edituploadform-btn_prev')
const btn_next = $('#pnl_edituploadform-btn_next')
const btn_addnew = $('#pnl_edituploadform-btn_addnew')
const chk_autoadd = $('#pnl_edituploadform-autoadd')


const pnl_form = $('#pnl_edituploadform-form')
const obj = {
	txt_medialogproofupload_id: $('#pnl_edituploadform-txt_medialogproofupload_id'),
	txt_mediaadslot_timestart: $('#pnl_edituploadform-txt_mediaadslot_timestart'),
	txt_mediaadslot_timeend: $('#pnl_edituploadform-txt_mediaadslot_timeend'),
	txt_mediaadslot_duration: $('#pnl_edituploadform-txt_mediaadslot_duration'),
	txt_mediaadslot_descr: $('#pnl_edituploadform-txt_mediaadslot_descr'),
	txt_mediaadslot_code: $('#pnl_edituploadform-txt_mediaadslot_code'),
	txt_actual_timestart: $('#pnl_edituploadform-txt_actual_timestart'),
	txt_actual_timeend: $('#pnl_edituploadform-txt_actual_timeend'),
	txt_actual_duration: $('#pnl_edituploadform-txt_actual_duration'),
	txt_spot_id: $('#pnl_edituploadform-txt_spot_id'),
	txt_mediaorder_ref: $('#pnl_edituploadform-txt_mediaorder_ref'),
	txt_mediaorder_reftype: $('#pnl_edituploadform-txt_mediaorder_reftype'),
	txt_mediaorder_descr: $('#pnl_edituploadform-txt_mediaorder_descr'),
	txt_mediaorder_id: $('#pnl_edituploadform-txt_mediaorder_id'),
	txt_mediaordertype_id: $('#pnl_edituploadform-txt_mediaordertype_id'),
	txt_agency_code: $('#pnl_edituploadform-txt_agency_code'),
	txt_agency_name: $('#pnl_edituploadform-txt_agency_name'),
	txt_agency_partner_id: $('#pnl_edituploadform-txt_agency_partner_id'),
	txt_advertiser_code: $('#pnl_edituploadform-txt_advertiser_code'),
	txt_advertiser_name: $('#pnl_edituploadform-txt_advertiser_name'),
	txt_advertiser_partner_id: $('#pnl_edituploadform-txt_advertiser_partner_id'),
	txt_brand_code: $('#pnl_edituploadform-txt_brand_code'),
	txt_brand_name: $('#pnl_edituploadform-txt_brand_name'),
	txt_brand_id: $('#pnl_edituploadform-txt_brand_id'),
	txt_programme_code: $('#pnl_edituploadform-txt_programme_code'),
	txt_programme_name: $('#pnl_edituploadform-txt_programme_name'),
	txt_project_id: $('#pnl_edituploadform-txt_project_id'),
	txt_episode_code: $('#pnl_edituploadform-txt_episode_code'),
	txt_episode_name: $('#pnl_edituploadform-txt_episode_name'),
	txt_projecttask_id: $('#pnl_edituploadform-txt_projecttask_id'),
	txt_medialogproof_validr: $('#pnl_edituploadform-txt_medialogproof_validr'),
	txt_medialogproof_ppnidr: $('#pnl_edituploadform-txt_medialogproof_ppnidr'),
	cbo_pph_taxtype_id: $('#pnl_edituploadform-cbo_pph_taxtype_id'),
	cbo_billoutpreprocess_id: $('#pnl_edituploadform-cbo_billoutpreprocess_id'),
	txt_medialogproof_id: $('#pnl_edituploadform-txt_medialogproof_id')
}


let form;
let header_data;



export async function init(opt) {
	this_page_id = opt.id
	this_page_options = opt;

	
	form = new global.fgta4form(pnl_form, {
		primary: obj.txt_medialogproofupload_id,
		autoid: true,
		logview: 'trn_medialogproofupload',
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




	obj.cbo_pph_taxtype_id.name = 'pnl_edituploadform-cbo_pph_taxtype_id'		
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
			criteria.taxmodel_id = 'PPH'
		},
		OnDataLoaded : (result, options) => {
			result.records.unshift({taxtype_id:'--NULL--', taxtype_name:'NONE'});	
		},
		OnSelected: (value, display, record, args) => {
			if (value!=args.PreviousValue ) {
			}			
		}
	})				
	
	
	obj.cbo_billoutpreprocess_id.name = 'pnl_edituploadform-cbo_billoutpreprocess_id'		
	new fgta4slideselect(obj.cbo_billoutpreprocess_id, {
		title: 'Pilih Proses',
		returnpage: this_page_id,
		api: $ui.apis.load_billoutpreprocess_id,
		fieldValue: 'billoutpreprocess_id',
		fieldValueMap: 'billoutpreprocess_id',
		fieldDisplay: 'billoutpreprocess_name',
		fields: [
			{mapping: 'billoutpreprocess_id', text: 'billoutpreprocess_id'},
			{mapping: 'billoutpreprocess_name', text: 'billoutpreprocess_name'},
		],
		OnDataLoading: (criteria, options) => {},
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
					$ui.getPages().show('pnl_edituploadgrid', ()=>{
						form.setViewMode()
						$ui.getPages().ITEMS['pnl_edituploadgrid'].handler.scrolllast()
					})					
				})
			} else {
				$ui.getPages().show('pnl_edituploadgrid', ()=>{
					form.setViewMode()
					$ui.getPages().ITEMS['pnl_edituploadgrid'].handler.scrolllast()
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
		options.api = `${global.modulefullname}/upload-open`
		options.criteria[form.primary.mapping] = data[form.primary.mapping]
	}

	var fn_dataopened = async (result, options) => {
		var record = result.record;
		updatefilebox(result.record);
/*
		if (record.pph_taxtype_id==null) { record.pph_taxtype_id='--NULL--'; record.pph_taxtype_name='NONE'; }

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
		data.upload_value = 0

		data.mediaadslot_duration = 0
		data.actual_duration = 0
		data.medialogproof_validr = 0
		data.medialogproof_ppnidr = 0

		data.pph_taxtype_id = '--NULL--'
		data.pph_taxtype_name = 'NONE'
		data.billoutpreprocess_id = '0'
		data.billoutpreprocess_name = '--PILIH--'

		form.rowid = null
		options.OnCanceled = () => {
			$ui.getPages().show('pnl_edituploadgrid')
		}
	})
}


async function form_datasaving(data, options) {
	options.api = `${global.modulefullname}/upload-save`

	// options.skipmappingresponse = ['pph_taxtype_id', ];
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
	form.rowid = $ui.getPages().ITEMS['pnl_edituploadgrid'].handler.updategrid(data, form.rowid)

	var autoadd = chk_autoadd.prop("checked")
	if (autoadd) {
		setTimeout(()=>{
			btn_addnew_click()
		}, 1000)
	}
}

async function form_deleting(data, options) {
	options.api = `${global.modulefullname}/upload-delete`
}

async function form_deleted(result, options) {
	options.suppressdialog = true
	$ui.getPages().show('pnl_edituploadgrid', ()=>{
		$ui.getPages().ITEMS['pnl_edituploadgrid'].handler.removerow(form.rowid)
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
	var objid = obj.txt_medialogproofupload_id
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
	var record = $ui.getPages().ITEMS['pnl_edituploadgrid'].handler.getGrid().DATA[dataid]

	open(record, trid, header_data)
}

function btn_next_click() {
	var nextode = $(`#${form.rowid}`).next()
	if (nextode.length==0) {
		return
	} 

	var trid = nextode.attr('id')
	var dataid = nextode.attr('dataid')
	var record = $ui.getPages().ITEMS['pnl_edituploadgrid'].handler.getGrid().DATA[dataid]

	open(record, trid, header_data)
}